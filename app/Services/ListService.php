<?php

namespace App\Services;

use App\Models\ListModel;
use App\Models\ListItem;
use App\Models\Access;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ListService
{
    
    public function index(string $id = null)
    {
        if ($id !== null) {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'Пользователь не найден'], 404);
            }
            $lists = $user->lists;
        } else {
            $user = Auth::user();
            $lists = $user->lists;
        }

        return response()->json($lists);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $listName = $request->input('name');

            if (empty($listName)) {
                return response()->json(['error' => 'У списка должно быть имя'], 400);
            }

            $user = Auth::user();
            $list = $user->lists()->create([
                'name' => $listName,
            ]);

            $list->accesses()->create([
                'read' => true,
                'edit' => true,
                'creator' => true,
                'user_id' => $user->id
            ]);

            $items = $request->input('items');
    
            if (empty($items)) {
                DB::rollBack();
                return response()->json(['error' => 'У списка должен быть хотя бы один пункт'], 400);
            }
    
            foreach ($items as $itemData) {
                $imageUrl = null;
                if (isset($itemData['image'])) {
                    if ($itemData['image'] instanceof UploadedFile && $itemData['image']->isValid()) {
                        $file = $itemData['image'];
                        $filePath = $file->store('images', 'public');
                        $imageUrl = asset('storage/' . $filePath);
                    } else {
                        DB::rollBack();
                        return response()->json(['error' => 'Формат изображения не подходит'], 400);
                    }
                }
    
                $item = $list->items()->create([
                    'title' => $itemData['title'],
                    'body' => $itemData['body'],
                    'img_path' => $imageUrl
                ]);
    
                $tags = $itemData['tags'];
                if (!empty($tags)) {
                    foreach ($tags as $tagData) {
                        $tag = $list->tags()->create(['name' => $tagData['name']]);
                        $item->tags()->attach($tag);
                    }
                }
            }
    
            DB::commit();
    
            return response()->json(['message' => 'Список создан'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка при создании списка', 'exception' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $user = Auth::user();

        if ($user->accesses->contains('list_model_id', $id)) {
            
            $list = ListModel::with(['accesses' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }, 'tags', 'items', 'items.tags'])->find($id);

            if(empty($list)){
                return response()->json(['error' => 'Список не найден'], 404);
            }
            return response()->json($list);
        }

        return response()->json(['error' => 'Доступ запрещен'], 403);
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $listName = $request->input('name');

            if (empty($listName)) {
                return response()->json(['error' => 'У списка должно быть имя'], 400);
            }

            $user = Auth::user();
            $list = $user->lists()->findOrFail($id);
            $list->name = $listName;
            $list->save();

            $items = $request->input('items');

            if (empty($items)) {
                DB::rollBack();
                return response()->json(['error' => 'У списка должен быть хотя бы один пункт'], 400);
            }

            $list->items()->delete();

            foreach ($items as $itemData) {
                $imageUrl = null;
                if (isset($itemData['image'])) {
                    if ($itemData['image'] instanceof UploadedFile && $itemData['image']->isValid()) {
                        $file = $itemData['image'];
                        $filePath = $file->store('images', 'public');
                        $imageUrl = asset('storage/' . $filePath);
                    } else {
                        DB::rollBack();
                        return response()->json(['error' => 'Формат изображения не подходит'], 400);
                    }
                }

                $item = $list->items()->create([
                    'title' => $itemData['title'],
                    'body' => $itemData['body'],
                    'img_path' => $imageUrl
                ]);

                $tags = $itemData['tags'];
                if (!empty($tags)) {
                    foreach ($tags as $tagData) {
                        $tagName = $tagData['name'];
                        $tag = Tag::firstOrCreate(['name' => $tagName]);
                        $tag->list_model_id = $list->id;
                        $tag->save();
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Список обновлен'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка при обновлении списка', 'exception' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        $user = Auth::user();
        $list = ListModel::find($id);
        if(empty($list)){
            return response()->json(['error' => 'Список не найден'], 404);
        }
        if($user->id === $list->user->id){
        $list->delete();
        return response()->json(['message' => 'Список удален'], 200);
        }else{
            return response()->json(['error' => 'Список может удалить только его создатель'], 403);
        }
    }

    public function setAccess(Request $request)
    {
        $listId = $request->input('list_id');
        $userName = $request->input('user_name');
        $read = $request->input('read');
        $edit = $request->input('edit');

        $creator = Auth::user();
        $list = ListModel::find($listId);
        if($creator->id !== $list->user->id)
        {
            return response()->json(['error' => 'Настраивать доступ может только его создатель'], 403);
        }

        $user = User::where('name', $userName)->first();

        if (empty($list)) {
            return response()->json(['error' => 'Список не найден'], 404);
        }
        if (empty($user)) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        $access = Access::where('list_id', $listId)
                        ->where('user_id', $user->id)
                        ->first();

        if (empty($access)) {
            $access = new Access();
            $access->list_id = $listId;
            $access->user_id = $user->id;
        }

        $access->read = $read;
        $access->edit = $edit;
        $access->save();

        return response()->json(['message' => 'Доступ обновлен'], 200);
    }
}
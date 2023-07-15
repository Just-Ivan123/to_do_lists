<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ListService;

class ListController extends Controller
{

    protected $listService;

    public function __construct(ListService $listService)
    {
        $this->listService = $listService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       return $this->listService->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->listService->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->listService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return $this->listService->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->listService->destroy();
    }

    public function setAccess(Request $request)
    {
        return $this->listService->setAccess($request);
    }
}

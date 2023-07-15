<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'img_path',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'list_item_tag');
    }

    public function list()
    {
        return $this->belongsTo(ListModel::class);
    }
}

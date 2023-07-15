<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
    ];

    public function listItems()
    {
        return $this->belongsToMany(ListItem::class, 'list_item_tag');
    }

    public function list()
    {
        return $this->belongsTo(ListModel::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accesses()
    {
        return $this->hasMany(Access::class);
    }

    public function items()
    {
        return $this->hasMany(ListItem::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }
}

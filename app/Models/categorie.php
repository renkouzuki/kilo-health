<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class categorie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['icon', 'name', 'slug'];

    public function posts()
    {
        return $this->hasMany(post::class , 'category_id');
    }

    public function topics()
    {
        return $this->hasMany(topic::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'author_id',
        'thumbnail',
        'read_time',
        'published_at',
        'views',
        'likes'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(categorie::class);
    }


    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function postViews()
    {
        return $this->hasMany(post_view::class);
    }

    public function uploadMedia()
    {
        return $this->hasMany(upload_media::class);
    }
}

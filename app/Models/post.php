<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description','upload_media_id', 'content' , 'category_id', 'author_id', 'thumbnail', 'read_time', 'published_at', 'content_type'];
    protected $dates = ['published_at'];

    protected $casts = [
        'content_type' => 'string',
    ];

    public function category()
    {
        return $this->belongsTo(categorie::class);
    }

    public function author(){
        return $this->belongsTo(User::class, 'author_id');
    }

    public function views()
    {
        return $this->hasMany(post_view::class);
    }

    public function uploadMedia()
    {
        return $this->belongsTo(upload_media::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'post_likes');// this shit doesnt need ->withTimestamp() to work;
    }

    public function postPhotos(){
        return $this->hasMany(more_post_photos::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'category_id', 'author_id', 'thumbnail', 'read_time', 'published_at', 'content_type'];
    protected $dates = ['published_at'];

    protected $casts = [
        'content_type' => 'string',
    ];

    public function category()
    {
        return $this->belongsTo(categorie::class);
    }

    public function views()
    {
        return $this->hasMany(post_view::class);
    }

    public function uploadMedia()
    {
        return $this->hasMany(upload_media::class);
    }
}

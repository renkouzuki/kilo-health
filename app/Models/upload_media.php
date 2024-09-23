<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class upload_media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['url', 'post_id'];

    public function post()
    {
        return $this->belongsTo(post::class);
    }
}

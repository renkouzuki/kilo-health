<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class more_post_photos extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'post_id',
        'url',
    ];

    public function post(){
        return $this->belongsTo(post::class);
    }
}

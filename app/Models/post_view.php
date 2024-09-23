<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class post_view extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['post_id', 'user_id', 'viewed_at'];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class site_setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'name', 'input_type', 'value'];

    protected $casts = [
        'input_type' => 'string',
    ];
}

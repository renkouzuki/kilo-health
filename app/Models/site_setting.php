<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class site_setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'logo_url',
        'footer_about',
        'footer_categories',
        'footer_company_links'
    ];

    protected $casts = [
        'footer_categories' => 'array',
        'footer_company_links' => 'array',
    ];

    //// disable use only first data to display on site like logo and stuffs
    protected $primaryKey = 'id';
    public $incrementing = false;

    /// method inex get the data if not exist create it id = 1 "directly"
    public static function getSettings()
    {
        return static::firstOrCreate(['id' => 1]);
    }

    /// update site_settings data directly
    public static function updateSettings(array $data)
    {
        return static::getSettings()->update($data);
    }
}

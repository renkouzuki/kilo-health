<?php

namespace App\Services;

use App\Repositories\SiteSettings\SiteSettingInterface;

class SiteSettingsService
{

    protected $Repository;

    public function __construct(SiteSettingInterface $repository)
    {
        $this->Repository = $repository;
    }

    public function getSetting(string $key)
    {
        $setting = $this->Repository->findByKey($key);
        return $setting ? $this->formatSettingValue($setting) : null;
    }

    public function getAllSettings(): array
    {
        $settings = $this->Repository->getSettings();
        return array_map([$this, 'formatSettingValue'], $settings);
    }

    private function formatSettingValue(array $setting)
    {
        switch ($setting['input_type']) {
            case 'boolean':
                return (bool) $setting['value'];
            case 'number':
                return is_numeric($setting['value']) ? (float) $setting['value'] : $setting['value'];
            case 'image':
                return asset('storage/' . $setting['value']);
            default:
                return $setting['value'];
        }
    }
}

<?php

namespace App\Repositories\SiteSettings;

use App\Models\site_setting;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class SiteSettingController implements SiteSettingInterface
{

    public function getAllSettings(): Collection
    {
        try {
            return site_setting::all();
        } catch (Exception $e) {
            Log::error('Error retrieving all settings: ' . $e->getMessage());
            throw new Exception('Error retrieving all settings');
        }
    }

    public function getSetting(string $key): ?site_setting
    {
        try {
            return site_setting::where('key', $key)->first();
        } catch (Exception $e) {
            Log::error('Error retrieving setting: ' . $e->getMessage());
            throw new Exception('Error retrieving setting');
        }
    }

    public function updateSetting(string $key, string $value): bool
    {
        try {
            $setting = site_setting::where('key', $key)->first();
            if (!$setting) {
                return false;
            }
            return $setting->update(['value' => $value]);
        } catch (Exception $e) {
            Log::error('Error updating setting: ' . $e->getMessage());
            throw new Exception('Error updating setting');
        }
    }

    public function createSetting(array $data): site_setting
    {
        try {
            return site_setting::create($data);
        } catch (Exception $e) {
            Log::error('Error creating setting: ' . $e->getMessage());
            throw new Exception('Error creating setting');
        }
    }

    public function deleteSetting(string $key): bool
    {
        try {
            $setting = site_setting::where('key', $key)->first();
            if (!$setting) {
                return false;
            }
            return $setting->delete();
        } catch (Exception $e) {
            Log::error('Error deleting setting: ' . $e->getMessage());
            throw new Exception('Error deleting setting');
        }
    }
}

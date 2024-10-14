<?php

namespace App\Repositories\SiteSettings;

use App\Models\site_setting;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SiteSettingController implements SiteSettingInterface
{

    protected $logService;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
    }

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
        } catch (ModelNotFoundException $e) {
            throw new Exception('Setting not found');
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
            $oldValue = $setting->value;
            $updated = $setting->update(['value' => $value]);
            if ($updated) {
                $this->logService->log(Auth::id(), 'updated_setting', site_setting::class, $setting->id, json_encode([
                    'key' => $key,
                    'old_value' => $oldValue,
                    'new_value' => $value
                ]));
            }
            return $updated;
        } catch (Exception $e) {
            Log::error('Error updating setting: ' . $e->getMessage());
            throw new Exception('Error updating setting');
        }
    }

    public function createSetting(array $data): site_setting
    {
        try {
            $setting = site_setting::create($data);
            $this->logService->log(Auth::id(), 'created_setting', site_setting::class, $setting->id, json_encode($data));
            return $setting;
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
            $deleted = $setting->delete();
            if ($deleted) {
                $this->logService->log(Auth::id(), 'deleted_setting', site_setting::class, $setting->id, json_encode(['key' => $key]));
            }
            return $deleted;
        } catch (Exception $e) {
            Log::error('Error deleting setting: ' . $e->getMessage());
            throw new Exception('Error deleting setting');
        }
    }
}

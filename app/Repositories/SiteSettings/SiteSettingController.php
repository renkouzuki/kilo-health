<?php

namespace App\Repositories\SiteSettings;

use App\Models\site_setting;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SiteSettingController implements SiteSettingInterface
{

    protected $logService;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
    }

    public function getAllSettings(string $search = null , int $perPage = 10): LengthAwarePaginator
    {
        try {
            $query = site_setting::query()
            ->when($search , function($query,$search){
               return $query->where('name' , 'LIKE' , "%{$search}%");
            })->orderBy('created_at', 'desc');
            return $query->latest()->paginate($perPage);
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

    public function updateSetting(string $key, Request $req): bool
    {
        try {
            $setting = site_setting::where('key', $key)->first();
            if (!$setting) {
                return false;
            }
            $oldValue = $setting->value;

            $data = [
                'name' => $req->name,
                'input_type'=>$req->input_type
            ];

            if($req->hasFile('image')){
                Storage::disk('s3')->delete($setting->value);
                $data['value'] = $req->file('image')->store('site_image' , 's3');
            }else{
                $data['value'] = $req->value;
            }

            $data = array_filter($data);

            $updated = $setting->update($data);
            if ($updated) {
                $this->logService->log(Auth::id(), 'updated_setting', site_setting::class, $setting->id, json_encode([
                    'key' => $key,
                    'old_value' => $oldValue,
                    'new_value' => $req->value
                ]));
            }
            return $updated;
        } catch (ModelNotFoundException $e){
            throw new Exception('site_setting not found');
        } catch (Exception $e) {
            Log::error('Error updating setting: ' . $e->getMessage());
            throw new Exception('Error updating setting');
        }
    }

    public function createSetting(Request $req): site_setting
    {
        try {
            $data = [
                'key' => $req->key,
                'name' => $req->name,
                'value' => $req->hasFile('image') ? $req->file('image')->store('site_image' , 's3') : $req->value,
                'input_type'=>$req->input_type
            ];

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

<?php

namespace App\Repositories\SiteSettings;

use App\Models\site_setting;
use Illuminate\Database\Eloquent\Collection;

interface SiteSettingInterface
{
    public function getAllSettings(): Collection;
    public function getSetting(string $key): ?site_setting;
    public function updateSetting(string $key, string $value): bool;
    public function createSetting(array $data): site_setting;
    public function deleteSetting(string $key): bool;
}

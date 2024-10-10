<?php

namespace App\Repositories\SiteSettings;

use App\Models\site_setting;

class SiteSettingController {
    public function all(): site_setting
    {
        $category = site_setting::all()->latest();

        return $category;
    }

    public function find(int $id): site_setting
    {
        return site_setting::all()->latest();
    }

    public function create(array $data): void {}

    public function update($id, array $data): void {}

    public function delete($id): void {}
}

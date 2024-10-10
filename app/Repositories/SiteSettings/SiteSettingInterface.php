<?php

namespace App\Repositories\SiteSettings;

use App\Models\site_setting;

interface SiteSettingInterface {
    public function all():site_setting;
    public function find(int $id):site_setting;
    public function create(array $data):void;
    public function update(int $id, array $data):void;
    public function delete(int $id):void;
}

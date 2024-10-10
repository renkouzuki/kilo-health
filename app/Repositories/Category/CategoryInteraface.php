<?php

namespace App\Repositories\Category;

use App\Models\categorie;

interface CategoryInterface {
    public function all():categorie;
    public function find(int $id):categorie;
    public function create(array $data):void;
    public function update(int $id, array $data):void;
    public function delete(int $id):void;
}

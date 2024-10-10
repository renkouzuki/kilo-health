<?php

namespace App\Repositories\Topics;

use App\Models\topic;

interface TopicInterface {
    public function all():topic;
    public function find(int $id):topic;
    public function create(array $data):void;
    public function update(int $id, array $data):void;
    public function delete(int $id):void;
}

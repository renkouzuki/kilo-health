<?php

namespace App\Repositories\Dashboard;

use Illuminate\Database\Eloquent\Collection;

interface dashbInterface
{
    public function getUserGrowth(): array;
    public function getContentOverview(): array;
    public function getEngagementSummary(): array;
    public function getTopPerformingPost(): array;
}

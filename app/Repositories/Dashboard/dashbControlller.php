<?php

namespace App\Repositories\Dashboard;

class dashbControlller implements dashbInterface
{
    public function getTopArticles(int $limit = 3, int $days = 7): array {}
    public function getPopularCategories(int $limit = 3, int $days = 7): array {}
    public function getRecentArticlesPerformance(int $limit = 5): array {}
    public function getMostActiveAuthors(int $limit = 3, int $days = 30): array {}
    public function getDashboardAnalytics(): array {}
}

<?php

namespace App\Repositories\Dashboard;

use App\Models\categorie;
use App\Models\post;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class dashbControlller implements dashbInterface
{
    public function getUserGrowth(): array
    {
        try {
            $totalUsers = User::count();
            $newUsersLastWeek = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            return [
                'total_users' => $totalUsers,
                'new_users_last_week' => $newUsersLastWeek
            ];
        } catch (Exception $e) {
            Log::error("error: " . $e->getMessage());
            throw new Exception('Error retrieving user growth');
        }
    }

    public function getContentOverview(): array
    {
        try {
            $totalPosts = post::count();
            $totalCategories = categorie::count();

            return [
                'total_posts' => $totalPosts,
                'total_categories' => $totalCategories
            ];
        } catch (Exception $e) {
            Log::error("error: " . $e->getMessage());
            throw new Exception('Error retrieving content overview');
        }
    }

    public function getEngagementSummary(): array
    {
        try {
            $totalViews = post::sum('views');
            $totalLikews = post::sum('likes');

            return [
                'total_views' => $totalViews,
                'total_likes' => $totalLikews
            ];
        } catch (Exception $e) {
            Log::error("error: " . $e->getMessage());
            throw new Exception('Error retrieving engagement summary');
        }
    }

    public function getTopPerformingPost(): array
    {
        try {
            $topPost = post::orderBy('views', 'desc')->first();

            return [
                'title' => $topPost ? $topPost->title : 'No posts yet',
                'views' => $topPost ? $topPost->views : 0
            ];
        } catch (Exception $e) {
            Log::error("error: " . $e->getMessage());
            throw new Exception('Error retrieving top performing post');
        }
    }
}

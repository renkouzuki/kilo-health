<?php

namespace App\Http\Controllers;

use App\Repositories\Dashboard\dashbInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $Repository;

    public function __construct(dashbInterface $repository)
    {
        $this->Repository = $repository;
    }

    public function getDashboardAnalytics(): JsonResponse
    {
        try {
            $data = [
                'user_growth' => $this->Repository->getUserGrowth(),
                'content_overview' => $this->Repository->getContentOverview(),
                'engagement_summary' => $this->Repository->getEngagementSummary(),
                'top_performing_post' => $this->Repository->getTopPerformingPost(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching analytics data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

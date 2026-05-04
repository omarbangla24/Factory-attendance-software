<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get dashboard summary data
     */
    public function summary(): JsonResponse
    {
        $data = $this->dashboardService->getDashboardData();

        return response()->json([
            'data' => [
                'todays_summary' => $data['todays_summary'],
                'month_summary' => $data['month_summary'],
                'salary_summary' => $data['salary_summary'],
                'recent_advances' => $data['recent_advances'],
                'recent_payments' => $data['recent_payments'],
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'date' => now()->toDateString(),
                'month' => now()->format('Y-m'),
            ],
        ]);
    }
}

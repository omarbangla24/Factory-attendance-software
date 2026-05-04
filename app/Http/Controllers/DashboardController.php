<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): View
    {
        $data = $this->dashboardService->getDashboardData();

        return view('dashboard', [
            'todays_summary' => $data['todays_summary'],
            'month_summary' => $data['month_summary'],
            'salary_summary' => $data['salary_summary'],
            'recent_advances' => $data['recent_advances'],
            'recent_payments' => $data['recent_payments'],
        ]);
    }
}

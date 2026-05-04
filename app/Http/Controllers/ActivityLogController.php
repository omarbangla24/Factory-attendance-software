<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.manage');
    }

    public function index(Request $request)
    {
        $query = Activity::query();

        // Filter by model type
        if ($request->filled('model')) {
            $modelMap = [
                'employee' => 'App\\Models\\Employee',
                'attendance' => 'App\\Models\\Attendance',
                'advance' => 'App\\Models\\Advance',
                'salary_sheet' => 'App\\Models\\SalarySheet',
                'salary_payment' => 'App\\Models\\SalaryPayment',
            ];

            if (isset($modelMap[$request->model])) {
                $query->where('subject_type', $modelMap[$request->model]);
            }
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('description', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('activity-logs.index', compact('logs'));
    }

    public function show(Activity $activity)
    {
        return view('activity-logs.show', compact('activity'));
    }
}

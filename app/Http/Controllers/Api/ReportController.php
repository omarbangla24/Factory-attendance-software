<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * GET /api/reports/monthly-hajira
     */
    public function monthlyHajira(Request $request)
    {
        $month = $request->input('month', \Carbon\Carbon::now()->format('Y-m'));
        $employeeId = $request->input('employee_id');

        $report = $this->reportService->getMonthlyHajiraReport($month, $employeeId);

        return response()->json([
            'data' => $report,
            'meta' => [
                'month' => $month,
                'employee_id' => $employeeId,
                'record_count' => count($report),
                'total_hajira' => collect($report)->sum('total_hajira'),
                'total_present' => collect($report)->sum('present_days'),
                'total_absent' => collect($report)->sum('absent_days'),
            ],
        ]);
    }

    /**
     * GET /api/reports/overtime
     */
    public function overtime(Request $request)
    {
        $fromDate = $request->input('from_date', \Carbon\Carbon::now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', \Carbon\Carbon::now()->format('Y-m-d'));
        $employeeId = $request->input('employee_id');

        $report = $this->reportService->getOvertimeReport($fromDate, $toDate, $employeeId);

        return response()->json([
            'data' => $report,
            'meta' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'employee_id' => $employeeId,
                'record_count' => count($report),
                'total_hours' => collect($report)->sum('total_overtime_hours'),
                'total_amount' => collect($report)->sum('overtime_amount'),
            ],
        ]);
    }

    /**
     * GET /api/reports/absent
     */
    public function absent(Request $request)
    {
        $fromDate = $request->input('from_date', \Carbon\Carbon::now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', \Carbon\Carbon::now()->format('Y-m-d'));
        $employeeId = $request->input('employee_id');

        $report = $this->reportService->getAbsentReport($fromDate, $toDate, $employeeId);

        return response()->json([
            'data' => $report,
            'meta' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'employee_id' => $employeeId,
                'record_count' => count($report),
                'total_absent_days' => collect($report)->sum('total_absent_days'),
            ],
        ]);
    }

    /**
     * GET /api/reports/advance
     */
    public function advance(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $employeeId = $request->input('employee_id');

        $report = $this->reportService->getAdvanceReport($fromDate, $toDate, $employeeId);

        return response()->json([
            'data' => $report,
            'meta' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'employee_id' => $employeeId,
                'record_count' => count($report),
                'total_advance' => collect($report)->sum('total_advance'),
                'total_deducted' => collect($report)->sum('total_deducted'),
                'total_remaining' => collect($report)->sum('remaining_balance'),
            ],
        ]);
    }

    /**
     * GET /api/reports/salary-sheet
     */
    public function salarySheet(Request $request)
    {
        $month = $request->input('month', \Carbon\Carbon::now()->format('Y-m'));
        $employeeId = $request->input('employee_id');

        $report = $this->reportService->getSalarySheetReport($month, $employeeId);

        return response()->json([
            'data' => $report,
            'meta' => [
                'month' => $month,
                'employee_id' => $employeeId,
                'record_count' => count($report),
                'total_net_salary' => collect($report)->sum('net_salary'),
                'total_paid' => collect($report)->sum('paid_amount'),
                'total_due' => collect($report)->sum('due_amount'),
            ],
        ]);
    }

    /**
     * GET /api/reports/payment
     */
    public function payment(Request $request)
    {
        $fromDate = $request->input('from_date', \Carbon\Carbon::now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', \Carbon\Carbon::now()->format('Y-m-d'));
        $employeeId = $request->input('employee_id');

        $data = $this->reportService->getPaymentReport($fromDate, $toDate, $employeeId);

        return response()->json([
            'data' => $data['payments'],
            'meta' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'employee_id' => $employeeId,
                'record_count' => count($data['payments']),
                'total_paid' => $data['summary']['total_paid'],
                'payment_count' => $data['summary']['payment_count'],
                'method_summary' => $data['summary']['method_summary'],
            ],
        ]);
    }

    /**
     * GET /api/reports/employee-ledger/{employee_id}
     */
    public function employeeLedger($employeeId)
    {
        $ledger = $this->reportService->getEmployeeLedger($employeeId);

        return response()->json(['data' => $ledger]);
    }

    /**
     * GET /api/reports/accounts-summary
     */
    public function accountsSummary(Request $request)
    {
        $month = $request->input('month', \Carbon\Carbon::now()->format('Y-m'));

        $data = $this->reportService->getAccountsSummary($month);

        return response()->json(['data' => $data]);
    }
}

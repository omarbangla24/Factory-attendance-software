<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Monthly Hajira Report
     */
    public function monthlyHajira(Request $request): View
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $employeeId = $request->input('employee_id', '');

        $report = $this->reportService->getMonthlyHajiraReport($month, $employeeId ?: null);

        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $months = $this->getAvailableMonths();

        // Calculate totals
        $totals = [
            'total_hajira' => collect($report)->sum('total_hajira'),
            'total_present' => collect($report)->sum('present_days'),
            'total_absent' => collect($report)->sum('absent_days'),
        ];

        return view('reports.monthly-hajira', compact('report', 'month', 'employeeId', 'employees', 'months', 'totals'));
    }

    /**
     * Overtime Report
     */
    public function overtime(Request $request): View
    {
        $fromDate = $request->input('from_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        $employeeId = $request->input('employee_id', '');

        $report = $this->reportService->getOvertimeReport($fromDate, $toDate, $employeeId ?: null);

        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        $totals = [
            'total_hours' => collect($report)->sum('total_overtime_hours'),
            'total_amount' => collect($report)->sum('overtime_amount'),
        ];

        return view('reports.overtime', compact('report', 'fromDate', 'toDate', 'employeeId', 'employees', 'totals'));
    }

    /**
     * Absent Report
     */
    public function absent(Request $request): View
    {
        $fromDate = $request->input('from_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        $employeeId = $request->input('employee_id', '');

        $report = $this->reportService->getAbsentReport($fromDate, $toDate, $employeeId ?: null);

        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        $totals = [
            'total_absence_records' => collect($report)->sum('total_absent_days'),
        ];

        return view('reports.absent', compact('report', 'fromDate', 'toDate', 'employeeId', 'employees', 'totals'));
    }

    /**
     * Advance Report
     */
    public function advance(Request $request): View
    {
        $fromDate = $request->input('from_date', '');
        $toDate = $request->input('to_date', '');
        $employeeId = $request->input('employee_id', '');

        $report = $this->reportService->getAdvanceReport($fromDate ?: null, $toDate ?: null, $employeeId ?: null);

        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        $totals = [
            'total_advance' => collect($report)->sum('total_advance'),
            'total_deducted' => collect($report)->sum('total_deducted'),
            'total_remaining' => collect($report)->sum('remaining_balance'),
        ];

        return view('reports.advance', compact('report', 'fromDate', 'toDate', 'employeeId', 'employees', 'totals'));
    }

    /**
     * Salary Sheet Report
     */
    public function salarySheet(Request $request): View
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $employeeId = $request->input('employee_id', '');

        $report = $this->reportService->getSalarySheetReport($month, $employeeId ?: null);

        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $months = $this->getAvailableMonths();

        $totals = [
            'total_basic' => collect($report)->sum('basic_amount'),
            'total_overtime' => collect($report)->sum('overtime_amount'),
            'total_advance_deducted' => collect($report)->sum('advance_deducted'),
            'total_adjustments' => collect($report)->sum('adjustment_amount'),
            'total_net_salary' => collect($report)->sum('net_salary'),
            'total_paid' => collect($report)->sum('paid_amount'),
            'total_due' => collect($report)->sum('due_amount'),
        ];

        return view('reports.salary-sheet', compact('report', 'month', 'employeeId', 'employees', 'months', 'totals'));
    }

    /**
     * Payment Report
     */
    public function payment(Request $request): View
    {
        $fromDate = $request->input('from_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        $employeeId = $request->input('employee_id', '');

        $data = $this->reportService->getPaymentReport($fromDate, $toDate, $employeeId ?: null);

        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        return view('reports.payment', array_merge(
            $data,
            compact('fromDate', 'toDate', 'employeeId', 'employees')
        ));
    }

    /**
     * Employee Ledger
     */
    public function employeeLedger(Request $request): View
    {
        $employeeId = $request->input('employee_id', Employee::where('status', 'active')->first()->id ?? null);

        if (!$employeeId) {
            return redirect()->back()->with('error', 'No active employees found');
        }

        $ledger = $this->reportService->getEmployeeLedger($employeeId);
        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        return view('reports.employee-ledger', compact('ledger', 'employeeId', 'employees'));
    }

    /**
     * Accounts Summary
     */
    public function accountsSummary(Request $request): View
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $data = $this->reportService->getAccountsSummary($month);
        $months = $this->getAvailableMonths();

        return view('reports.accounts-summary', array_merge($data, compact('month', 'months')));
    }

    /**
     * Helper: Get available months from salary sheets
     */
    protected function getAvailableMonths()
    {
        $months = \App\Models\SalarySheet::select('month')
            ->distinct()
            ->orderBy('month', 'desc')
            ->limit(12)
            ->pluck('month')
            ->toArray();

        return array_combine($months, $months);
    }
}

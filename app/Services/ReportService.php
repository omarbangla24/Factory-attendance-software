<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Advance;
use App\Models\SalarySheet;
use App\Models\SalaryPayment;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get Monthly Hajira Report
     */
    public function getMonthlyHajiraReport($month = null, $employeeId = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');

        $query = Attendance::where('date', '>=', $month . '-01')
            ->where('date', '<', Carbon::createFromFormat('Y-m', $month)->addMonth()->format('Y-m-01'))
            ->with('employee');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $attendances = $query->get();

        $report = [];
        foreach ($attendances->groupBy('employee_id') as $empId => $records) {
            $employee = $records->first()->employee;
            $report[] = [
                'employee_id' => $empId,
                'employee_name' => $employee->name,
                'department' => $employee->department,
                'total_hajira' => $records->sum('hajira_value'),
                'present_days' => $records->where('hajira_type', '!=', 'absent')->count(),
                'absent_days' => $records->where('hajira_type', 'absent')->count(),
            ];
        }

        usort($report, fn($a, $b) => strcmp($a['employee_name'], $b['employee_name']));
        return $report;
    }

    /**
     * Get Overtime Report
     */
    public function getOvertimeReport($fromDate = null, $toDate = null, $employeeId = null)
    {
        $fromDate = $fromDate ? Carbon::createFromFormat('Y-m-d', $fromDate) : Carbon::now()->subMonth();
        $toDate = $toDate ? Carbon::createFromFormat('Y-m-d', $toDate) : Carbon::now();

        $query = Attendance::whereBetween('date', [$fromDate, $toDate])
            ->where('overtime_hours', '>', 0)
            ->with('employee');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $attendances = $query->get();

        $report = [];
        foreach ($attendances->groupBy('employee_id') as $empId => $records) {
            $employee = $records->first()->employee;
            $totalOvertimeHours = $records->sum('overtime_hours');
            $overtimeAmount = $totalOvertimeHours * $employee->overtime_rate;

            $report[] = [
                'employee_id' => $empId,
                'employee_name' => $employee->name,
                'department' => $employee->department,
                'total_overtime_hours' => $totalOvertimeHours,
                'overtime_rate' => $employee->overtime_rate,
                'overtime_amount' => $overtimeAmount,
            ];
        }

        usort($report, fn($a, $b) => strcmp($a['employee_name'], $b['employee_name']));
        return $report;
    }

    /**
     * Get Absent Report
     */
    public function getAbsentReport($fromDate = null, $toDate = null, $employeeId = null)
    {
        $fromDate = $fromDate ? Carbon::createFromFormat('Y-m-d', $fromDate) : Carbon::now()->subMonth();
        $toDate = $toDate ? Carbon::createFromFormat('Y-m-d', $toDate) : Carbon::now();

        $query = Attendance::whereBetween('date', [$fromDate, $toDate])
            ->where('hajira_type', 'absent')
            ->with('employee')
            ->orderBy('date', 'desc');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $absences = $query->get();

        $report = [];
        foreach ($absences->groupBy('employee_id') as $empId => $records) {
            $employee = $records->first()->employee;
            $dates = $records->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();

            $report[] = [
                'employee_id' => $empId,
                'employee_name' => $employee->name,
                'department' => $employee->department,
                'total_absent_days' => $records->count(),
                'absent_dates' => $dates,
            ];
        }

        usort($report, fn($a, $b) => strcmp($a['employee_name'], $b['employee_name']));
        return $report;
    }

    /**
     * Get Advance Report
     */
    public function getAdvanceReport($fromDate = null, $toDate = null, $employeeId = null)
    {
        $fromDate = $fromDate ? Carbon::createFromFormat('Y-m-d', $fromDate) : null;
        $toDate = $toDate ? Carbon::createFromFormat('Y-m-d', $toDate) : null;

        $query = Advance::with('employee');

        if ($fromDate && $toDate) {
            $query->whereBetween('date', [$fromDate, $toDate]);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $advances = $query->get();

        $report = [];
        foreach ($advances->groupBy('employee_id') as $empId => $records) {
            $employee = $records->first()->employee;
            $totalAdvance = $records->sum('amount');

            // Calculate deducted amount from advance_deductions
            $deducted = \App\Models\AdvanceDeduction::whereIn('advance_id', $records->pluck('id'))->sum('amount');
            $remaining = $totalAdvance - $deducted;

            $report[] = [
                'employee_id' => $empId,
                'employee_name' => $employee->name,
                'department' => $employee->department,
                'total_advance' => $totalAdvance,
                'total_deducted' => $deducted,
                'remaining_balance' => $remaining,
            ];
        }

        usort($report, fn($a, $b) => strcmp($a['employee_name'], $b['employee_name']));
        return $report;
    }

    /**
     * Get Salary Sheet Report
     */
    public function getSalarySheetReport($month = null, $employeeId = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');

        $query = SalarySheet::where('month', $month)
            ->with('employee');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $salaries = $query->orderBy('employee_id')->get();

        $report = [];
        foreach ($salaries as $salary) {
            $report[] = [
                'employee_id' => $salary->employee_id,
                'employee_name' => $salary->employee->name,
                'department' => $salary->employee->department,
                'month' => $salary->month,
                'basic_amount' => $salary->basic_amount,
                'overtime_amount' => $salary->overtime_amount,
                'advance_deducted' => $salary->advance_deducted,
                'adjustment_amount' => $salary->adjustment_amount,
                'net_salary' => $salary->net_salary,
                'paid_amount' => $salary->paid_amount,
                'due_amount' => $salary->due_amount,
                'status' => $salary->status,
            ];
        }

        return $report;
    }

    /**
     * Get Payment Report
     */
    public function getPaymentReport($fromDate = null, $toDate = null, $employeeId = null)
    {
        $fromDate = $fromDate ? Carbon::createFromFormat('Y-m-d', $fromDate) : Carbon::now()->subMonth();
        $toDate = $toDate ? Carbon::createFromFormat('Y-m-d', $toDate) : Carbon::now();

        $query = SalaryPayment::whereBetween('payment_date', [$fromDate, $toDate])
            ->with('employee');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $totalPaid = $payments->sum('amount');
        $methodSummary = $payments->groupBy('payment_method')->map(fn($group) => [
            'count' => $group->count(),
            'total' => $group->sum('amount'),
        ]);

        return [
            'payments' => $payments->map(fn($p) => [
                'employee_id' => $p->employee_id,
                'employee_name' => $p->employee->name,
                'payment_date' => $p->payment_date->format('Y-m-d'),
                'amount' => $p->amount,
                'payment_method' => $p->payment_method,
                'note' => $p->note,
            ])->toArray(),
            'summary' => [
                'total_paid' => $totalPaid,
                'payment_count' => $payments->count(),
                'method_summary' => $methodSummary->toArray(),
            ],
        ];
    }

    /**
     * Get Employee Ledger
     */
    public function getEmployeeLedger($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        // Attendances
        $attendances = Attendance::where('employee_id', $employeeId)
            ->orderBy('date', 'desc')
            ->get();

        // Advances
        $advances = Advance::where('employee_id', $employeeId)
            ->orderBy('date', 'desc')
            ->get();

        $totalAdvanceGiven = $advances->sum('amount');
        $totalAdvanceDeducted = \App\Models\AdvanceDeduction::whereIn('advance_id', $advances->pluck('id'))->sum('amount');
        $currentAdvanceBalance = $totalAdvanceGiven - $totalAdvanceDeducted;

        // Salary sheets
        $salarySheets = SalarySheet::where('employee_id', $employeeId)
            ->orderBy('month', 'desc')
            ->get();

        // Payments
        $payments = SalaryPayment::where('employee_id', $employeeId)
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalDueSalary = $salarySheets->sum('due_amount');

        return [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'department' => $employee->department,
                'phone' => $employee->phone,
                'hajira_rate' => $employee->hajira_rate,
                'overtime_rate' => $employee->overtime_rate,
                'status' => $employee->status,
            ],
            'attendances' => $attendances->map(fn($a) => [
                'date' => $a->date->format('Y-m-d'),
                'hajira_type' => $a->hajira_type,
                'hajira_value' => $a->hajira_value,
                'overtime_hours' => $a->overtime_hours,
                'note' => $a->note,
            ])->toArray(),
            'advances' => $advances->map(fn($a) => [
                'date' => $a->date->format('Y-m-d'),
                'amount' => $a->amount,
                'reason' => $a->reason,
                'note' => $a->note,
            ])->toArray(),
            'salary_sheets' => $salarySheets->map(fn($s) => [
                'month' => $s->month,
                'net_salary' => $s->net_salary,
                'paid_amount' => $s->paid_amount,
                'due_amount' => $s->due_amount,
                'status' => $s->status,
            ])->toArray(),
            'payments' => $payments->map(fn($p) => [
                'payment_date' => $p->payment_date->format('Y-m-d'),
                'amount' => $p->amount,
                'payment_method' => $p->payment_method,
            ])->toArray(),
            'summary' => [
                'total_advance_given' => $totalAdvanceGiven,
                'total_advance_deducted' => $totalAdvanceDeducted,
                'current_advance_balance' => $currentAdvanceBalance,
                'total_salary_generated' => $salarySheets->count(),
                'total_paid' => $payments->sum('amount'),
                'total_due' => $totalDueSalary,
            ],
        ];
    }

    /**
     * Get Accounts Summary
     */
    public function getAccountsSummary($month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');

        $salarySheets = SalarySheet::where('month', $month)->get();
        $payments = SalaryPayment::whereHas('salarySheet', fn($q) => $q->where('month', $month))->get();
        $advances = Advance::where('date', '>=', $month . '-01')
            ->where('date', '<', Carbon::createFromFormat('Y-m', $month)->addMonth()->format('Y-m-01'))
            ->get();

        $totalAdvanceDeducted = \App\Models\AdvanceDeduction::whereIn(
            'salary_sheet_id',
            $salarySheets->pluck('id')
        )->sum('amount');

        return [
            'month' => $month,
            'summary' => [
                'total_salary' => $salarySheets->sum('net_salary'),
                'total_basic' => $salarySheets->sum('basic_amount'),
                'total_overtime_pay' => $salarySheets->sum('overtime_amount'),
                'total_advance_given' => $advances->sum('amount'),
                'total_advance_deducted' => $totalAdvanceDeducted,
                'total_adjustments' => $salarySheets->sum('adjustment_amount'),
                'total_paid' => $payments->sum('amount'),
                'total_due' => $salarySheets->sum('due_amount'),
                'employee_count' => $salarySheets->count(),
                'payment_count' => $payments->count(),
            ],
        ];
    }
}

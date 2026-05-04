<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Advance;
use App\Models\SalarySheet;
use App\Models\SalaryPayment;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get today's attendance summary
     */
    public function getTodaysSummary()
    {
        $today = Carbon::now()->toDateString();
        
        $attendances = Attendance::whereDate('date', $today)
            ->select('hajira_type', 'hajira_value', 'overtime_hours')
            ->get();
        
        return [
            'total_present' => $attendances->where('hajira_type', '!=', 'absent')->count(),
            'total_absent' => $attendances->where('hajira_type', 'absent')->count(),
            'total_hajira' => $attendances->sum('hajira_value'),
            'total_overtime_hours' => $attendances->sum('overtime_hours'),
        ];
    }

    /**
     * Get this month's attendance summary
     */
    public function getMonthSummary()
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $attendances = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->select('hajira_value', 'overtime_hours')
            ->get();

        return [
            'total_hajira' => $attendances->sum('hajira_value'),
            'total_overtime_hours' => $attendances->sum('overtime_hours'),
        ];
    }

    /**
     * Get salary and payment summary for this month
     */
    public function getSalarySummary()
    {
        $currentMonth = Carbon::now()->format('Y-m');

        // Get all salary sheets for current month
        $salarySheets = SalarySheet::where('month', $currentMonth)
            ->select('id', 'net_salary', 'paid_amount', 'due_amount')
            ->get();

        $totalPayable = $salarySheets->sum('net_salary');
        $totalPaid = $salarySheets->sum('paid_amount');
        $totalDue = $salarySheets->sum('due_amount');

        // Get advance balance (total given - total deducted across ALL months)
        $totalAdvanceGiven = Advance::sum('amount');
        $totalAdvanceDeducted = \DB::table('advance_deductions')
            ->sum('amount');
        
        $advanceBalance = $totalAdvanceGiven - $totalAdvanceDeducted;

        return [
            'total_salary_payable' => $totalPayable,
            'total_paid_this_month' => $totalPaid,
            'total_due' => $totalDue,
            'total_advance_balance' => $advanceBalance,
        ];
    }

    /**
     * Get recent advances (last 5)
     */
    public function getRecentAdvances($limit = 5)
    {
        return Advance::with('employee:id,name')
            ->latest('date')
            ->limit($limit)
            ->select('id', 'employee_id', 'date', 'amount', 'reason')
            ->get()
            ->map(function ($advance) {
                return [
                    'id' => $advance->id,
                    'employee_name' => $advance->employee->name ?? 'N/A',
                    'date' => $advance->date,
                    'amount' => $advance->amount,
                    'reason' => $advance->reason,
                ];
            });
    }

    /**
     * Get recent payments (last 5)
     */
    public function getRecentPayments($limit = 5)
    {
        return SalaryPayment::with(['salarySheet:id,employee_id', 'salarySheet.employee:id,name'])
            ->latest('payment_date')
            ->limit($limit)
            ->select('id', 'salary_sheet_id', 'employee_id', 'payment_date', 'amount', 'payment_method')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'employee_name' => optional($payment->salarySheet->employee)->name ?? 'N/A',
                    'payment_date' => $payment->payment_date,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                ];
            });
    }

    /**
     * Get complete dashboard data
     */
    public function getDashboardData()
    {
        return [
            'todays_summary' => $this->getTodaysSummary(),
            'month_summary' => $this->getMonthSummary(),
            'salary_summary' => $this->getSalarySummary(),
            'recent_advances' => $this->getRecentAdvances(),
            'recent_payments' => $this->getRecentPayments(),
        ];
    }
}

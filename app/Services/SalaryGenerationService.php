<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SalarySheet;
use App\Models\Attendance;
use App\Models\Advance;
use App\Models\AdvanceDeduction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SalaryGenerationService
{
    public function generateSalaries($month, $employeeIds = null)
    {
        // Parse month (YYYY-MM format)
        $monthDate = Carbon::createFromFormat('Y-m', $month);
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();

        // Get employees to process
        $query = Employee::where('status', 'active');
        if ($employeeIds) {
            $query->whereIn('id', $employeeIds);
        }
        $employees = $query->get();

        $salarySheets = [];

        foreach ($employees as $employee) {
            // Check if salary sheet already exists for this month
            $existing = SalarySheet::where('employee_id', $employee->id)
                ->where('month', $month)
                ->first();

            if ($existing) {
                // If draft, can regenerate. If locked, skip.
                if ($existing->status === 'draft') {
                    $salarySheets[] = $this->regenerateSalarySheet($existing);
                }
                // If locked, don't regenerate
                continue;
            }

            // Create new salary sheet
            $salarySheets[] = $this->createSalarySheet($employee, $month, $monthStart, $monthEnd);
        }

        return $salarySheets;
    }

    public function createSalarySheet(Employee $employee, $month, $monthStart, $monthEnd)
    {
        // Calculate attendance statistics for the month
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $totalHajira = $attendances->sum('hajira_value');
        $totalOvertimeHours = $attendances->sum('overtime_hours');
        $absentDays = $attendances->where('hajira_type', 'absent')->count();

        // Calculate salary amounts
        $basicAmount = $totalHajira * $employee->hajira_rate;
        $overtimeAmount = $totalOvertimeHours * $employee->overtime_rate;

        // Calculate net salary before advance deduction
        $salaryPayable = $basicAmount + $overtimeAmount; // adjustment_amount will be added in UI

        // Create salary sheet
        $salarySheet = SalarySheet::create([
            'employee_id' => $employee->id,
            'month' => $month,
            'total_hajira' => $totalHajira,
            'total_overtime_hours' => $totalOvertimeHours,
            'absent_days' => $absentDays,
            'basic_amount' => $basicAmount,
            'overtime_amount' => $overtimeAmount,
            'adjustment_amount' => 0,
            'advance_deducted' => 0,
            'net_salary' => $salaryPayable,
            'paid_amount' => 0,
            'due_amount' => $salaryPayable,
            'status' => 'draft',
        ]);

        // Process advance deductions
        $this->processAdvanceDeductions($employee, $salarySheet);

        return $salarySheet;
    }

    public function regenerateSalarySheet(SalarySheet $salarySheet)
    {
        if ($salarySheet->status === 'locked') {
            throw new \Exception('Cannot regenerate locked salary sheet');
        }

        // Parse month dates
        $monthDate = Carbon::createFromFormat('Y-m', $salarySheet->month);
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();

        // Delete existing advance deductions for this salary sheet
        AdvanceDeduction::where('salary_sheet_id', $salarySheet->id)->delete();

        // Recalculate attendance statistics
        $attendances = Attendance::where('employee_id', $salarySheet->employee_id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $totalHajira = $attendances->sum('hajira_value');
        $totalOvertimeHours = $attendances->sum('overtime_hours');
        $absentDays = $attendances->where('hajira_type', 'absent')->count();

        // Get employee for rates
        $employee = $salarySheet->employee;

        // Recalculate amounts
        $basicAmount = $totalHajira * $employee->hajira_rate;
        $overtimeAmount = $totalOvertimeHours * $employee->overtime_rate;

        // Update salary sheet
        $salarySheet->update([
            'total_hajira' => $totalHajira,
            'total_overtime_hours' => $totalOvertimeHours,
            'absent_days' => $absentDays,
            'basic_amount' => $basicAmount,
            'overtime_amount' => $overtimeAmount,
            // Keep adjustment_amount as is
            'net_salary' => $basicAmount + $overtimeAmount + $salarySheet->adjustment_amount,
            'due_amount' => 0, // Will be updated after advance deductions
        ]);

        // Reprocess advance deductions
        $this->processAdvanceDeductions($employee, $salarySheet);

        return $salarySheet;
    }

    private function processAdvanceDeductions(Employee $employee, SalarySheet $salarySheet)
    {
        // Get employee's advances ordered by date (oldest first)
        $advances = Advance::where('employee_id', $employee->id)
            ->orderBy('date')
            ->get();

        $salaryPayable = $salarySheet->basic_amount + $salarySheet->overtime_amount + $salarySheet->adjustment_amount;
        $totalDeducted = 0;

        foreach ($advances as $advance) {
            // Calculate how much of this advance has been deducted
            $alreadyDeducted = AdvanceDeduction::where('advance_id', $advance->id)
                ->sum('amount');

            $remainingBalance = $advance->amount - $alreadyDeducted;

            // Skip if nothing left to deduct
            if ($remainingBalance <= 0) {
                continue;
            }

            // Calculate how much to deduct
            $deductionAmount = min($remainingBalance, $salaryPayable - $totalDeducted);

            if ($deductionAmount > 0) {
                // Create advance deduction record
                AdvanceDeduction::create([
                    'advance_id' => $advance->id,
                    'employee_id' => $employee->id,
                    'salary_sheet_id' => $salarySheet->id,
                    'amount' => $deductionAmount,
                ]);

                $totalDeducted += $deductionAmount;
            }

            // Stop if we've deducted as much as possible
            if ($totalDeducted >= $salaryPayable) {
                break;
            }
        }

        // Update salary sheet with deduction total
        $netSalary = $salaryPayable - $totalDeducted;
        $salarySheet->update([
            'advance_deducted' => $totalDeducted,
            'net_salary' => $netSalary,
            'due_amount' => $netSalary,
        ]);
    }

    public function applyAdvancesOnly(SalarySheet $salarySheet)
    {
        if ($salarySheet->status === 'locked') {
            throw new \Exception('Cannot modify locked salary sheet');
        }

        // Delete only the advance deductions tied to this sheet, then re-run
        AdvanceDeduction::where('salary_sheet_id', $salarySheet->id)->delete();

        // Reset advance_deducted, net_salary, due_amount before re-processing
        $salaryPayable = $salarySheet->basic_amount + $salarySheet->overtime_amount + $salarySheet->adjustment_amount;
        $salarySheet->update([
            'advance_deducted' => 0,
            'net_salary' => $salaryPayable,
            'due_amount' => $salaryPayable - $salarySheet->paid_amount,
        ]);

        $this->processAdvanceDeductions($salarySheet->employee, $salarySheet);

        return $salarySheet->fresh();
    }

    public function hasPendingAdvances(SalarySheet $salarySheet): bool
    {
        $advances = Advance::where('employee_id', $salarySheet->employee_id)->get();

        foreach ($advances as $advance) {
            $totalDeducted = AdvanceDeduction::where('advance_id', $advance->id)->sum('amount');
            if (($advance->amount - $totalDeducted) > 0.001) {
                return true;
            }
        }

        return false;
    }

    public function updateAndLock(SalarySheet $salarySheet, $adjustmentAmount)
    {
        if ($salarySheet->status === 'locked') {
            throw new \Exception('Cannot modify locked salary sheet');
        }

        // Update adjustment amount
        $salarySheet->adjustment_amount = $adjustmentAmount;
        $salarySheet->save();

        // Reprocess with new adjustment
        $salaryPayable = $salarySheet->basic_amount + $salarySheet->overtime_amount + $adjustmentAmount;
        
        // Delete existing advance deductions
        AdvanceDeduction::where('salary_sheet_id', $salarySheet->id)->delete();

        // Recalculate advance deductions with new adjustment
        $this->processAdvanceDeductions($salarySheet->employee, $salarySheet);

        // Lock the salary sheet
        $salarySheet->update([
            'status' => 'locked',
            'locked_at' => now(),
        ]);

        return $salarySheet;
    }
}

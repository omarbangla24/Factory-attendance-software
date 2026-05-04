<?php

namespace App\Services;

use App\Models\SalaryPayment;
use App\Models\SalarySheet;
use Carbon\Carbon;

class PaymentService
{
    public function recordPayment($salarySheetId, $amount, $paymentDate, $paymentMethod, $note = null)
    {
        // Validate salary sheet exists
        $salarySheet = SalarySheet::find($salarySheetId);
        if (!$salarySheet) {
            throw new \Exception('Salary sheet not found');
        }

        // Check if salary is locked
        if ($salarySheet->status !== 'locked') {
            throw new \Exception('Can only pay locked salary sheets');
        }

        // Validate amount
        if ($amount <= 0) {
            throw new \Exception('Payment amount must be greater than 0');
        }

        // Prevent overpayment
        $remainingDue = $salarySheet->due_amount;
        if ($amount > $remainingDue) {
            throw new \Exception(
                'Payment amount (' . $amount . ') exceeds due amount (' . $remainingDue . ')'
            );
        }

        // Validate payment method
        $validMethods = ['cash', 'bank', 'mobile_banking'];
        if (!in_array($paymentMethod, $validMethods)) {
            throw new \Exception('Invalid payment method');
        }

        // Create payment record
        $payment = SalaryPayment::create([
            'salary_sheet_id' => $salarySheetId,
            'employee_id' => $salarySheet->employee_id,
            'payment_date' => $paymentDate,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'note' => $note,
            'created_by' => auth()->id(),
        ]);

        // Update salary sheet
        $this->updateSalarySheetStatus($salarySheet);

        return $payment;
    }

    public function updateSalarySheetStatus(SalarySheet $salarySheet)
    {
        // Recalculate paid amount
        $totalPaid = SalaryPayment::where('salary_sheet_id', $salarySheet->id)
            ->sum('amount');

        // Calculate due amount
        $duAmount = $salarySheet->net_salary - $totalPaid;

        // Determine status
        if ($duAmount <= 0) {
            // Fully paid
            $status = 'paid';
            $duAmount = 0;
        } elseif ($totalPaid > 0) {
            // Partial payment
            $status = 'partial';
        } else {
            // No payment yet - keep locked status
            $status = $salarySheet->status;
        }

        // Update salary sheet
        $salarySheet->update([
            'paid_amount' => $totalPaid,
            'due_amount' => max(0, $duAmount),
            'status' => $status,
        ]);

        return $salarySheet;
    }

    public function reversePayment(SalaryPayment $payment)
    {
        $salarySheet = $payment->salarySheet;

        // Delete payment
        $payment->delete();

        // Update salary sheet status
        $this->updateSalarySheetStatus($salarySheet);

        return $salarySheet;
    }

    public function getPaymentHistory($salarySheetId)
    {
        return SalaryPayment::where('salary_sheet_id', $salarySheetId)
            ->orderBy('payment_date', 'desc')
            ->with('createdByUser')
            ->get();
    }

    public function getEmployeePaymentHistory($employeeId, $month = null)
    {
        $query = SalaryPayment::where('employee_id', $employeeId);

        if ($month) {
            // Filter by month (format: YYYY-MM)
            $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $month);
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();

            $query->whereBetween('payment_date', [$monthStart, $monthEnd]);
        }

        return $query->orderBy('payment_date', 'desc')
            ->with('salarySheet', 'createdByUser')
            ->get();
    }
}

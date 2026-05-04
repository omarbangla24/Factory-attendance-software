<?php

namespace App\Http\Controllers;

use App\Models\SalarySheet;
use App\Models\SalaryPayment;
use App\Models\Employee;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request): View
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $status = $request->input('status', '');

        // Get salary sheets with their payment status
        $query = SalarySheet::where('status', '!=', 'draft')
            ->where('month', $month)
            ->with('employee', 'salaryPayments');

        if ($status) {
            $query->where('status', $status);
        }

        $salaries = $query->orderBy('employee_id')->paginate(15);

        // Calculate summary
        $summary = [
            'total_due' => $salaries->sum('due_amount'),
            'total_paid' => $salaries->sum('paid_amount'),
            'total_salary' => $salaries->sum('net_salary'),
        ];

        $months = $this->getAvailableMonths();
        $statuses = ['locked', 'partial', 'paid'];

        return view('payments.index', compact('salaries', 'month', 'status', 'summary', 'months', 'statuses'));
    }

    public function pay(SalarySheet $salarySheet): View
    {
        if ($salarySheet->status === 'draft') {
            abort(403, 'Cannot pay draft salary sheet');
        }

        $paymentHistory = $this->paymentService->getPaymentHistory($salarySheet->id);

        return view('payments.pay', compact('salarySheet', 'paymentHistory'));
    }

    public function store(Request $request, SalarySheet $salarySheet)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank,mobile_banking',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $this->paymentService->recordPayment(
                $salarySheet->id,
                $validated['amount'],
                $validated['payment_date'],
                $validated['payment_method'],
                $validated['note'] ?? null
            );

            return redirect()->route('payments.show', $salarySheet)
                ->with('success', 'Payment recorded successfully');
        } catch (\Exception $e) {
            return redirect()->route('payments.pay', $salarySheet)
                ->with('error', $e->getMessage());
        }
    }

    public function show(SalarySheet $salarySheet): View
    {
        $salarySheet->load('employee', 'salaryPayments.createdByUser');
        $paymentHistory = $salarySheet->salaryPayments()->orderBy('payment_date', 'desc')->get();

        return view('payments.show', compact('salarySheet', 'paymentHistory'));
    }

    public function employeePayments(Employee $employee, Request $request): View
    {
        $month = $request->input('month', '');

        $paymentHistory = $this->paymentService->getEmployeePaymentHistory(
            $employee->id,
            $month ? $month : null
        );

        // Calculate summary
        $summary = [
            'total_paid' => $paymentHistory->sum('amount'),
            'total_payments' => count($paymentHistory),
        ];

        $months = $this->getAvailableMonths();

        return view('payments.employee-history', compact('employee', 'paymentHistory', 'summary', 'month', 'months'));
    }

    public function destroy(SalaryPayment $payment)
    {
        $salarySheet = $payment->salarySheet;

        try {
            $this->paymentService->reversPayment($payment);

            return redirect()->route('payments.show', $salarySheet)
                ->with('success', 'Payment reversed successfully');
        } catch (\Exception $e) {
            return redirect()->route('payments.show', $salarySheet)
                ->with('error', $e->getMessage());
        }
    }

    private function getAvailableMonths()
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->format('F Y');
        }
        return $months;
    }
}

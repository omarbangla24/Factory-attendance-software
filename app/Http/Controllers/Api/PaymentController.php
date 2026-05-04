<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalaryPayment;
use App\Models\SalarySheet;
use App\Models\Employee;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $month = $request->input('month', '');
        $salarySheetId = $request->input('salary_sheet_id', '');
        $employeeId = $request->input('employee_id', '');

        $query = SalaryPayment::with('salarySheet', 'employee', 'createdByUser');

        if ($month) {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();
            $query->whereBetween('payment_date', [$monthStart, $monthEnd]);
        }

        if ($salarySheetId) {
            $query->where('salary_sheet_id', $salarySheetId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $payments,
            'meta' => [
                'total' => count($payments),
                'total_amount' => $payments->sum('amount'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'salary_sheet_id' => 'required|exists:salary_sheets,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank,mobile_banking',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $payment = $this->paymentService->recordPayment(
                $validated['salary_sheet_id'],
                $validated['amount'],
                $validated['payment_date'],
                $validated['payment_method'],
                $validated['note'] ?? null
            );

            $payment->load('salarySheet', 'employee', 'createdByUser');

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => $payment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function salaryPayments($salarySheetId)
    {
        $salarySheet = SalarySheet::find($salarySheetId);

        if (!$salarySheet) {
            return response()->json([
                'success' => false,
                'message' => 'Salary sheet not found',
            ], 404);
        }

        $payments = SalaryPayment::where('salary_sheet_id', $salarySheetId)
            ->with('createdByUser')
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'salary_sheet' => $salarySheet,
                'payments' => $payments,
                'summary' => [
                    'total_paid' => $salarySheet->paid_amount,
                    'total_due' => $salarySheet->due_amount,
                    'net_salary' => $salarySheet->net_salary,
                    'status' => $salarySheet->status,
                ],
            ],
        ]);
    }

    public function employeePayments($employeeId, Request $request)
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        $month = $request->input('month', '');

        $query = SalaryPayment::where('employee_id', $employeeId)
            ->with('salarySheet', 'createdByUser');

        if ($month) {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();
            $query->whereBetween('payment_date', [$monthStart, $monthEnd]);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => $employee,
                'payments' => $payments,
                'summary' => [
                    'total_paid' => $payments->sum('amount'),
                    'total_payments' => count($payments),
                ],
            ],
        ]);
    }

    public function destroy($id)
    {
        $payment = SalaryPayment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        try {
            $salarySheet = $payment->salarySheet;
            $this->paymentService->reversPayment($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment reversed successfully',
                'data' => $salarySheet->refresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

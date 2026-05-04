<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdvanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');

        $query = Advance::with('employee', 'deductions');

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $advances = $query->orderBy('date', 'desc')->paginate($perPage);

        return response()->json($advances);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'reason' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();

        $advance = Advance::create($validated);

        return response()->json([
            'message' => 'Advance created successfully',
            'data' => $advance->load('employee'),
        ], 201);
    }

    public function show(Advance $advance): JsonResponse
    {
        $advance->load('employee', 'deductions');

        $deductedAmount = $advance->deductions->sum('amount');
        $remainingAmount = $advance->amount - $deductedAmount;

        return response()->json([
            'data' => $advance,
            'deducted_amount' => $deductedAmount,
            'remaining_amount' => $remainingAmount,
        ]);
    }

    public function update(Request $request, Advance $advance): JsonResponse
    {
        // Check if advance has deductions
        if ($advance->deductions()->count() > 0) {
            return response()->json([
                'message' => 'Cannot update advance that has been deducted',
            ], 403);
        }

        $validated = $request->validate([
            'employee_id' => 'sometimes|integer|exists:employees,id',
            'date' => 'sometimes|date',
            'amount' => 'sometimes|numeric|gt:0',
            'reason' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
        ]);

        $advance->update($validated);

        return response()->json([
            'message' => 'Advance updated successfully',
            'data' => $advance->load('employee'),
        ]);
    }

    public function destroy(Advance $advance): JsonResponse
    {
        // Check if advance has deductions
        if ($advance->deductions()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete advance that has been deducted',
            ], 403);
        }

        $advance->delete();

        return response()->json([
            'message' => 'Advance deleted successfully',
        ]);
    }

    public function employeeBalance(Employee $employee): JsonResponse
    {
        $totalAdvance = $employee->advances()->sum('amount');
        $totalDeducted = $employee->advanceDeductions()->sum('amount');
        $remainingBalance = $totalAdvance - $totalDeducted;

        return response()->json([
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'total_advance' => $totalAdvance,
            'total_deducted' => $totalDeducted,
            'remaining_balance' => $remainingBalance,
        ]);
    }
}

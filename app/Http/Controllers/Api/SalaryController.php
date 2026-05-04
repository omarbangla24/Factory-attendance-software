<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalarySheet;
use App\Models\Employee;
use App\Services\SalaryGenerationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalaryController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryGenerationService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $status = $request->input('status', '');
        $employeeId = $request->input('employee_id', '');

        $query = SalarySheet::where('month', $month)
            ->with('employee', 'deductions');

        if ($status) {
            $query->where('status', $status);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $salaries = $query->orderBy('employee_id')->get();

        return response()->json([
            'success' => true,
            'data' => $salaries,
            'meta' => [
                'month' => $month,
                'total' => count($salaries),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer|exists:employees,id',
        ]);

        try {
            $employeeIds = $validated['employee_ids'] ?? null;
            $salarySheets = $this->salaryService->generateSalaries($validated['month'], $employeeIds);

            return response()->json([
                'success' => true,
                'message' => count($salarySheets) . ' salary sheets generated/updated',
                'data' => $salarySheets,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function show($id)
    {
        $salarySheet = SalarySheet::with('employee', 'deductions.advance')->find($id);

        if (!$salarySheet) {
            return response()->json([
                'success' => false,
                'message' => 'Salary sheet not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $salarySheet,
        ]);
    }

    public function update(Request $request, $id)
    {
        $salarySheet = SalarySheet::find($id);

        if (!$salarySheet) {
            return response()->json([
                'success' => false,
                'message' => 'Salary sheet not found',
            ], 404);
        }

        if ($salarySheet->status === 'locked') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit locked salary sheet',
            ], 403);
        }

        $validated = $request->validate([
            'adjustment_amount' => 'required|numeric',
        ]);

        try {
            $this->salaryService->updateAndLock($salarySheet, $validated['adjustment_amount']);

            return response()->json([
                'success' => true,
                'message' => 'Salary sheet locked successfully',
                'data' => $salarySheet->refresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function lock($id)
    {
        $salarySheet = SalarySheet::find($id);

        if (!$salarySheet) {
            return response()->json([
                'success' => false,
                'message' => 'Salary sheet not found',
            ], 404);
        }

        if ($salarySheet->status === 'locked') {
            return response()->json([
                'success' => false,
                'message' => 'Salary sheet is already locked',
            ], 400);
        }

        try {
            $adjustment = $salarySheet->adjustment_amount ?? 0;
            $this->salaryService->updateAndLock($salarySheet, $adjustment);

            return response()->json([
                'success' => true,
                'message' => 'Salary sheet locked successfully',
                'data' => $salarySheet->refresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function regenerate($id)
    {
        $salarySheet = SalarySheet::find($id);

        if (!$salarySheet) {
            return response()->json([
                'success' => false,
                'message' => 'Salary sheet not found',
            ], 404);
        }

        if ($salarySheet->status === 'locked') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot regenerate locked salary sheet',
            ], 403);
        }

        try {
            $this->salaryService->regenerateSalarySheet($salarySheet);

            return response()->json([
                'success' => true,
                'message' => 'Salary sheet regenerated successfully',
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\SalarySheet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Search by name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $employees = $query->paginate($perPage);

        return response()->json($employees);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|unique:employees',
            'address' => 'nullable|string',
            'joining_date' => 'required|date',
            'department' => 'nullable|string',
            'hajira_rate' => 'required|numeric|min:0',
            'overtime_rate' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $employee = Employee::create($validated);

        return response()->json([
            'message' => 'Employee created successfully',
            'data' => $employee,
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json($employee);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|unique:employees,phone,' . $employee->id,
            'address' => 'sometimes|nullable|string',
            'joining_date' => 'sometimes|required|date',
            'department' => 'sometimes|nullable|string',
            'hajira_rate' => 'sometimes|required|numeric|min:0',
            'overtime_rate' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        $employee->update($validated);

        return response()->json([
            'message' => 'Employee updated successfully',
            'data' => $employee,
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        // Check if employee has attendance records
        if (Attendance::where('employee_id', $employee->id)->exists()) {
            return response()->json([
                'message' => 'Cannot delete employee with attendance records',
            ], 422);
        }

        // Check if employee has salary sheets
        if (SalarySheet::where('employee_id', $employee->id)->exists()) {
            return response()->json([
                'message' => 'Cannot delete employee with salary records',
            ], 422);
        }

        $employee->delete();

        return response()->json([
            'message' => 'Employee deleted successfully',
        ]);
    }

    public function active(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $employees = Employee::where('status', 'active')
            ->paginate($perPage);

        return response()->json($employees);
    }

    public function bulkUpdateRates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:employees,id',
            'hajira_rate' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
        ]);

        if (!$request->filled('hajira_rate') && !$request->filled('overtime_rate')) {
            return response()->json([
                'message' => 'At least one rate must be provided',
            ], 422);
        }

        $update = [];
        if ($request->filled('hajira_rate')) {
            $update['hajira_rate'] = $validated['hajira_rate'];
        }
        if ($request->filled('overtime_rate')) {
            $update['overtime_rate'] = $validated['overtime_rate'];
        }

        Employee::whereIn('id', $validated['employee_ids'])->update($update);

        return response()->json([
            'message' => 'Employee rates updated successfully',
            'updated_count' => count($validated['employee_ids']),
        ]);
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:employees,id',
            'status' => 'required|in:active,inactive',
        ]);

        Employee::whereIn('id', $validated['employee_ids'])
            ->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Employee status updated successfully',
            'updated_count' => count($validated['employee_ids']),
        ]);
    }
}

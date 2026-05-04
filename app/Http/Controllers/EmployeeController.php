<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\SalarySheet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Employee::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $employees = $query->paginate(10);
        $departments = Employee::distinct()->pluck('department');

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create(): View
    {
        $departments = Employee::distinct()->pluck('department')->push('')->sort();
        return view('employees.create', compact('departments'));
    }

    public function store(Request $request)
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

        Employee::create($validated);
        return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    }

    public function edit(Employee $employee): View
    {
        $departments = Employee::distinct()->pluck('department')->push('')->sort();
        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|unique:employees,phone,' . $employee->id,
            'address' => 'nullable|string',
            'joining_date' => 'required|date',
            'department' => 'nullable|string',
            'hajira_rate' => 'required|numeric|min:0',
            'overtime_rate' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $employee->update($validated);
        return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
    }

    public function destroy(Employee $employee)
    {
        // Check if employee has attendance records
        if (Attendance::where('employee_id', $employee->id)->exists()) {
            return redirect()->route('employees.index')->with('error', 'Cannot delete employee with attendance records');
        }

        // Check if employee has salary sheets
        if (SalarySheet::where('employee_id', $employee->id)->exists()) {
            return redirect()->route('employees.index')->with('error', 'Cannot delete employee with salary records');
        }

        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully');
    }

    public function bulkUpdateRates(Request $request)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:employees,id',
            'hajira_rate' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
        ]);

        $update = [];
        if ($request->filled('hajira_rate')) {
            $update['hajira_rate'] = $validated['hajira_rate'];
        }
        if ($request->filled('overtime_rate')) {
            $update['overtime_rate'] = $validated['overtime_rate'];
        }

        Employee::whereIn('id', $validated['employee_ids'])->update($update);

        return redirect()->route('employees.index')->with('success', 'Employee rates updated successfully');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:employees,id',
            'status' => 'required|in:active,inactive',
        ]);

        Employee::whereIn('id', $validated['employee_ids'])
            ->update(['status' => $validated['status']]);

        return redirect()->route('employees.index')->with('success', 'Employee status updated successfully');
    }
}

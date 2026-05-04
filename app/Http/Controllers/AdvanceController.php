<?php

namespace App\Http\Controllers;

use App\Models\Advance;
use App\Models\Employee;
use App\Models\AdvanceDeduction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class AdvanceController extends Controller
{
    public function index(Request $request): View
    {
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

        $advances = $query->orderBy('date', 'desc')->paginate(15);

        // Calculate balance summary
        $totals = $this->calculateTotals($advances);

        return view('advances.index', compact('advances', 'totals', 'search', 'startDate', 'endDate'));
    }

    public function create(): View
    {
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        return view('advances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'reason' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();

        Advance::create($validated);

        return redirect()->route('advances.index')
            ->with('success', 'Advance created successfully');
    }

    public function show(Advance $advance): View
    {
        $advance->load('employee', 'deductions');

        // Calculate deducted amount for this advance
        $deductedAmount = $advance->deductions->sum('amount');
        $remainingAmount = $advance->amount - $deductedAmount;

        return view('advances.show', compact('advance', 'deductedAmount', 'remainingAmount'));
    }

    public function edit(Advance $advance): View
    {
        // Check if advance has been deducted
        if ($advance->deductions->count() > 0) {
            abort(403, 'Cannot edit advance that has been deducted');
        }

        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        return view('advances.edit', compact('advance', 'employees'));
    }

    public function update(Request $request, Advance $advance)
    {
        // Check if advance has been deducted
        if ($advance->deductions->count() > 0) {
            return redirect()->route('advances.show', $advance)
                ->with('error', 'Cannot edit advance that has been deducted');
        }

        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'reason' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
        ]);

        $advance->update($validated);

        return redirect()->route('advances.show', $advance)
            ->with('success', 'Advance updated successfully');
    }

    public function destroy(Advance $advance)
    {
        // Check if advance has been deducted
        if ($advance->deductions->count() > 0) {
            return redirect()->route('advances.index')
                ->with('error', 'Cannot delete advance that has been deducted');
        }

        $advance->delete();

        return redirect()->route('advances.index')
            ->with('success', 'Advance deleted successfully');
    }

    private function calculateTotals($advances)
    {
        $totalAdvance = 0;
        $totalDeducted = 0;

        foreach ($advances as $advance) {
            $totalAdvance += $advance->amount;
            $totalDeducted += $advance->deductions->sum('amount');
        }

        return [
            'total_advance' => $totalAdvance,
            'total_deducted' => $totalDeducted,
            'remaining_balance' => $totalAdvance - $totalDeducted,
        ];
    }
}

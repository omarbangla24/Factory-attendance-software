<?php

namespace App\Http\Controllers;

use App\Models\SalarySheet;
use App\Models\Employee;
use App\Models\Setting;
use App\Services\SalaryGenerationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class SalaryController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryGenerationService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    public function index(Request $request): View
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $status = $request->input('status', '');

        $query = SalarySheet::where('month', $month)
            ->with('employee', 'deductions');

        if ($status) {
            $query->where('status', $status);
        }

        $salaries = $query->orderBy('employee_id')->paginate(15);

        // Calculate summary
        $summary = $this->calculateSummary($salaries);

        $statuses = ['draft', 'locked', 'partial', 'paid'];
        $months = $this->getAvailableMonths();
        $currencySymbol = Setting::get('currency_symbol', '৳');

        return view('salaries.index', compact('salaries', 'month', 'status', 'summary', 'statuses', 'months', 'currencySymbol'));
    }

    public function create(): View
    {
        $month = Carbon::now()->format('Y-m');
        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        return view('salaries.create', compact('month', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer|exists:employees,id',
        ]);

        $employeeIds = $validated['employee_ids'] ?? null;

        // Generate salary sheets
        $salarySheets = $this->salaryService->generateSalaries($validated['month'], $employeeIds);

        return redirect()->route('salaries.index', ['month' => $validated['month']])
            ->with('success', count($salarySheets) . ' salary sheets generated/updated');
    }

    public function show(SalarySheet $salarySheet): View
    {
        $salarySheet->load('employee', 'deductions.advance');
        $currencySymbol  = Setting::get('currency_symbol', '৳');
        $companyName     = Setting::get('company_name', 'Hajira Payroll System');
        $companyPhone    = Setting::get('company_phone', '');
        $companyAddress  = Setting::get('company_address', '');
        $hasPendingAdvances = $salarySheet->status === 'draft'
            && $this->salaryService->hasPendingAdvances($salarySheet);

        return view('salaries.show', compact(
            'salarySheet', 'currencySymbol', 'companyName', 'companyPhone', 'companyAddress', 'hasPendingAdvances'
        ));
    }

    public function edit(SalarySheet $salarySheet): View
    {
        if ($salarySheet->status === 'locked') {
            abort(403, 'Cannot edit locked salary sheet');
        }

        $currencySymbol = Setting::get('currency_symbol', '৳');

        return view('salaries.edit', compact('salarySheet', 'currencySymbol'));
    }

    public function update(Request $request, SalarySheet $salarySheet)
    {
        if ($salarySheet->status === 'locked') {
            return redirect()->route('salaries.show', $salarySheet)
                ->with('error', 'Cannot edit locked salary sheet');
        }

        $validated = $request->validate([
            'adjustment_amount' => 'required|numeric',
        ]);

        try {
            $this->salaryService->updateAndLock($salarySheet, $validated['adjustment_amount']);

            return redirect()->route('salaries.show', $salarySheet)
                ->with('success', 'Salary sheet locked successfully');
        } catch (\Exception $e) {
            return redirect()->route('salaries.show', $salarySheet)
                ->with('error', $e->getMessage());
        }
    }

    public function regenerate(SalarySheet $salarySheet)
    {
        if ($salarySheet->status === 'locked') {
            return redirect()->route('salaries.show', $salarySheet)
                ->with('error', 'Cannot regenerate locked salary sheet');
        }

        try {
            $this->salaryService->regenerateSalarySheet($salarySheet);

            return redirect()->route('salaries.show', $salarySheet)
                ->with('success', 'Salary sheet regenerated successfully');
        } catch (\Exception $e) {
            return redirect()->route('salaries.show', $salarySheet)
                ->with('error', $e->getMessage());
        }
    }

    public function applyAdvances(SalarySheet $salarySheet)
    {
        if ($salarySheet->status === 'locked') {
            return redirect()->route('salaries.show', $salarySheet)
                ->with('error', 'Cannot modify a locked salary sheet');
        }

        try {
            $updated = $this->salaryService->applyAdvancesOnly($salarySheet);
            $deducted = $updated->advance_deducted;

            return redirect()->route('salaries.show', $salarySheet)
                ->with('success', "Advances applied. Total deducted: {$deducted}");
        } catch (\Exception $e) {
            return redirect()->route('salaries.show', $salarySheet)
                ->with('error', $e->getMessage());
        }
    }

    private function calculateSummary($salaries)
    {
        return [
            'total_salary' => $salaries->sum('net_salary'),
            'total_paid' => $salaries->sum('paid_amount'),
            'total_due' => $salaries->sum('due_amount'),
            'total_deducted' => $salaries->sum('advance_deducted'),
        ];
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

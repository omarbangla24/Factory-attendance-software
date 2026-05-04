<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Advance;
use App\Models\SalarySheet;
use App\Models\SalaryPayment;
use App\Models\AdvanceDeduction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Create 5 test employees
        $employees = [
            [
                'name' => 'Ahmed Hassan',
                'phone' => '01700000001',
                'address' => 'Dhaka, Bangladesh',
                'joining_date' => '2023-01-15',
                'department' => 'IT',
                'hajira_rate' => 500,
                'overtime_rate' => 150,
                'status' => 'active',
            ],
            [
                'name' => 'Fatima Khan',
                'phone' => '01700000002',
                'address' => 'Chittagong, Bangladesh',
                'joining_date' => '2023-02-10',
                'department' => 'HR',
                'hajira_rate' => 450,
                'overtime_rate' => 120,
                'status' => 'active',
            ],
            [
                'name' => 'Mohammad Ali',
                'phone' => '01700000003',
                'address' => 'Sylhet, Bangladesh',
                'joining_date' => '2023-03-20',
                'department' => 'Finance',
                'hajira_rate' => 550,
                'overtime_rate' => 160,
                'status' => 'active',
            ],
            [
                'name' => 'Aisha Ahmed',
                'phone' => '01700000004',
                'address' => 'Khulna, Bangladesh',
                'joining_date' => '2023-04-05',
                'department' => 'Sales',
                'hajira_rate' => 400,
                'overtime_rate' => 100,
                'status' => 'active',
            ],
            [
                'name' => 'Ibrahim Khan',
                'phone' => '01700000005',
                'address' => 'Rajshahi, Bangladesh',
                'joining_date' => '2023-05-12',
                'department' => 'Operations',
                'hajira_rate' => 480,
                'overtime_rate' => 140,
                'status' => 'inactive',
            ],
        ];

        $createdEmployees = [];
        foreach ($employees as $emp) {
            $createdEmployees[] = Employee::create($emp);
        }

        // Create attendance records for current month
        $currentDate = Carbon::now();
        foreach ($createdEmployees as $employee) {
            for ($day = 1; $day <= 25; $day++) {
                $date = Carbon::create($currentDate->year, $currentDate->month, $day);
                
                // Skip weekends
                if ($date->dayOfWeek === Carbon::FRIDAY || $date->dayOfWeek === Carbon::SATURDAY) {
                    continue;
                }

                // Random attendance status
                $random = rand(1, 10);
                $hajiraType = 'one';
                $hajiraValue = 1;
                $overtimeHours = 0;

                if ($random === 1) {
                    $hajiraType = 'absent';
                    $hajiraValue = 0;
                } elseif ($random === 2) {
                    $hajiraType = 'one_half';
                    $hajiraValue = 1.5;
                }

                if ($random <= 8) {
                    $overtimeHours = rand(0, 3);
                }

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->format('Y-m-d'),
                    'hajira_type' => $hajiraType,
                    'hajira_value' => $hajiraValue,
                    'overtime_hours' => $overtimeHours,
                    'note' => $random === 1 ? 'Absent without leave' : null,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }
        }

        // Create advance records
        foreach ($createdEmployees as $employee) {
            if (rand(1, 2) === 1) {
                Advance::create([
                    'employee_id' => $employee->id,
                    'date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                    'amount' => rand(5000, 20000),
                    'reason' => 'Emergency',
                    'note' => 'Medical emergency advance',
                    'created_by' => 1,
                ]);
            }
        }

        // Create salary sheet for current month
        $month = Carbon::now()->format('Y-m');
        foreach ($createdEmployees as $employee) {
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', $currentDate->month)
                ->get();

            $totalHajira = $attendances->sum('hajira_value');
            $totalOvertimeHours = $attendances->sum('overtime_hours');
            $absentDays = $attendances->where('hajira_type', 'absent')->count();
            $basicAmount = $totalHajira * $employee->hajira_rate;
            $overtimeAmount = $totalOvertimeHours * $employee->overtime_rate;
            $advances = Advance::where('employee_id', $employee->id)
                ->whereMonth('date', $currentDate->month)
                ->get();
            $advanceDeducted = $advances->sum('amount');
            $netSalary = $basicAmount + $overtimeAmount - $advanceDeducted;

            $salarySheet = SalarySheet::create([
                'employee_id' => $employee->id,
                'month' => $month,
                'total_hajira' => $totalHajira,
                'total_overtime_hours' => $totalOvertimeHours,
                'absent_days' => $absentDays,
                'basic_amount' => $basicAmount,
                'overtime_amount' => $overtimeAmount,
                'advance_deducted' => $advanceDeducted,
                'adjustment_amount' => 0,
                'net_salary' => $netSalary,
                'paid_amount' => 0,
                'due_amount' => $netSalary,
                'status' => 'draft',
            ]);

            // Link advance deductions
            foreach ($advances as $advance) {
                AdvanceDeduction::create([
                    'advance_id' => $advance->id,
                    'employee_id' => $employee->id,
                    'salary_sheet_id' => $salarySheet->id,
                    'amount' => $advance->amount,
                ]);
            }

            // Create some payment records
            if (rand(1, 2) === 1) {
                $paidAmount = $netSalary * 0.5;
                $salarySheet->update([
                    'paid_amount' => $paidAmount,
                    'due_amount' => $netSalary - $paidAmount,
                    'status' => 'partial',
                ]);

                SalaryPayment::create([
                    'salary_sheet_id' => $salarySheet->id,
                    'employee_id' => $employee->id,
                    'payment_date' => Carbon::now()->format('Y-m-d'),
                    'amount' => $paidAmount,
                    'payment_method' => ['cash', 'bank', 'mobile_banking'][rand(0, 2)],
                    'note' => 'Partial payment',
                    'created_by' => 1,
                ]);
            }
        }
    }
}

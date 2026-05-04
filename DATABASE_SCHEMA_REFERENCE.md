# Database Schema Summary

## Quick Reference

### Table Schema Details

| Table | Purpose | Key Relationships |
|-------|---------|-------------------|
| `employees` | Employee master data | 1→Many: attendances, advances, salary_sheets, payments, deductions |
| `attendances` | Daily attendance records | Many→1: employee; Unique: (employee_id, date) |
| `advances` | Salary advance requests | Many→1: employee; 1→Many: advance_deductions |
| `salary_sheets` | Monthly salary calculation | Many→1: employee; Unique: (employee_id, month); Status: draft→locked→partial→paid |
| `advance_deductions` | Advance-to-salary linking | Many→1: advance, employee, salary_sheet |
| `salary_payments` | Individual payment transactions | Many→1: salary_sheet, employee |

---

## Field Types & Rules

### Attendance Status Values
```
hajira_type     → 'absent', 'one', 'one_half'
hajira_value    → 0, 1, 1.5 (auto-set based on type)
```

### Salary Sheet Status Values
```
'draft'      → Initial state, can edit attendance
'locked'     → Finalized, cannot edit attendance for this month
'partial'    → Some payments received
'paid'       → Fully paid
```

### Payment Methods
```
'cash', 'bank', 'mobile_banking'
```

### Employee Status
```
'active' → Included in payroll processing
'inactive' → Excluded from current operations
```

---

## Salary Calculation Formula

```
Total Hajira = SUM(hajira_value) for all days in month
Total Overtime = SUM(overtime_hours) for all days in month

basic_amount = hajira_rate × total_hajira
overtime_amount = overtime_rate × total_overtime_hours
advance_deducted = SUM(amounts from advance_deductions)
adjustment_amount = Manual adjustments (bonuses, deductions)

net_salary = basic_amount + overtime_amount - advance_deducted + adjustment_amount
paid_amount = SUM(salary_payment amounts) for this sheet
due_amount = net_salary - paid_amount
```

---

## Important Constraints

1. **One attendance per day**: `UNIQUE(employee_id, date)`
2. **One salary sheet per month**: `UNIQUE(employee_id, month)`
3. **Unique phone**: `UNIQUE(phone)` on employees
4. **Cascade delete**: Deleting employee cascades to all related records
5. **Foreign key enforcement**: All references to employees/users are enforced

---

## Indexes (Performance)

```
employees:        status, department
attendances:      date, (employee_id, date) PRIMARY
advances:         employee_id, date
salary_sheets:    status, month, (employee_id, month) PRIMARY
salary_payments:  salary_sheet_id, employee_id, payment_date
```

---

## Migration Files

```
2026_05_03_100300_create_employees_table.php
2026_05_03_100301_create_attendances_table.php
2026_05_03_100302_create_advances_table.php
2026_05_03_100303_create_salary_sheets_table.php
2026_05_03_100304_create_advance_deductions_table.php
2026_05_03_100305_create_salary_payments_table.php
```

---

## Model Classes

```
App\Models\Employee              → Primary model
App\Models\Attendance            → Attendance records
App\Models\Advance               → Advance requests
App\Models\SalarySheet           → Monthly payroll
App\Models\AdvanceDeduction      → Advance-salary link
App\Models\SalaryPayment         → Payment transactions
```

---

## Seeded Test Data

```
Employees:            5 (4 active, 1 inactive)
Attendances:          ~85 (one per working day per employee)
Advances:             ~2 (50% of employees)
Salary Sheets:        5 (one per employee, current month)
Advance Deductions:   ~2 (linked to salary sheets)
Salary Payments:      ~3 (50% have partial payments)
```

---

## Testing Checklist

### Data Integrity
- [ ] All fields of each table validate correctly
- [ ] Unique constraints prevent duplicates
- [ ] Foreign keys prevent orphaned data
- [ ] Null fields are truly optional

### Relationships
- [ ] Employee → all related records
- [ ] SalarySheet ← AdvanceDeduction → Advance
- [ ] SalarySheet ← SalaryPayment

### Calculations
- [ ] Salary formula: basic + overtime - advances + adjustments
- [ ] Due amount: net_salary - paid_amount
- [ ] Absent days count correctly

### Status Workflow
- [ ] New sheets start as 'draft'
- [ ] Can transition to 'locked'
- [ ] Changes to 'partial' on first payment
- [ ] Changes to 'paid' when fully paid

### Constraints
- [ ] Cannot duplicate (employee_id, date) in attendances
- [ ] Cannot duplicate (employee_id, month) in salary_sheets
- [ ] Phone numbers unique across employees
- [ ] Dates are properly formatted

---

## Common Queries

```php
// Get employee with all salary data for month
Employee::with([
    'attendances' => fn($q) => $q->whereMonth('date', 5)->whereYear('date', 2026),
    'salarySheets' => fn($q) => $q->where('month', '2026-05'),
    'salarySheets.advanceDeductions',
    'salarySheets.salaryPayments'
])->first();

// Get unpaid salary sheets
SalarySheet::where('status', '!=', 'paid')->with('employee')->get();

// Get employee's advance history
Advance::where('employee_id', 1)->with('advanceDeductions')->get();

// Get payment details for salary sheet
SalaryPayment::where('salary_sheet_id', 1)->with('employee')->get();
```

---

## Database Export & Backup

```bash
# Backup
mysqldump -u root fms > fms_backup.sql

# Restore
mysql -u root fms < fms_backup.sql

# From Laravel
php artisan db:seed --class=EmployeeSeeder  # Reseed test data
php artisan migrate:fresh --seed            # Full reset
```

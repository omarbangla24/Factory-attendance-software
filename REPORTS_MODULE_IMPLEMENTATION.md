# Reports Module - Implementation Guide

## Overview

The Reports module provides 8 comprehensive reports for analyzing payroll data with both web and API interfaces. All reports support filtering and print-friendly export.

## Reports Available

### 1. Monthly Hajira Report
**Purpose**: Attendance summary by month

**Filters**:
- Month (YYYY-MM)
- Employee (optional)

**Data Shown**:
- Employee name and department
- Total hajira value
- Present days count
- Absent days count

**Totals Calculated**:
- Total hajira = sum of all hajira_value for month
- Total present = count of records where hajira_type != 'absent'
- Total absent = count of records where hajira_type = 'absent'

**Verification Logic**:
```
For each employee in report:
  total_hajira = SUM(attendance.hajira_value) WHERE month matches
  present_days = COUNT(attendance) WHERE hajira_type != 'absent'
  absent_days = COUNT(attendance) WHERE hajira_type = 'absent'

Verify: present_days + absent_days = total attendance records
```

### 2. Overtime Report
**Purpose**: Overtime hours and compensation tracking

**Filters**:
- Date range (from_date, to_date)
- Employee (optional)

**Data Shown**:
- Employee name and department
- Total overtime hours
- Overtime rate
- Overtime amount (hours × rate)

**Totals Calculated**:
- Total hours = sum of all overtime_hours in date range
- Total amount = sum of (overtime_hours × overtime_rate)

**Verification Logic**:
```
For each employee in report:
  total_hours = SUM(attendance.overtime_hours) WHERE date in range
  total_amount = total_hours × employee.overtime_rate

Verify: total_hours × rate = total_amount
```

### 3. Absent Report
**Purpose**: Track absence records with dates

**Filters**:
- Date range (from_date, to_date)
- Employee (optional)

**Data Shown**:
- Employee name and department
- Total absent days
- List of absence dates

**Totals Calculated**:
- Total absence records = count of rows where hajira_type = 'absent'

**Verification Logic**:
```
For each employee in report:
  total_absent_days = COUNT(attendance) WHERE hajira_type = 'absent' AND date in range
  absent_dates = LIST of dates WHERE hajira_type = 'absent'

Verify: count(absent_dates) = total_absent_days
```

### 4. Advance Report
**Purpose**: Employee advance management

**Filters**:
- Date range (from_date, to_date) - optional
- Employee (optional)

**Data Shown**:
- Employee name and department
- Total advance given
- Total advance deducted
- Remaining balance

**Totals Calculated**:
- Total advance = sum of all advance.amount
- Total deducted = sum of all advance_deductions.amount for those advances
- Remaining balance = total_advance - total_deducted

**Verification Logic**:
```
For each employee in report:
  advances = Advance records for employee (filtered by date if provided)
  total_advance = SUM(advances.amount)
  
  deductions = AdvanceDeduction WHERE advance_id IN (advances)
  total_deducted = SUM(deductions.amount)
  
  remaining = total_advance - total_deducted

Verify: remaining >= 0 (never negative)
Verify: total_deducted <= total_advance
```

### 5. Salary Sheet Report
**Purpose**: Complete salary breakdown by month

**Filters**:
- Month (YYYY-MM)
- Employee (optional)

**Data Shown**:
- Employee name and department
- Basic amount
- Overtime amount
- Advance deducted
- Adjustment amount
- Net salary
- Paid amount
- Due amount
- Status (draft/locked/partial/paid)

**Totals Calculated**:
- Total basic = sum of basic_amount
- Total overtime = sum of overtime_amount
- Total advance deducted = sum of advance_deducted
- Total adjustments = sum of adjustment_amount
- Total net salary = sum of net_salary
- Total paid = sum of paid_amount
- Total due = sum of due_amount

**Verification Logic**:
```
For each salary sheet in report:
  Verify: net_salary = basic_amount + overtime_amount + adjustment_amount - advance_deducted
  Verify: due_amount = net_salary - paid_amount
  Verify: status correct based on paid_amount vs net_salary
  
Sum totals:
  total_net = SUM(net_salary)
  total_paid = SUM(paid_amount)
  total_due = SUM(due_amount)
  
Verify: total_paid + total_due = total_net
```

### 6. Payment Report
**Purpose**: Payment tracking with method breakdown

**Filters**:
- Date range (from_date, to_date)
- Employee (optional)

**Data Shown**:
- Employee name
- Payment date
- Amount
- Payment method (cash/bank/mobile_banking)
- Note

**Totals & Summary**:
- Total paid = sum of all amounts
- Payment count = number of payment records
- Payment method summary:
  - Cash: count and total
  - Bank: count and total
  - Mobile Banking: count and total

**Verification Logic**:
```
payments = SalaryPayment records in date range (filtered by employee if provided)
total_paid = SUM(payments.amount)
payment_count = COUNT(payments)

For each payment_method:
  count = COUNT(payments WHERE method = X)
  total = SUM(payments.amount WHERE method = X)
  
Verify: SUM(method_totals) = total_paid
Verify: SUM(method_counts) = payment_count
```

### 7. Employee Ledger
**Purpose**: Complete financial record for single employee

**Filters**:
- Employee (required, dropdown)

**Data Sections**:

**A. Employee Information**:
- Name, department, phone
- Hajira rate, overtime rate
- Status

**B. Attendances**:
- All attendance records (date, hajira_type, value, overtime_hours, note)

**C. Advances**:
- All advance records (date, amount, reason, note)

**D. Salary Sheets**:
- All salary sheets (month, net_salary, paid_amount, due_amount, status)

**E. Payments**:
- All payment records (date, amount, method)

**F. Summary**:
- Total advance given
- Total advance deducted
- Current advance balance
- Total salary generated
- Total paid
- Total due

**Verification Logic**:
```
employee_id = selected employee

attendances = Attendance WHERE employee_id
advances = Advance WHERE employee_id
salary_sheets = SalarySheet WHERE employee_id
payments = SalaryPayment WHERE employee_id

total_advance_given = SUM(advances.amount)
total_advance_deducted = SUM(AdvanceDeduction.amount WHERE advance_id IN advances)
current_balance = total_advance_given - total_advance_deducted

total_paid = SUM(payments.amount)
total_due = SUM(salary_sheets.due_amount)

Verify: current_balance >= 0
Verify: total_paid = SUM of paid_amount from salary_sheets
```

### 8. Accounts Summary
**Purpose**: High-level financial overview for the month

**Filters**:
- Month (YYYY-MM)

**Data Sections**:

**A. Summary Cards**:
- Total salary (net)
- Employee count
- Total paid
- Total due

**B. Salary Components**:
- Basic pay total
- Overtime pay total
- Adjustments total

**C. Advance Management**:
- Total advance given
- Total advance deducted
- Balance (given - deducted)

**D. Payment Status**:
- Total paid
- Total due
- Payment count

**Verification Logic**:
```
month = selected month

salary_sheets = SalarySheet WHERE month
payments = SalaryPayment WHERE salary_sheet_id IN salary_sheets
advances = Advance WHERE date in month range

employee_count = COUNT(DISTINCT salary_sheets.employee_id)
total_salary = SUM(salary_sheets.net_salary)
total_paid = SUM(payments.amount)
total_due = SUM(salary_sheets.due_amount)

total_basic = SUM(salary_sheets.basic_amount)
total_overtime = SUM(salary_sheets.overtime_amount)
total_adjustments = SUM(salary_sheets.adjustment_amount)

total_advance_given = SUM(advances.amount)
total_advance_deducted = SUM(AdvanceDeduction.amount WHERE salary_sheet_id IN salary_sheets)

Verify: total_paid + total_due = total_salary
Verify: total_salary = total_basic + total_overtime + total_adjustments - total_advance_deducted
Verify: total_advance_deducted <= total_advance_given (never over-deduct)
Verify: payment_count = COUNT(payments)
```

## API Endpoints

All API endpoints require authentication via Laravel Sanctum bearer token.

### GET /api/reports/monthly-hajira
Query Parameters:
- `month` (optional): YYYY-MM format
- `employee_id` (optional): Filter by employee

Response:
```json
{
  "data": [
    {
      "employee_id": 1,
      "employee_name": "Ahmed Khan",
      "department": "Sales",
      "total_hajira": 20.0,
      "present_days": 20,
      "absent_days": 0
    }
  ],
  "meta": {
    "month": "2026-05",
    "employee_id": null,
    "record_count": 1,
    "total_hajira": 20.0,
    "total_present": 20,
    "total_absent": 0
  }
}
```

### GET /api/reports/overtime
Query Parameters:
- `from_date` (optional): YYYY-MM-DD format
- `to_date` (optional): YYYY-MM-DD format
- `employee_id` (optional): Filter by employee

Response:
```json
{
  "data": [
    {
      "employee_id": 1,
      "employee_name": "Ahmed Khan",
      "department": "Sales",
      "total_overtime_hours": 5.5,
      "overtime_rate": 100,
      "overtime_amount": 550.00
    }
  ],
  "meta": {
    "from_date": "2026-04-03",
    "to_date": "2026-05-03",
    "employee_id": null,
    "record_count": 1,
    "total_hours": 5.5,
    "total_amount": 550.00
  }
}
```

### GET /api/reports/absent
Query Parameters:
- `from_date` (optional): YYYY-MM-DD format
- `to_date` (optional): YYYY-MM-DD format
- `employee_id` (optional): Filter by employee

### GET /api/reports/advance
Query Parameters:
- `from_date` (optional): YYYY-MM-DD format
- `to_date` (optional): YYYY-MM-DD format
- `employee_id` (optional): Filter by employee

### GET /api/reports/salary-sheet
Query Parameters:
- `month` (optional): YYYY-MM format
- `employee_id` (optional): Filter by employee

### GET /api/reports/payment
Query Parameters:
- `from_date` (optional): YYYY-MM-DD format
- `to_date` (optional): YYYY-MM-DD format
- `employee_id` (optional): Filter by employee

Response includes `method_summary` with cash/bank/mobile_banking breakdowns.

### GET /api/reports/employee-ledger/{employee_id}
Path Parameters:
- `employee_id` (required): Employee ID

Response includes all sections: employee info, attendances, advances, salary sheets, payments, and summary.

### GET /api/reports/accounts-summary
Query Parameters:
- `month` (optional): YYYY-MM format

Response includes summary section with all financial data for the month.

## How to Verify Report Totals

### Manual Verification Steps

1. **Monthly Hajira Report**:
   - Navigate to Reports → Monthly Hajira
   - Select a month with known data
   - Manually count attendance records for each employee
   - Verify total_hajira matches sum of individual values
   - Verify present_days + absent_days = total records per employee

2. **Overtime Report**:
   - Go to Reports → Overtime
   - Select date range with known OT hours
   - Check individual employee overtime calculations
   - Verify: overtime_amount = total_hours × overtime_rate
   - Verify: sum of amounts matches total

3. **Salary Sheet Report**:
   - Go to Reports → Salary Sheets
   - Select a month after salary generation
   - Verify each row: net_salary = basic + overtime + adjustment - advance
   - Check: total_due = sum of (net_salary - paid_amount)
   - Verify: total_paid + total_due = total_net_salary

4. **Advance Report**:
   - Go to Reports → Advances
   - Check advance balance = given - deducted
   - Verify: no negative balances
   - Check: deducted never exceeds given

5. **Payment Report**:
   - Go to Reports → Payments
   - Verify payment method totals
   - Check: sum of method amounts = total_paid
   - Verify: payment count accuracy

### Automated Verification via API

Use curl to verify report calculations:

```bash
# Get salary sheet report for May 2026
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/salary-sheet?month=2026-05"

# Verify:
# 1. total_net_salary = sum of all net_salary values
# 2. total_paid + total_due = total_net_salary
# 3. Each row: net_salary = basic + overtime + adjustment - advance
```

### Database Query Verification

```sql
-- Verify Monthly Hajira totals
SELECT employee_id, SUM(hajira_value) as total_hajira,
       COUNT(CASE WHEN hajira_type != 'absent' THEN 1 END) as present,
       COUNT(CASE WHEN hajira_type = 'absent' THEN 1 END) as absent
FROM attendances
WHERE YEAR_MONTH(date) = '2026-05'
GROUP BY employee_id;

-- Verify Salary Sheet totals
SELECT SUM(net_salary) as total_net,
       SUM(paid_amount) as total_paid,
       SUM(due_amount) as total_due
FROM salary_sheets
WHERE month = '2026-05';

-- Verify Advance balances
SELECT employee_id,
       SUM(amount) as total_given,
       (SELECT SUM(amount) FROM advance_deductions 
        WHERE advance_id IN (SELECT id FROM advances WHERE employee_id = a.employee_id)) as total_deducted
FROM advances a
GROUP BY employee_id;
```

## Features

- ✓ 8 comprehensive report types
- ✓ Mobile-friendly card layout
- ✓ Desktop table layout
- ✓ Print-friendly export
- ✓ Multiple filter options
- ✓ Automatic total calculations
- ✓ RESTful API endpoints
- ✓ Role-based access (authentication required)
- ✓ Real-time data (no caching)
- ✓ Decimal precision (2 decimal places)

## Performance Considerations

- All reports use eager loading (with()) to prevent N+1 queries
- Date range filtering on large datasets may need indexes
- Pagination not implemented for reports (suitable for small-medium datasets)
- Consider adding caching for read-only reports

## Next Steps

- [ ] Add export to Excel/PDF functionality
- [ ] Add scheduled report generation via email
- [ ] Add dashboard widgets showing key report metrics
- [ ] Add drill-down capability (click totals to see details)
- [ ] Add comparative reporting (month-on-month, YoY)
- [ ] Add custom report builder

## Files Created

- `app/Services/ReportService.php` (12,918 chars)
- `app/Http/Controllers/ReportController.php` (6,793 chars)
- `app/Http/Controllers/Api/ReportController.php` (6,068 chars)
- `resources/views/reports/index.blade.php` (4,456 chars)
- `resources/views/reports/monthly-hajira.blade.php` (6,620 chars)
- `resources/views/reports/overtime.blade.php` (2,847 chars)
- `resources/views/reports/absent.blade.php` (2,106 chars)
- `resources/views/reports/advance.blade.php` (2,968 chars)
- `resources/views/reports/salary-sheet.blade.php` (2,914 chars)
- `resources/views/reports/payment.blade.php` (3,417 chars)
- `resources/views/reports/employee-ledger.blade.php` (4,631 chars)
- `resources/views/reports/accounts-summary.blade.php` (4,183 chars)

**Total**: 59,321 characters + routes + documentation

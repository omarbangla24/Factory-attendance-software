# Reports Module - Manual Testing Checklist

## Pre-Testing Setup

- [ ] User logged in as admin (a@a.com)
- [ ] Test data exists:
  - [ ] At least 2-3 employees
  - [ ] Attendance records for past month
  - [ ] Some advance records
  - [ ] Generated salary sheets
  - [ ] Salary payment records
- [ ] Database backup taken (if needed)

## Web UI Tests

### Navigation
- [ ] "Reports" link visible in main navigation
- [ ] "Reports" link visible in responsive mobile menu
- [ ] Reports menu item active when on reports pages
- [ ] Can navigate from any page to Reports dashboard

### Reports Index Page (/reports)
- [ ] Page loads without errors
- [ ] All 8 report cards visible:
  - [ ] Monthly Hajira Report
  - [ ] Overtime Report
  - [ ] Absent Report
  - [ ] Advance Report
  - [ ] Salary Sheet Report
  - [ ] Payment Report
  - [ ] Employee Ledger
  - [ ] Accounts Summary
- [ ] Each card shows icon, title, description
- [ ] Each card has clickable link to report
- [ ] Page is responsive on mobile (stacked cards)
- [ ] Page is responsive on desktop (grid layout)

### Monthly Hajira Report (/reports/monthly-hajira)
- [ ] Page loads without errors
- [ ] Filter form visible:
  - [ ] Month dropdown populated with available months
  - [ ] Employee dropdown populated with all employees
  - [ ] "Search" button functional
  - [ ] "Clear Filters" button functional
- [ ] Default data displayed (current month or last populated month)
- [ ] Summary cards show:
  - [ ] Total Hajira (numeric value)
  - [ ] Total Present (count)
  - [ ] Total Absent (count)
- [ ] Mobile view (on mobile device):
  - [ ] Employee cards visible (not table)
  - [ ] Card shows: name, department, hajira, present, absent
  - [ ] Layout is scrollable and readable
- [ ] Desktop view (on desktop):
  - [ ] Table visible with columns: Employee, Department, Total Hajira, Present, Absent
  - [ ] Data properly aligned
  - [ ] Summary row at bottom with totals
- [ ] Filtering works:
  - [ ] Filter by month shows only that month's data
  - [ ] Filter by employee shows only that employee
  - [ ] Clear filters resets to default
- [ ] Print button:
  - [ ] Page prints without UI elements
  - [ ] Print shows table format
  - [ ] Print includes summary
  - [ ] Print is readable

### Overtime Report (/reports/overtime)
- [ ] Page loads without errors
- [ ] Filter form visible:
  - [ ] From date picker functional
  - [ ] To date picker functional
  - [ ] Default date range set (past month)
  - [ ] Employee dropdown functional
- [ ] Summary cards show:
  - [ ] Total Hours
  - [ ] Total Amount (calculated)
- [ ] Data displays overtime hours and amounts
- [ ] Verify: overtime_amount = total_hours × overtime_rate
- [ ] Mobile and desktop layouts functional
- [ ] Print works correctly

### Absent Report (/reports/absent)
- [ ] Page loads without errors
- [ ] Filter form visible
- [ ] Absence dates displayed in list format
- [ ] Summary shows total absence count
- [ ] Filtering by date range works
- [ ] Filtering by employee works
- [ ] Print shows all absence records

### Advance Report (/reports/advance)
- [ ] Page loads without errors
- [ ] Summary cards show:
  - [ ] Total Advance Given
  - [ ] Total Advance Deducted
  - [ ] Remaining Balance
- [ ] Verify: Remaining = Given - Deducted
- [ ] Verify: No negative balances
- [ ] Filtering works
- [ ] Mobile and desktop layouts functional

### Salary Sheet Report (/reports/salary-sheet)
- [ ] Page loads without errors
- [ ] Month and employee filters work
- [ ] Summary cards show:
  - [ ] Total Basic
  - [ ] Total Overtime
  - [ ] Total Advance Deducted
  - [ ] Total Net Salary
  - [ ] Total Paid
  - [ ] Total Due
- [ ] Verify calculations:
  - [ ] net_salary = basic + overtime + adjustment - advance
  - [ ] due_amount = net_salary - paid_amount
- [ ] Table shows all salary components
- [ ] Status column displays correctly (draft/locked/partial/paid)
- [ ] Print functionality works

### Payment Report (/reports/payment)
- [ ] Page loads without errors
- [ ] Date range filters work
- [ ] Employee filter works
- [ ] Payment method summary shows:
  - [ ] Cash count and total
  - [ ] Bank count and total
  - [ ] Mobile Banking count and total
- [ ] Verify: Sum of method totals = Total Paid
- [ ] Payment list shows all fields: date, amount, method, note
- [ ] Mobile and desktop views functional

### Employee Ledger (/reports/employee-ledger)
- [ ] Page loads without errors
- [ ] Employee dropdown shows all employees
- [ ] Ledger displays all sections:
  - [ ] Employee information (name, dept, phone, rates)
  - [ ] Attendance history (all records)
  - [ ] Advance history (all records)
  - [ ] Salary sheets (all months)
  - [ ] Payment history (all transactions)
- [ ] Summary section shows:
  - [ ] Total advance balance
  - [ ] Total due salary
- [ ] Switching employee updates all sections
- [ ] Print shows complete ledger

### Accounts Summary (/reports/accounts-summary)
- [ ] Page loads without errors
- [ ] Month filter functional
- [ ] Summary cards display:
  - [ ] Total Salary
  - [ ] Employee Count
  - [ ] Total Paid
  - [ ] Total Due
- [ ] Salary components section shows:
  - [ ] Total Basic
  - [ ] Total Overtime
  - [ ] Total Adjustments
- [ ] Advance section shows:
  - [ ] Total Given
  - [ ] Total Deducted
  - [ ] Balance
- [ ] Verify: Total Paid + Total Due = Total Salary
- [ ] Payment status shows summary by method
- [ ] Print includes all sections

## API Tests

### Setup
- [ ] Postman or curl available
- [ ] Bearer token obtained: `php artisan tinker` → `User::first()->createToken('test')->plainTextToken`
- [ ] API tests use Authorization header: `Authorization: Bearer TOKEN`

### GET /api/reports/monthly-hajira
- [ ] Returns 200 status
- [ ] Data array present
- [ ] Meta object includes: month, employee_id, record_count, totals
- [ ] Without filters: returns all months
- [ ] With ?month=2026-05: filters by month
- [ ] With ?employee_id=1: filters by employee
- [ ] Data includes: employee_id, employee_name, department, total_hajira, present_days, absent_days

### GET /api/reports/overtime
- [ ] Returns 200 status
- [ ] Meta includes: from_date, to_date, record_count, total_hours, total_amount
- [ ] Date range filters work
- [ ] Employee filter works
- [ ] Data includes overtime_rate and calculated amount

### GET /api/reports/absent
- [ ] Returns 200 status
- [ ] Data includes absent dates list
- [ ] Filtering works

### GET /api/reports/advance
- [ ] Returns 200 status
- [ ] Meta shows total_advance, total_deducted, remaining_balance
- [ ] Calculations correct

### GET /api/reports/salary-sheet
- [ ] Returns 200 status
- [ ] Data includes all salary fields
- [ ] Meta includes salary component totals and summary
- [ ] Filtering by month works
- [ ] Filtering by employee works

### GET /api/reports/payment
- [ ] Returns 200 status
- [ ] Meta includes method_summary with cash/bank/mobile_banking
- [ ] Payment list includes all fields
- [ ] Date range filtering works

### GET /api/reports/employee-ledger/{employee_id}
- [ ] Returns 200 status with valid employee_id
- [ ] Returns error with invalid employee_id
- [ ] Data includes:
  - [ ] Employee information
  - [ ] Attendances array
  - [ ] Advances array
  - [ ] Salary sheets array
  - [ ] Payments array
  - [ ] Summary object
- [ ] Summary includes all balance calculations

### GET /api/reports/accounts-summary
- [ ] Returns 200 status
- [ ] Meta includes month and record_count
- [ ] Data includes:
  - [ ] total_salary
  - [ ] total_paid
  - [ ] total_due
  - [ ] salary_components (basic, overtime, adjustments)
  - [ ] advance_summary (given, deducted, balance)
  - [ ] payment_summary with method breakdown

### Error Handling
- [ ] Invalid month format returns error
- [ ] Invalid employee_id returns error
- [ ] Invalid date range returns error
- [ ] Unauthorized request (no token) returns 401
- [ ] Non-existent report returns 404

## Data Verification Tests

### Monthly Hajira Accuracy
- [ ] Manually verify: total_hajira = SUM(attendance.hajira_value) for month
- [ ] Verify: present_days count matches database
- [ ] Verify: absent_days count matches database

### Overtime Calculation Accuracy
- [ ] Pick an employee with overtime
- [ ] Verify: API returns correct hours
- [ ] Verify: amount = hours × employee.overtime_rate
- [ ] Verify: total matches sum of individual records

### Salary Sheet Accuracy
- [ ] Pick a locked salary sheet
- [ ] Verify: net_salary = basic + overtime + adjustment - advance
- [ ] Verify: due_amount = net_salary - paid_amount
- [ ] Verify: status matches paid_amount/net_salary

### Advance Balance Accuracy
- [ ] Sum all advances for employee: X
- [ ] Sum all advance_deductions for that employee: Y
- [ ] Verify API reports balance = X - Y
- [ ] Verify balance never negative

### Accounts Summary Accuracy
- [ ] Generate salary for a specific month
- [ ] Go to Accounts Summary with that month
- [ ] Verify: total_salary = sum of salary_sheets.net_salary for month
- [ ] Verify: total_paid = sum of payments for that month
- [ ] Verify: total_due = sum of salary_sheets.due_amount
- [ ] Verify: total_paid + total_due = total_salary

## Performance Tests

- [ ] Report loads within 2 seconds (small dataset)
- [ ] API endpoint responds within 1 second
- [ ] Print page renders without freezing
- [ ] No console errors in browser developer tools
- [ ] No database query errors in Laravel logs

## Responsive Design Tests

### Mobile (iPhone 12, 390px)
- [ ] All pages readable on small screen
- [ ] Forms stack vertically
- [ ] Tables convert to cards
- [ ] Buttons are tap-friendly (44px+ size)
- [ ] No horizontal scrolling needed
- [ ] Summary cards stack properly

### Tablet (iPad, 768px)
- [ ] Layout adapts to medium screen
- [ ] Tables show with horizontal scroll if needed
- [ ] Cards display in 2-column grid

### Desktop (1920px)
- [ ] Full table layout visible
- [ ] Multiple columns display properly
- [ ] Filters and data side-by-side (if designed)

## Edge Cases

- [ ] No data for selected month:
  - [ ] Report shows "No data" message
  - [ ] Summary shows zero values
  - [ ] No errors in console
- [ ] Single employee selected:
  - [ ] Report shows only that employee
  - [ ] Totals calculated correctly
  - [ ] API response includes employee_id filter
- [ ] Date range with no records:
  - [ ] Empty result set handled gracefully
  - [ ] No error messages
- [ ] Employee with zero advance balance:
  - [ ] Shows 0.00 (not null)
  - [ ] Remaining balance = 0
- [ ] Employee with no payments:
  - [ ] Shows 0 in payment count
  - [ ] Due amount = net salary
- [ ] Very large amounts (999,999.99):
  - [ ] Displays correctly
  - [ ] No number formatting issues
  - [ ] Calculations still accurate

## Regression Tests

- [ ] Other modules still work:
  - [ ] Dashboard loads
  - [ ] Employee CRUD works
  - [ ] Attendance entry works
  - [ ] Salary generation works
  - [ ] Payment entry works
- [ ] No new database errors
- [ ] No performance degradation
- [ ] All existing routes still work

## Completion Checklist

- [ ] All 8 web reports tested
- [ ] All 8 API endpoints tested
- [ ] Filters working on all reports
- [ ] Print functionality verified
- [ ] Mobile responsiveness confirmed
- [ ] Data accuracy verified against database
- [ ] Error handling tested
- [ ] No regression in other modules
- [ ] API documentation accurate
- [ ] Ready for production

## Notes

- Run `php artisan route:list` to verify all routes registered
- Check `storage/logs/laravel.log` for any errors during testing
- Use `php artisan tinker` to inspect data if results seem incorrect
- For bulk testing, create test data with factories (if needed)

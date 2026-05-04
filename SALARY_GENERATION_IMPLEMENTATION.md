# Monthly Salary Generation Module - Implementation Guide

## Overview
The Monthly Salary Generation module is responsible for creating salary sheets, calculating employee compensation, managing advance deductions, and implementing draft/lock workflows.

## Architecture

### Service Layer
`app/Services/SalaryGenerationService.php` - Core business logic for salary calculations

**Key Methods:**
1. `generateSalaries($month, $employeeIds = null)` - Generate or regenerate salary sheets for active employees
2. `createSalarySheet($employee, $month, $monthStart, $monthEnd)` - Create new salary sheet with calculations
3. `regenerateSalarySheet($salarySheet)` - Recalculate draft salary sheet with latest attendance data
4. `updateAndLock($salarySheet, $adjustmentAmount)` - Apply adjustment and lock for finalization
5. `processAdvanceDeductions($employee, $salarySheet)` - Handle advance deductions (oldest first)

### Controllers

#### Web Controller: `app/Http/Controllers/SalaryController.php`
- **index()** - List salary sheets with month/status filters
- **create()** - Show salary generation form
- **store()** - Generate salary sheets (POST)
- **show()** - Display salary details with breakdown
- **edit()** - Show lock & finalize form (adjustment setup)
- **update()** - Apply adjustment and lock salary (PUT)
- **regenerate()** - Recalculate draft salary sheet (PATCH)

#### API Controller: `app/Http/Controllers/Api/SalaryController.php`
- **index()** - List salaries (JSON)
- **store()** - Generate salaries (JSON)
- **show()** - Get salary details (JSON)
- **update()** - Lock and apply adjustment (JSON)
- **lock()** - Lock without adjustment (JSON)
- **regenerate()** - Regenerate draft salary (JSON)

### Models

#### SalarySheet Model
```php
protected $fillable = [
    'employee_id',
    'month',              // Format: YYYY-MM
    'total_hajira',       // Sum of hajira_value for month
    'total_overtime_hours',
    'absent_days',
    'basic_amount',       // total_hajira × employee.hajira_rate
    'overtime_amount',    // total_overtime_hours × employee.overtime_rate
    'advance_deducted',   // Auto-deducted from oldest advances
    'adjustment_amount',  // Manual adjustment (positive/negative)
    'net_salary',         // Basic + Overtime + Adjustment - Deducted
    'paid_amount',        // Amount already paid
    'due_amount',         // Remaining to pay (net_salary - paid_amount)
    'status',             // draft, locked, partial, paid
    'locked_at',          // Timestamp when locked
];
```

## Calculation Rules

### Salary Calculation Flow
```
Step 1: Aggregate Attendance for Month
  - total_hajira = SUM(attendance.hajira_value WHERE month = selected_month)
  - total_overtime_hours = SUM(attendance.overtime_hours WHERE month = selected_month)
  - absent_days = COUNT(attendance WHERE hajira_type = 'absent' AND month = selected_month)

Step 2: Calculate Base Amounts
  - basic_amount = total_hajira × employee.hajira_rate
  - overtime_amount = total_overtime_hours × employee.overtime_rate

Step 3: Calculate Salary Payable (before deductions)
  - salary_payable = basic_amount + overtime_amount + adjustment_amount

Step 4: Apply Advance Deductions (from oldest first)
  - For each advance (ordered by date):
    * Calculate remaining balance = advance.amount - total_deducted_so_far
    * Deduct minimum of (remaining_balance, salary_payable - already_deducted)
    * Create AdvanceDeduction record
    * Stop if salary_payable fully covered

Step 5: Calculate Net Salary
  - net_salary = salary_payable - total_deducted
  - due_amount = net_salary (initially)
  - paid_amount = 0 (initially)
```

### Example Calculation
```
Employee: Ahmed Hassan
Hajira Rate: 500, Overtime Rate: 200
Attendance for May 2026:
  - Total Hajira: 17.5 days
  - Total Overtime: 21.75 hours
  - Absent Days: 0

Calculation:
  Basic = 17.5 × 500 = 8,750
  Overtime = 21.75 × 200 = 4,350
  Subtotal = 8,750 + 4,350 = 13,100
  Adjustment = 0
  Salary Payable = 13,100
  
  Advances to deduct (oldest first):
    - Advance #1 (date: 2026-01-15): Amount 5,000, Already deducted: 0, Remaining: 5,000
      → Deduct min(5,000, 13,100) = 5,000 ✓
    - Advance #2 (date: 2026-02-20): Amount 3,000, Already deducted: 0, Remaining: 3,000
      → Deduct min(3,000, 8,100) = 3,000 ✓
  
  Total Deducted = 8,000
  Net Salary = 13,100 - 8,000 = 5,100
  Status = draft
```

## Status Workflow

### Draft (Initial State)
- Salary sheet just created
- **Can:** Edit adjustment amount, regenerate, lock
- **Cannot:** Modify after locking
- **Use for:** Reviewing calculations before finalization

### Locked (Final State)
- Applied adjustment and locked for payment
- **Can:** View, record payments
- **Cannot:** Edit, regenerate, delete
- **Use for:** Once approved and ready for payment processing

### Partial (Payment State)
- Some payments have been recorded
- Used when paid_amount > 0 but < due_amount

### Paid (Completion State)
- Full salary paid (paid_amount = due_amount)

## API Endpoints

### List Salary Sheets
```
GET /api/salaries?month=2026-05&status=draft&employee_id=1
```

**Query Parameters:**
- `month` (required) - Format: YYYY-MM
- `status` (optional) - draft, locked, partial, paid
- `employee_id` (optional) - Filter by employee

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_id": 1,
      "month": "2026-05",
      "total_hajira": "17.50",
      "total_overtime_hours": "21.75",
      "absent_days": 0,
      "basic_amount": "8750.00",
      "overtime_amount": "4350.00",
      "adjustment_amount": "0.00",
      "advance_deducted": "8000.00",
      "net_salary": "5100.00",
      "paid_amount": "0.00",
      "due_amount": "5100.00",
      "status": "draft",
      "locked_at": null,
      "employee": {
        "id": 1,
        "name": "Ahmed Hassan",
        "department": "Sales"
      },
      "deductions": [
        {
          "id": 1,
          "amount": "5000.00",
          "advance": { "id": 1, "amount": "5000.00", "reason": "Loan" }
        }
      ]
    }
  ],
  "meta": {
    "month": "2026-05",
    "total": 1
  }
}
```

### Generate Salary Sheets
```
POST /api/salaries
Content-Type: application/json

{
  "month": "2026-05",
  "employee_ids": [1, 2, 3]  // Optional - if omitted, all active employees
}
```

**Response:**
```json
{
  "success": true,
  "message": "3 salary sheets generated/updated",
  "data": [...]  // Array of created SalarySheet objects
}
```

### Get Salary Details
```
GET /api/salaries/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "employee_id": 1,
    "month": "2026-05",
    ...full salary sheet data,
    "deductions": [
      {
        "id": 1,
        "amount": "5000.00",
        "advance": {
          "id": 1,
          "date": "2026-01-15",
          "amount": "5000.00",
          "reason": "Personal Loan"
        }
      }
    ]
  }
}
```

### Lock Salary Sheet
```
PUT /api/salaries/{id}
Content-Type: application/json

{
  "adjustment_amount": 500  // Optional adjustment before locking
}
```

**Response:**
```json
{
  "success": true,
  "message": "Salary sheet locked successfully",
  "data": {
    "id": 1,
    "status": "locked",
    "locked_at": "2026-05-03 15:44:44",
    "net_salary": "5600.00",
    ...
  }
}
```

### Lock Without Adjustment
```
POST /api/salaries/{id}/lock
```

### Regenerate Draft Salary
```
POST /api/salaries/{id}/regenerate
```

**Response:**
```json
{
  "success": true,
  "message": "Salary sheet regenerated successfully",
  "data": {
    ...updated salary sheet with latest attendance data
  }
}
```

## Web UI Features

### Salary List (Dashboard)
- **Month Selector** - View salaries for any month
- **Status Filter** - Draft, Locked, Partial, Paid
- **Summary Cards** - Total salary, paid, due, deducted
- **Mobile Cards** - Card view on mobile (<768px)
- **Desktop Table** - Table view on desktop (≥768px)
- **Pagination** - 15 items per page

### Generate Salary
- **Month Picker** - Select month (default: current month)
- **Employee Selection** - All active or specific employees
- **Info Box** - Explains behavior for existing sheets

### Salary Details View
- **Employee Info** - Name, department, rates
- **Attendance Summary** - Total hajira, absent days, overtime
- **Salary Breakdown** - Calculation details with formulas
- **Advance Deductions** - List of deductions applied
- **Payment Summary** - Net, paid, due amounts
- **Status Badge** - Color-coded status indicator
- **Actions**:
  - Lock & Finalize (for draft)
  - Regenerate (for draft)
  - Back to list

### Lock & Finalize Form
- **Current Breakdown** - Shows current calculations
- **Adjustment Input** - Optional positive/negative adjustment
- **Final Net Display** - Real-time preview with JavaScript
- **Deductions Summary** - Shows advance deductions
- **Warning** - Cannot edit after locking
- **Actions**: Lock & Finalize / Cancel

## Advance Deduction Logic

### How Advance Deductions Work
1. **Retrieval** - Get employee's advances ordered by `date` (oldest first)
2. **Balance Calculation** - For each advance:
   - Get already_deducted = SUM(AdvanceDeduction.amount WHERE advance_id = X)
   - remaining_balance = advance.amount - already_deducted
3. **Deduction Process** - For each advance with remaining balance:
   - Calculate: deduction_amount = min(remaining_balance, salary_payable - total_deducted)
   - If > 0: Create AdvanceDeduction record
   - Add to total_deducted
   - Stop if total_deducted >= salary_payable
4. **Limitation** - Never deduct more than payable salary
   - If advance balance = 10,000 but net_salary = 5,000, only 5,000 is deducted

### Preventing Over-Deduction
```php
// In SalaryGenerationService::processAdvanceDeductions()

// Calculate how much we can still deduct
$deductionAmount = min($remainingBalance, $salaryPayable - $totalDeducted);

if ($deductionAmount > 0) {
    // Create deduction record
    AdvanceDeduction::create([...]);
    $totalDeducted += $deductionAmount;
}

// Stop if salary fully covered
if ($totalDeducted >= $salaryPayable) {
    break;
}
```

## Regeneration Behavior

### When Can You Regenerate?
- **Draft Status Only** - Cannot regenerate locked sheets
- **Preserves Adjustment** - Keeps adjustment_amount as-is
- **Recalculates Attendance** - Gets latest attendance data
- **Recalculates Deductions** - Reprocesses advances from scratch

### Regeneration Steps
1. Check status is 'draft' (else throw error)
2. Delete existing AdvanceDeduction records for this salary sheet
3. Re-fetch attendance data for the month
4. Recalculate: total_hajira, overtime_hours, absent_days
5. Recalculate: basic_amount, overtime_amount
6. Keep: adjustment_amount
7. Recalculate: net_salary
8. Reprocess: advance deductions
9. Return updated salary sheet (still draft status)

### Why Regenerate?
- **New Attendance Data** - If attendance was updated after salary sheet created
- **Verify Calculations** - Ensure latest data is reflected
- **Correction** - Fix errors in attendance without recreating

## Protected Operations

### Cannot Edit Locked Salaries
- Web: Redirect with error message (403 Forbidden)
- API: Return 403 with message "Cannot edit locked salary sheet"
- Lock status checked before: edit, update, regenerate

### Draft Sheets During Generate
If salary sheet exists for employee + month:
- **If Draft** - Regenerate with latest data
- **If Locked** - Skip (don't modify)
- **Result** - Some sheets regenerated, some skipped

## Data Integrity

### Unique Constraints
- `UNIQUE(employee_id, month)` - One salary per employee per month
- Enforced at database level to prevent duplicates

### Cascade Delete
- Deleting Employee cascades to:
  - Attendances
  - Salary Sheets
  - Advance Deductions (via salary sheets)
  - Advances

### Foreign Keys
- SalarySheet → Employee (required)
- AdvanceDeduction → Advance (required)
- AdvanceDeduction → SalarySheet (required)
- AdvanceDeduction → Employee (for reference)

## Testing Checklist

### Salary Generation
- [ ] Generate for all active employees
- [ ] Generate for selected employees
- [ ] Generate for specific month with attendance
- [ ] Verify calculations: basic = hajira × rate
- [ ] Verify calculations: overtime = hours × rate
- [ ] Verify net = basic + overtime - deducted
- [ ] Verify draft status on creation
- [ ] Regenerate existing draft recalculates with latest data
- [ ] Cannot regenerate locked salary
- [ ] Cannot generate for inactive employees

### Advance Deductions
- [ ] Deduction applied from oldest advance first
- [ ] Deduction never exceeds remaining advance balance
- [ ] Deduction never exceeds net salary
- [ ] Multiple deductions for same salary sheet
- [ ] Deduction records created correctly
- [ ] Remaining advances not deducted when salary fully covered
- [ ] Employee with no advances shows 0 deduction

### Lock & Finalize
- [ ] Can only lock draft salaries
- [ ] Adjustment amount applied to net salary
- [ ] Positive adjustment increases net salary
- [ ] Negative adjustment decreases net salary
- [ ] Status changes to 'locked' after lock
- [ ] locked_at timestamp is set
- [ ] Cannot edit after locking
- [ ] Cannot regenerate after locking

### API Endpoints
- [ ] GET /api/salaries returns list
- [ ] GET /api/salaries?month=X filters by month
- [ ] GET /api/salaries?status=draft filters by status
- [ ] GET /api/salaries/{id} returns details
- [ ] POST /api/salaries generates salaries
- [ ] PUT /api/salaries/{id} locks with adjustment
- [ ] POST /api/salaries/{id}/lock locks without adjustment
- [ ] POST /api/salaries/{id}/regenerate regenerates draft
- [ ] 403 error when trying to lock already-locked
- [ ] 403 error when trying to regenerate locked

### Web UI
- [ ] Salary list shows current month by default
- [ ] Month picker changes data displayed
- [ ] Status filter works correctly
- [ ] Summary cards show totals correctly
- [ ] Mobile view (cards) works on phones
- [ ] Desktop view (table) works on larger screens
- [ ] Pagination works with 15 items per page
- [ ] Show form generates salaries for selected
- [ ] Lock form shows adjustment input
- [ ] Real-time preview updates with adjustment input
- [ ] Back button returns to list
- [ ] Regenerate button only shows for draft
- [ ] Lock button only shows for draft

### Edge Cases
- [ ] Employee with no attendance = 0 salary
- [ ] Advance larger than month salary = full deduction
- [ ] Multiple advances on same date processed correctly
- [ ] Regenerate with deleted attendance updates calculations
- [ ] Regenerate maintains adjustment amount
- [ ] Adjustment can be negative (deduction)
- [ ] Very large numbers (decimal precision) handled correctly
- [ ] Zero overtime hours = no overtime amount
- [ ] Zero absent days = full attendance

## Common Issues & Solutions

### Issue: Salary showing 0 for month
**Cause:** No attendance records for that month
**Solution:** Add attendance records for the month first

### Issue: Cannot regenerate salary
**Cause:** Salary is locked
**Solution:** Only draft salaries can be regenerated

### Issue: Advance not fully deducted
**Cause:** Salary payable is less than advance amount
**Solution:** This is correct - deduction is capped at payable salary

### Issue: Deduction exceeds net salary
**This should never happen** - the logic prevents over-deduction

### Issue: Status not updating on web
**Cause:** Page not refreshed
**Solution:** Refresh page after action

## Files Modified/Created

### New Files
- `app/Services/SalaryGenerationService.php` - Salary calculation service
- `app/Http/Controllers/SalaryController.php` - Web controller
- `app/Http/Controllers/Api/SalaryController.php` - API controller
- `resources/views/salaries/index.blade.php` - List view
- `resources/views/salaries/create.blade.php` - Generate form
- `resources/views/salaries/show.blade.php` - Details view
- `resources/views/salaries/edit.blade.php` - Lock form

### Modified Files
- `routes/web.php` - Added salary routes
- `routes/api.php` - Added API salary routes
- `resources/views/layouts/navigation.blade.php` - Added Salaries link
- `app/Models/SalarySheet.php` - Added deductions() alias

## Performance Considerations

### Query Optimization
- Salary list paginates (15 per page)
- Eager loading: with('employee', 'deductions')
- Index on: employee_id, month, status
- Foreign key constraints indexed

### Calculations
- Done in PHP (not SQL) for flexibility
- No N+1 queries due to eager loading
- Decimal precision maintained (DECIMAL(10,2))

## Future Enhancements

1. **Payment Recording** - Record partial/full payments
2. **Salary History** - Archive old salaries
3. **Batch Operations** - Lock multiple salaries at once
4. **Export** - PDF/Excel salary slips
5. **Notifications** - Email when salary is locked
6. **Audit Trail** - Track who locked when and what adjustment
7. **Salary Comparison** - Previous month comparison
8. **Deduction Reports** - Advance deduction analytics

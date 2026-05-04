# Monthly Salary Generation - Manual Test Checklist

## Test Environment Setup
- [ ] Database seeded with test data
- [ ] Attendance data exists for May 2026 (17+ records)
- [ ] 5 active employees configured
- [ ] 2 advances created for testing
- [ ] Employee rates: Hajira 500, Overtime 200

---

## Web UI Tests

### 1. Salary List Page
**Route:** GET /salaries

- [ ] Page loads without errors
- [ ] Displays current month by default
- [ ] Month picker is functional
- [ ] Status filter shows all options: draft, locked, partial, paid
- [ ] Summary cards display: Total Salary, Total Paid, Total Due, Total Deducted
- [ ] Mobile view shows cards (< 768px width)
- [ ] Desktop view shows table (> 768px width)
- [ ] Pagination shows 15 items per page
- [ ] Table columns: Employee, Basic, Overtime, Adjustment, Deducted, Net Salary, Status, Actions
- [ ] Status badges are color-coded:
  - [ ] Draft = yellow
  - [ ] Locked = green
  - [ ] Partial = blue
  - [ ] Paid = purple
- [ ] "Generate Salary" button is visible
- [ ] Filtering by month changes displayed data
- [ ] Filtering by status changes displayed data
- [ ] Clear button resets filters

### 2. Generate Salary Page
**Route:** GET /salaries/create

- [ ] Page loads with form
- [ ] Month picker defaults to current month
- [ ] "All Active Employees" option is default selected
- [ ] "Selected Employees" option can be selected
- [ ] Employee list appears when "Selected" is chosen
- [ ] Employee list shows: name and department
- [ ] Each employee has a checkbox
- [ ] Can select multiple employees
- [ ] Generate button submits form
- [ ] Cancel button returns to list

**Test Scenario A - Generate for All Employees:**
- [ ] Click "Generate" with all employees selected
- [ ] Redirects to salary list
- [ ] Success message shows: "4 salary sheets generated/updated"
- [ ] New salary sheets appear in list with status "draft"
- [ ] Each salary has non-zero net_salary

**Test Scenario B - Generate for Selected Employees:**
- [ ] Uncheck some employees
- [ ] Select only 2 employees
- [ ] Click "Generate"
- [ ] Only 2 new salary sheets created
- [ ] Success message shows: "2 salary sheets generated/updated"

**Test Scenario C - Regenerate Existing Draft:**
- [ ] Generate salary for May 2026
- [ ] Verify calculations
- [ ] Generate again for same month
- [ ] Draft salaries are regenerated (not duplicated)
- [ ] No error about duplicate entry

### 3. Salary Details Page
**Route:** GET /salaries/{id}

- [ ] Page loads with salary details
- [ ] Shows employee information: name, department, rates
- [ ] Shows attendance summary:
  - [ ] Total Hajira displayed
  - [ ] Absent Days displayed
  - [ ] Overtime Hours displayed
  - [ ] Month displayed
- [ ] Salary calculation shown:
  - [ ] Basic = Total Hajira × Rate
  - [ ] Overtime = Hours × Rate
  - [ ] Adjustment amount shown
  - [ ] Advance deducted shown
  - [ ] Net Salary = Basic + Overtime + Adjustment - Deducted
- [ ] Advance deductions table shown (if any):
  - [ ] Date column
  - [ ] Reason column
  - [ ] Advance amount column
  - [ ] Deducted amount column
- [ ] Status badge shown and color-coded
- [ ] Back link returns to list
- [ ] For draft status:
  - [ ] "Lock & Finalize" button visible
  - [ ] "Regenerate" button visible
- [ ] For locked status:
  - [ ] "Lock & Finalize" button NOT visible
  - [ ] "Regenerate" button NOT visible
  - [ ] Locked timestamp displayed

### 4. Lock & Finalize Page
**Route:** GET /salaries/{id}/edit (for draft only)

- [ ] Page loads with salary breakdown
- [ ] Shows current calculations
- [ ] Adjustment input field is empty or shows current value
- [ ] Adjustment field accepts decimal numbers
- [ ] Positive adjustment increases final net
- [ ] Negative adjustment decreases final net
- [ ] Final net salary preview updates in real-time
- [ ] JavaScript calculator works:
  - [ ] Formula shown in preview
  - [ ] Real-time calculation works
- [ ] Advance deductions summary shown
- [ ] Warning message about locking is visible
- [ ] "Lock & Finalize" button submits form
- [ ] "Cancel" button returns to view page

**Test Scenario D - Lock with Positive Adjustment:**
- [ ] Enter adjustment = 500
- [ ] Final net = basic + overtime + 500 - deducted
- [ ] Click "Lock & Finalize"
- [ ] Redirects to salary view
- [ ] Success message: "Salary sheet locked successfully"
- [ ] Status changed to "locked"
- [ ] Adjustment amount shows 500
- [ ] Net salary updated
- [ ] locked_at timestamp is set

**Test Scenario E - Lock with Negative Adjustment:**
- [ ] Enter adjustment = -300
- [ ] Final net = basic + overtime - 300 - deducted
- [ ] Click "Lock & Finalize"
- [ ] Net salary decreased correctly

**Test Scenario F - Lock without Adjustment:**
- [ ] Leave adjustment as 0
- [ ] Click "Lock & Finalize"
- [ ] Salary locked with original calculation

**Test Scenario G - Cannot Edit After Lock:**
- [ ] Try to access /salaries/{id}/edit for locked salary
- [ ] Should get 403 error or redirect
- [ ] Edit button not visible on locked salary view

---

## Calculation Verification Tests

### 5. Salary Calculations

**Test Scenario H - Employee with Full Attendance:**
- [ ] Generate salary for May 2026
- [ ] Employee: Ahmed Hassan
  - [ ] Expected Total Hajira: 17.50
  - [ ] Expected Basic: 17.50 × 500 = 8,750
  - [ ] Expected Overtime: 21.75 × 200 = 4,350
  - [ ] Expected Before Deduction: 13,100
- [ ] Verify displayed values match expected
- [ ] Check net after deductions

**Test Scenario I - Employee with Advances:**
- [ ] Generate salary for May 2026
- [ ] Employee: Mohammad Ali (has advances)
  - [ ] Advance deductions visible
  - [ ] Deducted from oldest first
  - [ ] Never exceeds remaining balance
  - [ ] Never exceeds net salary
- [ ] Verify net_salary = basic + overtime - deducted

**Test Scenario J - Employee with No Attendance:**
- [ ] Create employee with no attendance for month
- [ ] Generate salary
- [ ] Total hajira = 0
- [ ] Basic amount = 0
- [ ] Net salary = 0

**Test Scenario K - Zero Overtime:**
- [ ] Find employee with no overtime for month
- [ ] Overtime amount = 0
- [ ] Net = basic + 0 - deducted

---

## Regeneration Tests

### 6. Regenerate Functionality

**Test Scenario L - Regenerate Updates Attendance:**
- [ ] Generate salary for May 2026
- [ ] Note current net_salary
- [ ] Manually add new attendance record for that employee/month
- [ ] Click "Regenerate" on that salary
- [ ] Total hajira increases
- [ ] Net salary updates
- [ ] Status remains "draft"
- [ ] Adjustment amount preserved

**Test Scenario M - Cannot Regenerate Locked:**
- [ ] Lock a salary sheet
- [ ] Try to access regenerate button
- [ ] Button should not be visible
- [ ] If manually accessing /regenerate endpoint:
  - [ ] Should get error or redirect

**Test Scenario N - Regenerate Recalculates Deductions:**
- [ ] Generate salary with advances
- [ ] Note deductions
- [ ] Manually delete an advance deduction
- [ ] Click Regenerate
- [ ] Deductions recalculated
- [ ] May be different if salary changed

---

## Advance Deduction Tests

### 7. Advance Deduction Logic

**Test Scenario O - Deduct from Oldest First:**
- [ ] Create employee with 2+ advances on different dates
- [ ] Advance 1: 2026-01-15, amount 5000
- [ ] Advance 2: 2026-02-20, amount 3000
- [ ] Generate salary for May 2026
- [ ] Both deductions should appear in order
- [ ] Advance 1 (older) appears first in deductions list

**Test Scenario P - Never Over-Deduct:**
- [ ] Create employee with 10,000 advance
- [ ] Generate salary for month with net = 5000
- [ ] Deducted should be 5000 (not 10000)
- [ ] Advance still has 5000 remaining for next month

**Test Scenario Q - Partial Deduction from Advance:**
- [ ] Employee has 8000 advance
- [ ] Generate salary with net_salary = 5000
- [ ] Deducted = 5000
- [ ] Next month generate salary with net = 6000
- [ ] Remaining balance from previous advance = 3000
- [ ] This salary deducts 3000 (not 6000)

**Test Scenario R - No Deduction if No Advances:**
- [ ] Generate salary for employee with no advances
- [ ] advance_deducted = 0
- [ ] No deductions table shown
- [ ] net_salary = basic + overtime

---

## Status Workflow Tests

### 8. Status Transitions

**Test Scenario S - Draft → Locked:**
- [ ] Create salary (status = draft)
- [ ] Click "Lock & Finalize"
- [ ] Status changes to "locked"
- [ ] locked_at is set to current timestamp

**Test Scenario T - Locked State Persistence:**
- [ ] Lock salary sheet
- [ ] Navigate away and back
- [ ] Status still "locked"
- [ ] locked_at still shows

**Test Scenario U - Cannot Change Locked:**
- [ ] Lock salary sheet
- [ ] Try to edit (view should prevent it)
- [ ] Try to regenerate (button not visible)
- [ ] Try to lock again (should show already locked error)

---

## API Tests

### 9. API Endpoints

**Test Scenario V - GET /api/salaries (List):**
```bash
curl -X GET "http://localhost:8000/api/salaries?month=2026-05" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
- [ ] Returns 200 OK
- [ ] Response has "success": true
- [ ] Data array contains salary objects
- [ ] Each salary has all fields
- [ ] Employee object nested
- [ ] Deductions array nested

**Test Scenario W - GET /api/salaries/{id} (Details):**
```bash
curl -X GET "http://localhost:8000/api/salaries/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
- [ ] Returns 200 OK
- [ ] Single salary object returned
- [ ] All details included
- [ ] Deductions with advance info shown

**Test Scenario X - POST /api/salaries (Generate):**
```bash
curl -X POST "http://localhost:8000/api/salaries" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"month": "2026-05", "employee_ids": [1, 2]}'
```
- [ ] Returns 201 Created
- [ ] Message shows count generated
- [ ] Data array contains created salaries
- [ ] All salaries have status "draft"

**Test Scenario Y - PUT /api/salaries/{id} (Lock with Adjustment):**
```bash
curl -X PUT "http://localhost:8000/api/salaries/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"adjustment_amount": 500}'
```
- [ ] Returns 200 OK
- [ ] Status changed to "locked"
- [ ] Adjustment amount updated
- [ ] Net salary recalculated
- [ ] locked_at timestamp set

**Test Scenario Z - POST /api/salaries/{id}/lock (Lock without Adjustment):**
```bash
curl -X POST "http://localhost:8000/api/salaries/1/lock" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
- [ ] Returns 200 OK
- [ ] Status changed to "locked"
- [ ] Adjustment not changed
- [ ] locked_at timestamp set

**Test Scenario AA - POST /api/salaries/{id}/regenerate (Regenerate):**
```bash
curl -X POST "http://localhost:8000/api/salaries/1/regenerate" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
- [ ] Returns 200 OK (if draft)
- [ ] Calculations updated
- [ ] Status remains "draft"
- [ ] Returns 403 if locked

---

## Error Handling Tests

### 10. Error Scenarios

**Test Scenario AB - Invalid Month Format:**
- [ ] Generate with month = "05-2026" (wrong format)
- [ ] Should show validation error
- [ ] Should not create salary

**Test Scenario AC - Non-existent Employee:**
- [ ] Generate with employee_id = 999 (doesn't exist)
- [ ] Should show validation error
- [ ] Should not create salary

**Test Scenario AD - Non-existent Salary:**
- [ ] Access /salaries/999 (doesn't exist)
- [ ] Should show 404 error
- [ ] Should redirect or show error message

**Test Scenario AE - Locked Salary Cannot be Edited:**
- [ ] Lock salary sheet
- [ ] Try to access /salaries/{id}/edit
- [ ] Should show 403 error
- [ ] Should redirect to view page

**Test Scenario AF - Locked Salary Cannot be Regenerated:**
- [ ] Lock salary sheet
- [ ] Try to POST /api/salaries/{id}/regenerate
- [ ] Should return 403 error
- [ ] Web: "Regenerate" button not visible

---

## Mobile Responsive Tests

### 11. Responsive Design

**On Mobile (< 768px):**
- [ ] Salary list shows card view (not table)
- [ ] Cards stack vertically
- [ ] Employee name prominent
- [ ] Key numbers visible: basic, overtime, deducted, net
- [ ] Action buttons fit on card
- [ ] No horizontal scrolling needed

**On Tablet (768px - 1024px):**
- [ ] Table view shows
- [ ] Columns readable
- [ ] No excessive wrapping
- [ ] Action buttons accessible

**On Desktop (> 1024px):**
- [ ] Table view shows all columns clearly
- [ ] Good spacing
- [ ] Summary cards visible above table
- [ ] Pagination shows properly

---

## Data Integrity Tests

### 12. Data Validation

**Test Scenario AG - Decimal Precision:**
- [ ] Enter adjustment = 1000.99
- [ ] Stored as exactly 1000.99 (not 1000.989999...)
- [ ] Calculations use correct precision
- [ ] Display shows 2 decimal places

**Test Scenario AH - Unique Constraint:**
- [ ] Generate salary for employee + month
- [ ] Try to manually create duplicate
- [ ] Database prevents duplicate
- [ ] or application shows appropriate error

**Test Scenario AI - Foreign Key Relationship:**
- [ ] Create salary for employee
- [ ] Try to delete employee
- [ ] Either cascades or shows error
- [ ] Advance deductions deleted with salary

---

## Performance Tests

### 13. Performance

- [ ] Salary list loads within 2 seconds
- [ ] Pagination with 15 items loads quickly
- [ ] Generate salary for 5 employees completes quickly
- [ ] Show details page loads with all relationships
- [ ] No N+1 queries (use eager loading)
- [ ] No timeout on regenerate

---

## Edge Case Tests

### 14. Edge Cases

**Test Scenario AJ - Zero Adjustment:**
- [ ] Lock salary with adjustment = 0
- [ ] Should work normally
- [ ] Net salary unchanged

**Test Scenario AK - Large Adjustment:**
- [ ] Lock salary with adjustment = 100000
- [ ] Should apply correctly
- [ ] No precision loss

**Test Scenario AL - Negative Net Salary:**
- [ ] Large deductions > net salary
- [ ] Result should be negative (if allowed)
- [ ] or application prevents it
- [ ] Error message if validation prevents

**Test Scenario AM - Regenerate Multiple Times:**
- [ ] Regenerate same draft salary 3+ times
- [ ] Should remain in draft status
- [ ] Calculations stable
- [ ] No data corruption

---

## Browser Compatibility

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Chrome
- [ ] Mobile Safari

---

## Summary
**Total Test Cases:** 45+
**Estimated Time:** 3-4 hours
**Recommendation:** Run once before production release

---

## Sign-Off
- [ ] All tests passed
- [ ] No critical bugs
- [ ] Ready for production

**Tested By:** ________________
**Date:** ________________
**Notes:** ________________

# Salary Payment Module - Test Checklist

## Overview
The Salary Payment module enables recording of salary payments against generated salary sheets. It prevents overpayment, auto-updates salary status, and tracks payment history per employee and salary sheet.

## Payment Status Workflow
```
draft → locked (not eligible for payment)
locked → partial (when paid_amount > 0 and due_amount > 0)
partial → paid (when due_amount = 0)
paid → partial (when payment reversed)
partial/paid → locked (when payment reversed back to locked state)
```

## Database Relationships
- **SalaryPayment** belongsTo SalarySheet
- **SalaryPayment** belongsTo Employee
- **SalaryPayment** belongsTo User (created_by)
- **SalarySheet** hasMany SalaryPayments

## Manual Test Checklist

### 1. Salary Payment Web Interface

#### 1.1 View Salary Payments List
- [ ] Navigate to `/payments` menu link
- [ ] Default view shows current month's locked/partial/paid salaries
- [ ] Month filter shows correct month (YYYY-MM format)
- [ ] Status filter dropdown contains: All, Locked, Partial, Paid
- [ ] Summary cards display:
  - Total Salary (sum of net_salary)
  - Total Paid (sum of paid_amount)
  - Total Due (sum of due_amount)
- [ ] All values are number_formatted with 2 decimals
- [ ] Salaries paginated (15 per page)

#### 1.2 Mobile Card View (Small Screen)
- [ ] On mobile width (<768px), view shows employee cards instead of table
- [ ] Each card displays:
  - Employee name (bold)
  - Department
  - Month
  - Net Salary
  - Paid Amount (green)
  - Due Amount (orange)
  - Status badge (locked/partial/paid)
  - "Pay" button

#### 1.3 Desktop Table View (Large Screen)
- [ ] On desktop width (≥768px), view shows table with columns:
  - Employee
  - Department
  - Month
  - Net Salary
  - Paid Amount
  - Due Amount
  - Status
  - Actions (Pay, View)

#### 1.4 Filter & Search
- [ ] Filter by month: Changes URL to ?month=YYYY-MM
- [ ] Filter by status: Changes URL to ?status={locked,partial,paid}
- [ ] Combination filters work together
- [ ] Clear button resets to current month, all statuses
- [ ] Pagination works correctly (page parameter in URL)

### 2. Record Payment

#### 2.1 Payment Form Access
- [ ] Click "Pay" button on salary card/row
- [ ] Navigates to `/payments/{salarySheet}/pay` route
- [ ] Form displays locked salary sheet details:
  - Employee name
  - Department
  - Month
  - Net Salary
  - Paid Amount (current)
  - Due Amount (remaining)
  - Status

#### 2.2 Payment Form Fields
- [ ] Amount input (required, numeric, max = due_amount)
- [ ] Payment Date input (required, date type)
- [ ] Payment Method dropdown (required):
  - Cash
  - Bank
  - Mobile Banking
- [ ] Note textarea (optional)
- [ ] "Record Payment" button (disabled if form invalid)
- [ ] "Cancel" button goes back to previous page

#### 2.3 Amount Validation
- [ ] HTML5 max="{{ $salarySheet->due_amount }}" prevents typing over limit
- [ ] Cannot submit form if amount > due_amount (backend validation)
- [ ] Cannot submit if amount ≤ 0
- [ ] Amount accepts decimal values (123.45)

#### 2.4 Payment Date Validation
- [ ] Must not be in future
- [ ] Defaults to today

#### 2.5 Successful Payment Recording
- [ ] After submitting valid payment:
  - Payment created in database with created_by = auth()->id()
  - SalarySheet.paid_amount updated += payment_amount
  - SalarySheet.due_amount updated -= payment_amount
  - SalarySheet.status updated automatically:
    - If due_amount = 0 → "paid"
    - If due_amount > 0 → "partial" (if paid_amount > 0)
  - Redirects to `/payments/{salarySheet}` (show page)
  - Success flash message displayed

#### 2.6 Payment History Sidebar (Payment Form)
- [ ] Sidebar shows payment history for current salary sheet
- [ ] Lists all previous payments with:
  - Date
  - Amount
  - Payment Method
  - Note (if present)
  - Reverse button (with confirmation)

### 3. View Salary Payment Details

#### 3.1 Payment Details Page
- [ ] Navigate to `/payments/{salarySheet}`
- [ ] Displays salary sheet information:
  - Employee name (heading)
  - Month
  - Department
  - Net Salary
  - Paid Amount
  - Due Amount
  - Status badge

#### 3.2 Payment History Table
- [ ] Shows all payments for salary sheet in reverse chronological order
- [ ] Columns: Date, Amount, Method, Note, Actions
- [ ] Each payment shows:
  - payment_date formatted
  - amount number_formatted with 2 decimals
  - payment_method capitalized (Cash, Bank, Mobile Banking)
  - note (if blank, shows "—")
  - Reverse button (delete action with confirmation)

#### 3.3 Summary Statistics
- [ ] Sidebar shows:
  - Total Paid (sum of all payments)
  - Total Due (remaining)
  - Payment Count (number of payments)
  - Status badge

#### 3.4 Reverse Payment Action
- [ ] Click "Reverse" button shows confirmation dialog
- [ ] On confirmation:
  - SalaryPayment deleted
  - SalarySheet.paid_amount recalculated (sum of remaining payments)
  - SalarySheet.due_amount recalculated (net_salary - paid_amount)
  - SalarySheet.status updated:
    - If due_amount = net_salary → "locked" (back to locked)
    - If due_amount > 0 and paid_amount > 0 → "partial"
  - Redirects back to show page
  - Success flash message displayed

### 4. Employee Payment History

#### 4.1 Access Employee Payment History
- [ ] Navigate to `/employees/{employee}/payment-history`
- [ ] OR click employee name on payment details page

#### 4.2 Employee Payment History Display
- [ ] Shows all payments for employee (all months)
- [ ] Month filter dropdown (optional):
  - Shows list of months with payments
  - Filter by single month
- [ ] Summary cards:
  - Total Paid (sum of all payments, or filtered month)
  - Payment Count
  - Average Payment

#### 4.3 Payment History Table/Cards
- [ ] Mobile view: card-based layout
- [ ] Desktop view: table with columns:
  - Month
  - Salary
  - Paid
  - Due
  - Status
  - Actions

### 5. API Endpoints Testing

#### 5.1 GET /api/payments (List Payments)
**Without filters:**
```bash
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/payments
```
- [ ] Returns JSON array of payments
- [ ] Pagination included (default 15 per page)
- [ ] Includes salary sheet and employee data

**With month filter:**
```bash
curl -H "Authorization: Bearer TOKEN" "http://localhost:8000/api/payments?month=2026-05"
```
- [ ] Returns only payments from May 2026

**With salary_sheet_id filter:**
```bash
curl -H "Authorization: Bearer TOKEN" "http://localhost:8000/api/payments?salary_sheet_id=1"
```
- [ ] Returns only payments for salary sheet ID 1

**With employee_id filter:**
```bash
curl -H "Authorization: Bearer TOKEN" "http://localhost:8000/api/payments?employee_id=1"
```
- [ ] Returns only payments for employee ID 1

**Combined filters:**
```bash
curl -H "Authorization: Bearer TOKEN" "http://localhost:8000/api/payments?month=2026-05&employee_id=1"
```
- [ ] Returns payments for employee 1 in May 2026

#### 5.2 POST /api/payments (Record Payment)
```bash
curl -X POST -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "salary_sheet_id": 1,
    "amount": 5000,
    "payment_date": "2026-05-15",
    "payment_method": "cash",
    "note": "May payment"
  }' \
  http://localhost:8000/api/payments
```
- [ ] Returns 201 Created
- [ ] Response includes created SalaryPayment data
- [ ] SalarySheet updated correctly
- [ ] Returns 422 if:
  - salary_sheet_id missing
  - amount missing or not numeric
  - amount > due_amount (overpayment)
  - payment_date missing or invalid
  - payment_method missing or invalid

#### 5.3 GET /api/salaries/{id}/payments (Salary Payments)
```bash
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/salaries/1/payments
```
- [ ] Returns 200 OK
- [ ] Returns all payments for salary sheet 1
- [ ] Includes employee data
- [ ] Includes created_by user data

#### 5.4 GET /api/employees/{id}/payments (Employee Payments)
```bash
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/employees/1/payments
```
- [ ] Returns 200 OK
- [ ] Returns all payments for employee 1
- [ ] Month filter works: `?month=2026-05`

**With month filter:**
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/employees/1/payments?month=2026-05"
```
- [ ] Returns only payments from May 2026

#### 5.5 DELETE /api/payments/{id} (Reverse Payment)
```bash
curl -X DELETE -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/payments/1
```
- [ ] Returns 200 OK or 204 No Content
- [ ] Payment deleted
- [ ] SalarySheet recalculated correctly
- [ ] Status updated correctly

### 6. Business Logic Testing

#### 6.1 Full Payment Scenario
- [ ] Generate salary for an employee in May 2026
- [ ] Lock the salary (net_salary = 10,000)
- [ ] Record payment for full amount (10,000)
- [ ] Verify:
  - paid_amount = 10,000
  - due_amount = 0
  - status = "paid"
- [ ] Payment recorded in database with created_by = current user

#### 6.2 Partial Payment Scenario
- [ ] Generate salary for an employee (net_salary = 10,000)
- [ ] Lock the salary
- [ ] Record first payment (5,000)
- [ ] Verify:
  - paid_amount = 5,000
  - due_amount = 5,000
  - status = "partial"
- [ ] Record second payment (3,000)
- [ ] Verify:
  - paid_amount = 8,000
  - due_amount = 2,000
  - status = "partial"
- [ ] Record final payment (2,000)
- [ ] Verify:
  - paid_amount = 10,000
  - due_amount = 0
  - status = "paid"

#### 6.3 Overpayment Prevention
- [ ] Generate salary (net_salary = 10,000)
- [ ] Lock salary
- [ ] Try to record payment for 10,001
- [ ] Verify:
  - Payment rejected with error message
  - SalarySheet unchanged
  - No payment created in database

#### 6.4 Payment Reversal Scenario
- [ ] Generate salary and lock (net_salary = 10,000)
- [ ] Record payment (8,000)
- [ ] Verify status = "partial"
- [ ] Reverse the payment
- [ ] Verify:
  - Payment deleted from database
  - paid_amount = 0
  - due_amount = 10,000
  - status = "locked"

#### 6.5 Multiple Reversals
- [ ] Generate salary, lock (net_salary = 10,000)
- [ ] Record 3 payments: 5,000 + 3,000 + 2,000
- [ ] Reverse middle payment (3,000)
- [ ] Verify:
  - paid_amount = 7,000
  - due_amount = 3,000
  - status = "partial"
  - Only 2 payments shown in history

#### 6.6 Decimal Handling
- [ ] Generate salary with decimal values:
  - hajira_rate = 123.45
  - overtime_rate = 67.89
  - overtime_hours = 2.5
- [ ] Record payment with decimal amount: 2567.89
- [ ] Verify all calculations preserve 2 decimal places
- [ ] No floating-point errors in calculations

### 7. Authorization Testing

#### 7.1 Authentication Required
- [ ] Unauthenticated user accessing `/payments` redirects to login
- [ ] Unauthenticated API request to `/api/payments` returns 401 Unauthorized

#### 7.2 Authenticated Access
- [ ] Logged-in user can access all payment routes
- [ ] Logged-in user can access all API endpoints
- [ ] created_by field stores correct user ID

### 8. Edge Cases

#### 8.1 Zero Due Amount Prevention
- [ ] Cannot record payment when due_amount already = 0
- [ ] Form validation prevents submitting with payment already complete

#### 8.2 Negative Amount Prevention
- [ ] Cannot submit payment with negative amount
- [ ] HTML5 validation prevents negative input

#### 8.3 Draft Salary Payment Prevention
- [ ] Draft salaries should not appear in payment list
- [ ] Draft salaries cannot be paid until locked

#### 8.4 Month Boundary Testing
- [ ] Payments for Feb 2026 show correctly
- [ ] Payments for Dec 2025 show correctly
- [ ] Month filter handles edge months

### 9. UI/UX Testing

#### 9.1 Responsive Design
- [ ] Mobile (320px): Cards stack, single column layout
- [ ] Tablet (768px): Mixed layout, cards + table
- [ ] Desktop (1024px+): Full table, sidebar summary

#### 9.2 Flash Messages
- [ ] Success messages appear after payment recorded
- [ ] Success messages appear after payment reversed
- [ ] Error messages appear for validation failures
- [ ] Flash messages auto-dismiss or have close button

#### 9.3 Confirmation Dialogs
- [ ] Reverse payment shows confirmation dialog
- [ ] Dialog asks "Are you sure?" with clear messaging
- [ ] Cancel button dismisses dialog
- [ ] Confirm button executes delete

#### 9.4 Loading States
- [ ] Submit buttons show loading state (disabled + spinner)
- [ ] API requests show loading state if applicable

### 10. Database Integrity Testing

#### 10.1 SalaryPayment Records
- [ ] salary_sheet_id is always set
- [ ] employee_id is always set
- [ ] payment_date is always set and valid
- [ ] amount is always positive decimal
- [ ] payment_method is one of: cash, bank, mobile_banking
- [ ] created_by is always set to current user
- [ ] created_at, updated_at timestamps are set

#### 10.2 SalarySheet Calculations
- [ ] paid_amount always = sum of all payments
- [ ] due_amount always = net_salary - paid_amount
- [ ] status correctly reflects paid/partial/locked state
- [ ] No orphaned payments after salary sheet deletion

## Automated Test Suite (Optional)

```php
// Feature Tests
- PaymentWebRouteTest
- PaymentListTest
- PaymentFormTest
- PaymentStorageTest
- PaymentReversalTest

// API Tests
- PaymentApiListTest
- PaymentApiCreateTest
- PaymentApiReversalTest

// Unit Tests
- PaymentServiceTest (recordPayment, updateStatus, reversPayment)
- SalarySheetStatusUpdateTest
```

## Sign-off
- [ ] All manual tests passed
- [ ] No JavaScript errors in browser console
- [ ] No PHP errors in Laravel logs
- [ ] Responsive design verified on multiple devices
- [ ] API responses validated with Postman/curl
- [ ] Database integrity confirmed
- [ ] Ready for production deployment

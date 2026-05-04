# Activity Log - Test Checklist

## Pre-Testing Setup

- [ ] Database migrated: `php artisan migrate`
- [ ] Cache cleared: `php artisan cache:clear`
- [ ] Routes cleared: `php artisan route:clear`
- [ ] Logged in as admin user (a@a.com)

---

## Access Tests

### Route Access
- [ ] `/activity-logs` accessible and shows empty state (no logs yet)
- [ ] `/activity-logs/1` returns 404 (no logs exist yet)
- [ ] Non-admin user gets 403 when accessing `/activity-logs`

### Permission Check
- [ ] Login as admin → Can access activity logs
- [ ] Login as accountant → Get 403 error
- [ ] Login as data_entry → Get 403 error

### Navigation
- [ ] "Activity Logs" link appears in admin menu
- [ ] "Activity Logs" link hidden for non-admin users
- [ ] Link location: Users → Activity Logs (under Users management)

---

## Employee Activity Logging

### Create Employee
1. Go to `/employees/create`
2. Fill form: Name="John Doe", Phone="03001234567", Department="IT", Hajira Rate="500", Status="active"
3. Click Create
4. Go to `/activity-logs`
5. Verify:
   - [ ] New "created" log entry appears
   - [ ] Model: Employee
   - [ ] User: Shows current user name
   - [ ] Properties show all entered fields
   - [ ] Timestamp is current time

### Update Employee
1. Go to `/employees` and edit an employee
2. Change: Name → "Jane Smith", Phone → "03009876543"
3. Click Update
4. Go to `/activity-logs`
5. Verify:
   - [ ] New "updated" log entry appears
   - [ ] Model: Employee
   - [ ] Click "View Details"
   - [ ] Shows old values: Name="John Doe", Phone="03001234567"
   - [ ] Shows new values: Name="Jane Smith", Phone="03009876543"
   - [ ] Changed fields highlighted in green/red

### Delete Employee
1. Go to `/employees`
2. Delete an employee
3. Go to `/activity-logs`
4. Verify:
   - [ ] New "deleted" log entry appears (if soft-delete implemented)
   - [ ] Model: Employee
   - [ ] Description: "deleted"

---

## Attendance Activity Logging

### Create Attendance
1. Go to `/attendance/daily`
2. Select an employee and set: Hajira="1", Overtime="2"
3. Click Save
4. Go to `/activity-logs?model=attendance`
5. Verify:
   - [ ] "created" log for Attendance appears
   - [ ] Shows hajira_value: 1
   - [ ] Shows overtime_hours: 2

### Update Attendance
1. Go to `/attendance/daily`
2. Modify same attendance: Hajira="1.5", Overtime="3"
3. Click Save
4. Go to `/activity-logs?model=attendance`
5. Verify:
   - [ ] "updated" log shows old and new values
   - [ ] hajira_value: 1 → 1.5 (visible in detail view)
   - [ ] overtime_hours: 2 → 3 (visible in detail view)

---

## Advance Activity Logging

### Create Advance
1. Go to `/advances` → Create
2. Select Employee, Enter Amount="10000", Reason="Medical", Date=today
3. Click Create
4. Go to `/activity-logs?model=advance`
5. Verify:
   - [ ] "created" log appears
   - [ ] Shows amount: 10000
   - [ ] Shows reason: Medical

### Update Advance
1. Edit the advance
2. Change Amount to "12000"
3. Click Update
4. Go to `/activity-logs?model=advance`
5. Verify:
   - [ ] "updated" log shows: amount 10000 → 12000

---

## Salary Sheet Activity Logging

### Create Salary Sheet
1. Go to `/salaries` → Generate
2. Select Month and Employee
3. Click Generate
4. Go to `/activity-logs?model=salary_sheet`
5. Verify:
   - [ ] "created" log appears
   - [ ] Shows calculated amounts (basic_amount, overtime_amount, net_salary)

### Update Salary Sheet
1. Edit the salary sheet (adjustment)
2. Change adjustment_amount
3. Click Update
4. Go to `/activity-logs?model=salary_sheet`
5. Verify:
   - [ ] "updated" log shows the change

### Lock Salary Sheet
1. Lock a salary sheet
2. Go to `/activity-logs?model=salary_sheet`
3. Verify:
   - [ ] Shows status change: draft → locked (or locked_at timestamp)

---

## Salary Payment Activity Logging

### Create Payment
1. Go to `/payments` → Create
2. Select Salary Sheet, Enter Amount="5000", Method="cash", Date=today
3. Click Create
4. Go to `/activity-logs?model=salary_payment`
5. Verify:
   - [ ] "created" log appears
   - [ ] Shows amount: 5000
   - [ ] Shows payment_method: cash

### Update Payment
1. Edit the payment
2. Change Amount to "6000"
3. Click Update
4. Go to `/activity-logs?model=salary_payment`
5. Verify:
   - [ ] "updated" log shows: amount 5000 → 6000

### Delete Payment
1. Delete a payment
2. Go to `/activity-logs?model=salary_payment`
3. Verify:
   - [ ] "deleted" log appears (if delete allowed)

---

## Filter Tests

### Filter by Model
1. Go to `/activity-logs`
2. Select Model: "Employee"
3. Click Filter
4. Verify:
   - [ ] Only Employee logs shown
   - [ ] No Attendance/Advance/etc logs
5. Reset and try each model type

### Filter by Action
1. Go to `/activity-logs`
2. Select Action: "created"
3. Click Filter
4. Verify:
   - [ ] Only "created" logs shown
   - [ ] No "updated" logs
5. Try Action: "updated"

### Filter by Date Range
1. Go to `/activity-logs`
2. Set From Date: "2026-05-01", To Date: "2026-05-02"
3. Click Filter
4. Verify:
   - [ ] Only logs from May 1-2 shown
   - [ ] No logs outside date range
5. Try different date ranges

### Combine Filters
1. Go to `/activity-logs`
2. Select Model: "Employee", Action: "updated", From Date: "2026-05-03"
3. Click Filter
4. Verify:
   - [ ] Shows only updated employee records from May 3rd onward
   - [ ] Other models not shown
   - [ ] Earlier dates not shown

### Reset Filters
1. Apply multiple filters
2. Click "Reset" button
3. Verify:
   - [ ] All filter fields cleared
   - [ ] Shows all logs again
   - [ ] No filters applied

---

## Detail View Tests

### Open Detail View
1. Go to `/activity-logs`
2. Click "View" on any log entry
3. Verify detail page loads with URL: `/activity-logs/{id}`

### View Basic Information
1. On detail page, check section "Basic Information"
   - [ ] User name visible
   - [ ] Action visible (created/updated/deleted)
   - [ ] Model name visible
   - [ ] Record ID visible
   - [ ] Timestamp visible

### View Changed Fields
1. Find an "updated" log with changes
2. Go to detail page
3. Verify "Changed Fields" section shows:
   - [ ] Each changed field listed
   - [ ] Old value in red box
   - [ ] New value in green box
   - [ ] Field names readable (underscores replaced with spaces)
   - [ ] Values properly formatted

### View IP & Browser
1. On detail page, check "IP & Browser" section
   - [ ] IP address visible
   - [ ] User agent visible

### View Raw JSON
1. On detail page, click "Raw Properties (JSON)"
   - [ ] JSON expands
   - [ ] Valid JSON shown
   - [ ] Contains attributes, old, new sections

### Back Navigation
1. On detail page, click "← Back to Activity Logs"
2. Verify:
   - [ ] Returns to list page
   - [ ] Previous filters preserved (if any)

---

## Mobile View Tests

### Mobile List View (< 640px)
1. View `/activity-logs` on mobile (or resize browser < 640px)
2. Verify:
   - [ ] Cards layout (not table)
   - [ ] Color-coded by action
   - [ ] Expandable "View Changes" section
   - [ ] "View Details" link at bottom
   - [ ] All readable on small screen

### Mobile Detail View
1. View `/activity-logs/1` on mobile
2. Verify:
   - [ ] Stacked layout
   - [ ] All sections readable
   - [ ] Back link works
   - [ ] JSON expandable

### Mobile Filters
1. On mobile, use filters
2. Verify:
   - [ ] All filter options visible
   - [ ] Filter button clickable
   - [ ] Reset button clickable
   - [ ] Results update properly

---

## Desktop View Tests

### Desktop List View
1. View `/activity-logs` on desktop (> 1024px)
2. Verify:
   - [ ] Table layout with columns
   - [ ] Headers: User, Action, Model, Changes, Time, Action
   - [ ] Rows aligned properly
   - [ ] All columns visible
   - [ ] Hover effect on rows

### Desktop Filters
1. On desktop, verify filter layout
2. Verify:
   - [ ] 5 filters in one row (or responsive)
   - [ ] Filter and Reset buttons aligned
   - [ ] Input fields sized appropriately

### Pagination
1. Create many logs (>50)
2. Go to `/activity-logs`
3. Verify:
   - [ ] Shows 50 per page (default)
   - [ ] Page numbers at bottom
   - [ ] Next/Previous buttons work
   - [ ] Clicking page number loads correct entries

---

## User Tracking Tests

### Verify Correct User
1. Create a change as user A
2. Go to activity logs
3. Verify:
   - [ ] User A name shown
   - [ ] User A email shown
4. Create change as user B
5. Verify:
   - [ ] User B name shown in new log
   - [ ] Old logs still show user A

### System Changes
1. If any system/console commands run
2. Go to activity logs
3. Verify:
   - [ ] Shows "System" or null for causer_id

---

## Timestamp Tests

### Timestamp Accuracy
1. Note current time
2. Make a change
3. Go to activity logs
4. Verify:
   - [ ] Timestamp matches current time (within 1 second)
   - [ ] Format readable: "M d, Y H:i" format
5. Go to detail page
6. Verify:
   - [ ] Shows full timestamp: "M d, Y H:i:s"

### Timestamp Sorting
1. Make multiple changes quickly
2. Go to `/activity-logs`
3. Verify:
   - [ ] Most recent first (descending)
   - [ ] Older entries at end

---

## Data Format Tests

### Decimal Numbers
1. Create/update decimal fields (rates, amounts)
2. Go to activity log
3. Verify:
   - [ ] Numbers formatted correctly
   - [ ] 2 decimal places shown (e.g., 500.00)

### Dates
1. Create/update date fields
2. Go to activity log
3. Verify:
   - [ ] Dates formatted correctly
   - [ ] No timezone issues

### Text Fields
1. Create/update text with special characters
2. Go to activity log
3. Verify:
   - [ ] Special characters preserved
   - [ ] No encoding issues
   - [ ] HTML not escaped visually (if intended)

### Null Values
1. Update field from value to NULL
2. Go to activity log
3. Verify:
   - [ ] Shows "Empty" or appropriate null indicator
   - [ ] Not showing as blank string

---

## Performance Tests

### Load Time
1. With 100+ logs in database
2. Go to `/activity-logs`
3. Measure load time
4. Verify:
   - [ ] Loads in < 500ms
   - [ ] No timeout errors

### Filter Performance
1. Apply complex filters with date range
2. Verify:
   - [ ] Results load in < 200ms
   - [ ] Smooth user experience

### Pagination Performance
1. Load page 5+ of logs
2. Verify:
   - [ ] Still loads quickly
   - [ ] No N+1 query problems

### Large Result Sets
1. Filter for large date range (months of data)
2. Verify:
   - [ ] Pagination prevents timeout
   - [ ] 50 per page manageable

---

## Security Tests

### Permission Enforcement
- [ ] Non-admin cannot GET /activity-logs (403)
- [ ] Non-admin cannot GET /activity-logs/1 (403)
- [ ] Admin can access both routes (200)

### No Sensitive Data
1. Check logs for logged-in user
2. Verify:
   - [ ] No passwords shown
   - [ ] No tokens shown
   - [ ] No API keys shown

### User Isolation
1. Login as user A, create activity
2. Login as user B, view activity logs
3. Verify:
   - [ ] Both users' activities visible (expected for admin)
   - [ ] No personal data leak

---

## Edge Cases

### Empty State
1. Clear all activity logs or on fresh install
2. Go to `/activity-logs`
3. Verify:
   - [ ] Shows "No activity logs found"
   - [ ] Filters still visible
   - [ ] No errors

### No Results
1. Apply filter that matches no logs
2. Verify:
   - [ ] Shows "No activity logs found"
   - [ ] Can reset filters

### Single Changed Field
1. Update only one field on record
2. Go to detail page
3. Verify:
   - [ ] Shows only that one field
   - [ ] Old and new values correct

### All Fields Changed
1. Update all fields of a record
2. Go to detail page
3. Verify:
   - [ ] All fields listed
   - [ ] Each shows old → new
   - [ ] Page renders properly (no layout breaks)

### Very Long Values
1. Create/update with very long text (1000+ chars)
2. Go to activity log
3. Verify:
   - [ ] Value visible
   - [ ] Word-break applied (not causing layout break)
   - [ ] Readable (not truncated unexpectedly)

---

## Database Tests

### Verify Table Structure
```bash
php artisan tinker
Activity::first()  # Check model works
```

- [ ] activity_log table exists
- [ ] All columns present (id, log_name, description, subject_type, subject_id, causer_type, causer_id, properties, created_at, updated_at)
- [ ] Indexes created on created_at and subject_type/subject_id

### Verify Data Integrity
```bash
Activity::count()  # Total logs
Activity::whereNull('causer_id')->count()  # Null users
Activity::where('description', 'created')->count()  # Created logs
```

- [ ] Correct log count
- [ ] Expected distribution of actions
- [ ] Relationships valid

---

## Integration Tests

### With Role/Permission System
- [ ] Activity logs link only shows for admin
- [ ] Non-admin cannot access via URL even if link added
- [ ] Permission enforcement works

### With Employee Module
- [ ] Employee CRUD changes logged
- [ ] Bulk updates logged
- [ ] Status changes logged

### With Attendance Module
- [ ] Bulk save logged
- [ ] Bulk actions logged
- [ ] Each record captured

### With Advance Module
- [ ] Create advance logged
- [ ] Update advance logged
- [ ] Delete advance logged

### With Salary Module
- [ ] Generate salary logged
- [ ] Lock salary logged
- [ ] Regenerate logged

### With Payment Module
- [ ] Create payment logged
- [ ] Delete payment logged
- [ ] Adjustments logged

---

## Completion Sign-Off

- **Total Tests**: 150+
- **Critical Tests**: Permission, Logging, Detail View
- **Performance Target**: < 500ms page load

| Component | Status | Notes |
|-----------|--------|-------|
| Access Control | [ ] | |
| Employee Logging | [ ] | |
| Attendance Logging | [ ] | |
| Advance Logging | [ ] | |
| Salary Logging | [ ] | |
| Payment Logging | [ ] | |
| Filters | [ ] | |
| Detail View | [ ] | |
| Mobile View | [ ] | |
| Desktop View | [ ] | |
| Performance | [ ] | |
| Security | [ ] | |
| Integration | [ ] | |

**Tester Name**: _______________
**Date**: _______________
**Status**: [ ] Pass [ ] Fail [ ] Partial
**Issues Found**: _______________________________________________________________

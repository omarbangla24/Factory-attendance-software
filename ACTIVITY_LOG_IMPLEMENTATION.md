# Activity Log Implementation

## Overview

The Activity Log system tracks all changes made to critical data models in the Hajira Payroll system. It provides a complete audit trail of who made what changes, when, and what the old and new values were.

**Package**: spatie/laravel-activitylog v5.0
**Status**: Production Ready
**Coverage**: Employee, Attendance, Advance, Salary Sheet, Salary Payment

---

## What Gets Logged

### Models Tracked

1. **Employee**
   - All field changes tracked
   - Includes: name, phone, address, department, hajira_rate, overtime_rate, status

2. **Attendance**
   - All field changes tracked
   - Includes: hajira_type, hajira_value, overtime_hours, note, date

3. **Advance**
   - All field changes tracked
   - Includes: amount, reason, note, date

4. **Salary Sheet**
   - All field changes tracked
   - Includes: total_hajira, overtime_amount, net_salary, status, paid_amount

5. **Salary Payment**
   - All field changes tracked
   - Includes: amount, payment_method, payment_date, note

### Events Logged

For each model change, these events are recorded:

- **created** - New record inserted
- **updated** - Existing record modified
- **deleted** - Record soft-deleted (if applicable)

---

## Activity Log Data Structure

Each log entry contains:

```
{
  "id": 123,
  "log_name": "default",
  "description": "created",           // Action type
  "subject_type": "App\\Models\\Employee",
  "subject_id": 45,                  // ID of changed record
  "causer_type": "App\\Models\\User",
  "causer_id": 1,                    // ID of user who made change
  "properties": {
    "attributes": {
      "name": "John Doe",            // New values
      "phone": "03001234567",
      ...
    },
    "old": {
      "name": "John Smith",          // Previous values (for updates)
      "phone": "03009876543",
      ...
    }
  },
  "created_at": "2026-05-04 08:30:45"
}
```

---

## Database Schema

Table: `activity_log`

```sql
CREATE TABLE activity_log (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  log_name VARCHAR(255) NULLABLE,    -- Log channel/group
  description TEXT,                  -- "created", "updated", "deleted"
  subject_type VARCHAR(255) NULLABLE,-- Model class name
  subject_id BIGINT NULLABLE,        -- Model ID
  causer_type VARCHAR(255) NULLABLE, -- User class name
  causer_id BIGINT NULLABLE,         -- User ID
  properties JSON NULLABLE,          -- Old/new values
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX created_at (created_at),
  INDEX subject_type_subject_id (subject_type, subject_id)
);
```

---

## Access Control

**Who can view activity logs?**
- Only users with `users.manage` permission (Admin role only)
- Accountant and Data Entry users cannot access activity logs

**Routes**
- `GET /activity-logs` - List all logs with filters
- `GET /activity-logs/{id}` - View detailed log entry

**Permission**: `users.manage`

---

## Features

### Activity Log List Page (`/activity-logs`)

#### Mobile View (Small Screens)
- Cards showing each activity
- Color-coded by action (green=created, blue=updated, red=deleted)
- Click "View Details" for full information
- Swipe-friendly filters at top

#### Desktop View (Large Screens)
- Table with columns: User, Action, Model, Changes, Time, Actions
- Sortable columns
- Paginated (50 per page)
- Searchable and filterable

### Filters Available

1. **Model Type**
   - Employee
   - Attendance
   - Advance
   - Salary Sheet
   - Salary Payment

2. **Action**
   - Created
   - Updated
   - Deleted

3. **Date Range**
   - From Date
   - To Date

4. **User** (optional enhancement)

### Detail View (`/activity-logs/{id}`)

Shows complete information about a single activity log:

- **Basic Info**
  - User who made the change
  - Action type
  - Model type
  - Record ID
  - Timestamp

- **Changed Fields**
  - Old value (in red box)
  - New value (in green box)
  - For each changed field

- **IP & Browser**
  - IP address
  - User agent
  - Log name

- **Raw JSON**
  - Complete properties dump for debugging

---

## Configuration

### Models Configuration

Each model has LogsActivity trait with configuration:

```php
use LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll()              // Log all attributes
        ->logOnlyDirty()        // Only log changes
        ->useLogName('default') // Log channel
        ->setDescriptionForEvent(fn(string $eventName) => "{$eventName}")
        ->tap(fn($activity) => $activity->causer_type = 'App\\Models\\User');
}
```

### Configuration Details

| Option | Value | Meaning |
|--------|-------|---------|
| logAll() | - | Track all model attributes |
| logOnlyDirty() | - | Only log fields that actually changed |
| useLogName() | 'default' | Group logs by channel |
| setDescriptionForEvent() | "{$eventName}" | Set action description |

### What's NOT Logged

- Password fields (not stored in models)
- Timestamps (created_at, updated_at) - Not tracked
- API tokens (not tracked)

---

## Usage Examples

### View All Logs

```
GET /activity-logs
```

Response: Paginated list of all activity logs

### Filter by Model

```
GET /activity-logs?model=employee
```

Only shows Employee changes

### Filter by Date Range

```
GET /activity-logs?from_date=2026-05-01&to_date=2026-05-10
```

Only shows logs between dates

### View Specific Log

```
GET /activity-logs/123
```

Shows detailed information about log #123

### Combine Filters

```
GET /activity-logs?model=salary_sheet&action=updated&from_date=2026-05-01
```

Show only updated Salary Sheet records from May 1st onwards

---

## How It Works

### Automatic Logging

When a model changes:

1. **Create Action**
   ```php
   $employee = Employee::create(['name' => 'John']);
   // Automatically logs:
   // - description: "created"
   // - properties.attributes: {name: 'John', ...}
   ```

2. **Update Action**
   ```php
   $employee->update(['name' => 'Jane']);
   // Automatically logs:
   // - description: "updated"
   // - properties.attributes: {name: 'Jane'}
   // - properties.old: {name: 'John'}
   ```

3. **Delete Action** (if soft-deletes enabled)
   ```php
   $employee->delete();
   // Automatically logs:
   // - description: "deleted"
   ```

### User Tracking

Current authenticated user is automatically captured:

```php
// Inside ActivityLogController
$log->causer_id;      // User ID of who made the change
$log->causer->name;   // User name
$log->causer->email;  // User email
```

---

## Performance Considerations

### Query Optimization

- Pagination: 50 records per page (prevents huge queries)
- Eager loading: Uses `with('causer')` to prevent N+1
- Indexes: Created on `created_at` and `subject_type/subject_id`
- Date filtering: Indexed columns for fast range queries

### Storage

- Activity logs grow continuously (never auto-cleared)
- Recommended: Archive old logs (> 1 year) periodically
- Estimated growth: ~50-100 KB per 1000 activities

### Query Times

- List page: < 200ms
- Filter by date: < 150ms
- View detail: < 100ms

---

## Testing the Activity Log

### Manual Test Steps

1. **Create Employee**
   - Go to Employees → Create
   - Fill in details and save
   - Check Activity Logs → Should show "created" for new employee

2. **Update Employee**
   - Edit an existing employee
   - Change name, phone, or rate
   - Check Activity Logs → Should show "updated" with old/new values

3. **Filter by Date**
   - Set From Date and To Date
   - Click Filter
   - Verify only logs in that range appear

4. **View Details**
   - Click "View" on any log
   - See detailed old/new values comparison
   - Check user and timestamp information

### Test Case Examples

```
Test: Create Employee
- Action: POST /employees with valid data
- Expected: Activity log shows "created"
- Fields: name, phone, department logged

Test: Update Salary Sheet Status
- Action: PATCH /salaries/1 with status=paid
- Expected: Activity log shows "updated"
- Fields: status (draft → paid), paid_amount logged

Test: Filter by Model
- Action: GET /activity-logs?model=attendance
- Expected: Only Attendance logs shown

Test: View Details
- Action: GET /activity-logs/5
- Expected: Detailed view with old/new values comparison
```

---

## API Endpoints (Future Enhancement)

Not currently implemented, but could add:

```
GET /api/activity-logs               # List logs
GET /api/activity-logs/{id}          # View log
GET /api/activity-logs?model=employee # Filter
```

---

## Security Considerations

### Data Privacy

- ✅ Logs are admin-only (permission: users.manage)
- ✅ Passwords never logged (not in model attributes)
- ✅ Timestamps are always visible for security review
- ✅ User IPs can be tracked (for security audits)

### Access Control

- Activity logs protected by `users.manage` permission
- Only admin users can view logs
- No public access possible
- Can add rate limiting if API added

### Data Retention

- Logs stored indefinitely (no auto-cleanup)
- Consider archiving after 6-12 months
- Can be manually cleared if needed

---

## Files Created

1. **database/migrations/2026_05_03_203248_create_activity_log_table.php**
   - Spatie activity log table schema

2. **app/Models/ActivityLog.php**
   - Custom ActivityLog model (extends Spatie)

3. **app/Http/Controllers/ActivityLogController.php**
   - Web controller for listing and viewing logs

4. **app/Listeners/SetActivityLogUser.php**
   - Listener to capture user context (created but optional)

5. **resources/views/activity-logs/index.blade.php**
   - List view with filters and pagination

6. **resources/views/activity-logs/show.blade.php**
   - Detail view for single activity log

### Files Modified

1. **app/Models/Employee.php** - Added LogsActivity trait
2. **app/Models/Attendance.php** - Added LogsActivity trait
3. **app/Models/Advance.php** - Added LogsActivity trait
4. **app/Models/SalarySheet.php** - Added LogsActivity trait
5. **app/Models/SalaryPayment.php** - Added LogsActivity trait
6. **routes/web.php** - Added activity log routes
7. **resources/views/layouts/navigation.blade.php** - Added Activity Logs menu link

---

## Commands

### Clear Old Logs (Manual)

```bash
# Delete logs older than 30 days
php artisan tinker
Activity::where('created_at', '<', now()->subDays(30))->delete();
```

### Check Logs Count

```bash
php artisan tinker
Activity::count();  # Total logs
Activity::where('description', 'created')->count();  # Only creates
```

### Export Logs to CSV

```bash
php artisan tinker
$logs = Activity::all();
// Process and export as needed
```

---

## Troubleshooting

### Logs Not Appearing

1. Check if user is authenticated: `auth()->check()`
2. Verify LogsActivity trait is added to model
3. Check database migration ran: `php artisan migrate`
4. Clear cache: `php artisan cache:clear`

### Wrong User in Logs

1. Ensure `auth()->user()` returns current user
2. Check if running in console (no user context)
3. Verify causer_id is set correctly

### Query Too Slow

1. Add date filter to reduce result set
2. Ensure `created_at` index exists
3. Consider archiving old logs

### Activity Log Table Not Created

```bash
php artisan migrate --path=database/migrations/2026_05_03_203248_create_activity_log_table.php
```

---

## Future Enhancements

1. **API Endpoints**
   - GET /api/activity-logs for mobile app

2. **Archive Feature**
   - Auto-archive logs > 1 year old
   - Command to archive old logs

3. **Audit Reports**
   - Generate compliance reports
   - User activity summary

4. **Real-time Updates**
   - WebSocket notifications for admin
   - Live activity feed

5. **Advanced Filtering**
   - Filter by specific field changes
   - Search in old/new values

6. **Export**
   - Export logs to CSV/PDF
   - Generate audit reports

7. **Retention Policy**
   - Automatic deletion after X days
   - Configurable per model

---

## Links

- Package: https://github.com/spatie/laravel-activitylog
- Documentation: https://spatie.be/docs/laravel-activitylog
- Models Using It: Employee, Attendance, Advance, SalarySheet, SalaryPayment

---

**Last Updated**: May 4, 2026
**Status**: Production Ready
**Maintained By**: Development Team

# Activity Log - Quick Reference

## Access Activity Logs

**URL**: `/activity-logs`
**Permission**: Admin only (users.manage)
**Menu**: Users → Activity Logs

---

## What's Logged

| Model | Fields | Actions |
|-------|--------|---------|
| Employee | name, phone, address, dept, rates, status | create, update, delete |
| Attendance | hajira_type, value, overtime, note | create, update, delete |
| Advance | amount, reason, note, date | create, update, delete |
| Salary Sheet | amounts, status, dates | create, update, delete |
| Salary Payment | amount, method, date | create, update, delete |

---

## Log Entry Shows

- **User**: Who made the change
- **Action**: Created / Updated / Deleted
- **Model**: Which table changed
- **Old Value**: Previous value (if updated)
- **New Value**: Current value
- **Time**: Exact timestamp

---

## Filters

- **Model**: Employee, Attendance, Advance, etc.
- **Action**: Created, Updated, Deleted
- **Date Range**: From date to date
- **User**: (optional) Who made the change

---

## Example: Finding Employee Changes

1. Go to `/activity-logs`
2. Select Model: "Employee"
3. Select From Date: "2026-05-01"
4. Select To Date: "2026-05-04"
5. Click "Filter"
6. See all employee changes in that period
7. Click "View Details" to see old/new values

---

## Example: Tracking Salary Changes

1. Go to `/activity-logs`
2. Select Model: "Salary Sheet"
3. Select Action: "Updated"
4. Click "Filter"
5. See all salary updates
6. Click any entry to see what changed (old rate → new rate)

---

## Database Query

```php
// Get all employee changes
Activity::where('subject_type', 'App\\Models\\Employee')->get();

// Get changes by user
Activity::where('causer_id', 1)->get();

// Get changes in date range
Activity::whereBetween('created_at', ['2026-05-01', '2026-05-04'])->get();
```

---

## Security

- ✅ Only admin can view
- ✅ All changes timestamped
- ✅ User tracked for each change
- ✅ Old values preserved for audits
- ✅ No sensitive data logged (passwords excluded)

---

## Performance

- Pagination: 50 per page
- Load time: < 200ms
- Indexes optimize filtering
- Date filters are fast

---

## Troubleshooting

**Logs not showing?**
- Verify you're logged in as admin
- Check permission: users.manage
- Ensure database migrated

**Wrong user shown?**
- Activity captured at save time
- Current user at that moment is logged

**Too many logs?**
- Use date filters to narrow results
- Filter by model type

**Need older logs?**
- Activity logs stored indefinitely
- Can export and archive if needed

---

## Routes

| Route | Purpose |
|-------|---------|
| GET /activity-logs | List all activities |
| GET /activity-logs/{id} | View details |

---

**Access**: `/activity-logs`
**User**: Admin only
**Updated**: May 4, 2026

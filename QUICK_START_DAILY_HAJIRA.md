# Daily Hajira Module - Quick Start Guide

## ⚡ 30-Second Overview

A mobile-first interface to mark employee attendance with Hajira values (0/1/1.5 days worked).

```
Date Picker → Search/Filter → Employee Cards → Bulk Actions → Save
```

## 🚀 Quick Start

### 1. Start the Server
```bash
cd /Users/mdomarfaruk/Sites/fms
php artisan serve
# Opens at http://localhost:8000
```

### 2. Login
```
Email:    hr@example.com
Password: password
```

### 3. Access Daily Hajira
Click "Daily Hajira" in navigation menu, or go to:
```
http://localhost:8000/attendance/daily
```

## 📱 How to Use

### Mark Attendance
1. Select date (today is default)
2. Search employee or filter by department
3. Click hajira button (Absent / 1 / 1.5)
4. Enter overtime hours (optional)
5. Add note (optional)
6. Click "Save All Attendance"

### Bulk Actions
- **Set All Absent** → Mark all employees absent
- **Set All 1 Hajira** → Mark all employees worked 1 day
- **Set All 1.5 Hajira** → Mark all employees worked 1.5 days  
- **Clear Overtime** → Reset all overtime to 0

### Summary Stats
Shows in real-time:
- Total Present (non-absent count)
- Total Absent
- Total Hajira (sum of days)
- Total Overtime Hours

## 🔌 API Endpoints

All require auth token in header: `Authorization: Bearer TOKEN`

### List Attendance
```bash
GET /api/attendances?date=2024-05-03
```

### Save Attendance
```bash
POST /api/attendances/bulk-save
Body: {
  "attendances": [
    {
      "employee_id": 1,
      "date": "2024-05-03",
      "hajira_type": "one",
      "hajira_value": 1,
      "overtime_hours": 0
    }
  ]
}
```

### Get Summary
```bash
GET /api/attendances/summary?date=2024-05-03
```

## 📚 Full Documentation

| File | Purpose |
|------|---------|
| DAILY_HAJIRA_IMPLEMENTATION_SUMMARY.md | Executive summary & features |
| DAILY_HAJIRA_MODULE.md | Complete implementation guide |
| DAILY_HAJIRA_API_QUICK_REFERENCE.md | API documentation |
| DAILY_HAJIRA_TEST_CHECKLIST.md | Testing checklist |

## 🛠️ Commands

```bash
# Clear caches
php artisan cache:clear view:clear route:clear

# View routes
php artisan route:list

# Restart server
php artisan serve --port=8000

# Generate API token (for testing)
php artisan tinker
# Then: $token = User::first()->createToken('api')->plainTextToken;
```

## 🎯 Key Features

✅ Mobile-first responsive design  
✅ Date picker with prev/next buttons  
✅ Employee search & department filter  
✅ Hajira buttons (Absent/1/1.5)  
✅ Overtime hours input  
✅ Bulk operations  
✅ Daily summary  
✅ Duplicate prevention  
✅ REST API ready  
✅ Sticky save button  

## 🔑 Important Notes

- **Duplicate Prevention**: Same (employee_id, date) cannot have 2 records
- **Auto-Update**: Saving same record again updates it (no duplicate)
- **Mobile**: Optimized for mobile screens (<768px)
- **API**: Requires authentication token
- **Data**: 5 test employees with 85 attendance records

## ❓ Troubleshooting

| Issue | Solution |
|-------|----------|
| Page not loading | Clear cache: `php artisan cache:clear` |
| Not authenticated | Log in with hr@example.com / password |
| Data not saving | Check browser console for errors |
| API 401 error | Generate token: see "Generate API Token" section |
| Duplicate records | Use bulk-save endpoint (handles updates) |

## 📊 Test Data

- **Employees**: 5 active employees
- **Attendance**: 85 existing records
- **Date Range**: Multiple dates for testing

## 🔗 Related Modules

- Employee Module: Manage employee master data
- Payroll Module: Coming in Phase 5
- Reports: Coming in Phase 6

## 📞 Support

Refer to:
1. DAILY_HAJIRA_MODULE.md for detailed info
2. DAILY_HAJIRA_API_QUICK_REFERENCE.md for API help
3. DAILY_HAJIRA_TEST_CHECKLIST.md for testing

---

**Version**: 1.0  
**Status**: ✅ Production Ready  
**Last Updated**: May 3, 2024  

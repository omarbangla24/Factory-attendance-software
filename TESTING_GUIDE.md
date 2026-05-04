# Hajira Payroll System - Testing Guide

## Quick Start

```bash
cd /Users/mdomarfaruk/Sites/fms
php artisan serve --port=8000
```

Then open: **http://localhost:8000**

**Login Credentials:**
- Email: `hr@example.com`
- Password: `password`

---

## 1. Authentication Testing

### Test 1.1: Login Flow
- Navigate to http://localhost:8000/login
- Enter hr@example.com / password
- Click Sign in
- ✓ Should redirect to dashboard
- ✓ User profile shows in sidebar

### Test 1.2: Logout
- Click "Logout" button (top-right)
- ✓ Should redirect to login page

---

## 2. Dashboard Testing

### Test 2.1: Dashboard Components
- Login successfully
- ✓ Dashboard shows 4 info cards
- ✓ Quick action buttons visible
- ✓ System status shows

---

## 3. Mobile Responsiveness

### Test 3.1: Mobile (375px)
- Open DevTools, set width to 375px
- ✓ Sidebar hidden
- ✓ Hamburger menu visible
- ✓ Content readable, no horizontal scroll

### Test 3.2: Tablet (768px)
- Set width to 768px
- ✓ Sidebar still hidden
- ✓ Hamburger menu works

### Test 3.3: Desktop (1920px)
- Set width to 1920px
- ✓ Sidebar visible on left
- ✓ Current page highlighted

---

## 4. Employee Management

### Test 4.1: View Employees
- Click "Employees" in sidebar
- ✓ Employee list loads
- ✓ "+ Add Employee" button visible

### Test 4.2: Create Employee
- Click "+ Add Employee"
- Fill form:
  - Code: EMP001
  - Name: John Doe
  - Email: john@example.com
  - Designation: Engineer
  - Hire Date: 2024-01-15
- Click Create
- ✓ Employee appears in list

### Test 4.3: Edit Employee
- Click "Edit" on any employee
- Change name, click Update
- ✓ Changes reflected in list

### Test 4.4: Delete Employee
- Click "Delete" on employee
- Confirm deletion
- ✓ Employee removed

---

## 5. Attendance Tracking

### Test 5.1: View Attendance
- Click "Daily Hajira" in sidebar
- ✓ Attendance list loads
- ✓ "+ Mark Attendance" button visible

### Test 5.2: Mark Attendance
- Click "+ Mark Attendance"
- Fill form:
  - Employee: Select from list
  - Date: 2024-05-03
  - Status: present
  - Check-in: 09:00
  - Check-out: 17:00
- Click Create
- ✓ Record appears with "present" (green badge)

### Test 5.3: Different Status Colors
- Create records with different status values
- ✓ present = Green badge
- ✓ absent = Red badge
- ✓ leave = Blue badge
- ✓ half_day = Yellow badge

### Test 5.4: Edit Attendance
- Click "Edit" on attendance record
- Change status to "leave"
- Click Update
- ✓ Status updates to blue badge

### Test 5.5: Delete Attendance
- Click "Delete" on record
- Confirm deletion
- ✓ Record removed

---

## 6. Form Validation

### Test 6.1: Required Fields
- Try creating employee without code
- ✓ Error: "Code is required"

### Test 6.2: Unique Constraints
- Create employee with email: test@example.com
- Try creating another with same email
- ✓ Error: "Email already exists"

### Test 6.3: Date Validation
- Try entering invalid date
- ✓ Date validation error shown

---

## 7. Navigation

### Test 7.1: Menu Items
- Click each sidebar item
- ✓ Each navigates to correct page
- ✓ Current page is highlighted

### Test 7.2: Mobile Navigation
- Resize to mobile (375px)
- Click hamburger icon
- ✓ Mobile menu opens
- Click menu item
- ✓ Navigates to page, menu closes

---

## 8. Database Verification

```bash
php artisan tinker

# Check data was saved
App\Models\Employee::all();
App\Models\Attendance::all();
App\Models\User::all();

# Check relationships
$emp = App\Models\Employee::first();
$emp->attendance;  # Should show attendance records
```

---

## Success Checklist

- [ ] Login/Logout works
- [ ] Dashboard loads
- [ ] Mobile view responsive
- [ ] Create employee works
- [ ] Edit employee works
- [ ] Delete employee works
- [ ] Mark attendance works
- [ ] Edit attendance works
- [ ] Delete attendance works
- [ ] Status colors display correctly
- [ ] Form validation works
- [ ] Navigation works on all pages
- [ ] Data saves to database
- [ ] No console errors

---

**All tests passed? Ready for Phase 2!**

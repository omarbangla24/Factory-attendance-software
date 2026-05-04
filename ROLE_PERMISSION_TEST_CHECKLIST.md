# Role-Based Permission System - Test Checklist

## Pre-Testing Setup

- [ ] Roles and permissions seeded: `php artisan db:seed --class=RoleAndPermissionSeeder`
- [ ] Cache cleared: `php artisan cache:clear && php artisan config:clear`
- [ ] Routes cleared: `php artisan route:clear`
- [ ] Test users created with different roles:
  - [ ] Admin user (a@a.com) - Already assigned admin role
  - [ ] Accountant user - Create new user and assign accountant role
  - [ ] Data Entry user - Create new user and assign data_entry role

## Authentication Tests

- [ ] Login as admin user
- [ ] Login as accountant user
- [ ] Login as data_entry user
- [ ] Logout works for all users
- [ ] Invalid credentials rejected
- [ ] Unauthorized access shows 403 error

## Web Route Protection Tests (Admin User)

Admin user should access ALL routes:

### Employees Routes
- [ ] GET /employees → 200 OK (list loads)
- [ ] GET /employees/create → 200 OK (create form loads)
- [ ] POST /employees → Success (can create)
- [ ] GET /employees/{id}/edit → 200 OK (edit form loads)
- [ ] PUT /employees/{id} → Success (can update)
- [ ] POST /employees/bulk-update-rates → Success
- [ ] POST /employees/bulk-update-status → Success
- [ ] DELETE /employees/{id} → Success (can delete)

### Attendance Routes
- [ ] GET /attendance/daily → 200 OK (loads)
- [ ] POST /attendance/bulk-save → Success (can save)
- [ ] POST /attendance/bulk-action → Success (bulk actions work)

### Advances Routes
- [ ] GET /advances → 200 OK (list loads)
- [ ] POST /advances → Success (can create)
- [ ] GET /advances/{id}/edit → 200 OK (edit loads)
- [ ] PUT /advances/{id} → Success (can update)
- [ ] DELETE /advances/{id} → Success (can delete)

### Salaries Routes
- [ ] GET /salaries → 200 OK
- [ ] POST /salaries → Success (generate)
- [ ] PATCH /salaries/{id}/regenerate → Success

### Payments Routes
- [ ] GET /payments → 200 OK
- [ ] POST /payments → Success (can pay)
- [ ] DELETE /payments/{id} → Success (can delete)

### Reports Routes
- [ ] GET /reports → 200 OK
- [ ] GET /reports/monthly-hajira → 200 OK
- [ ] GET /reports/overtime → 200 OK
- [ ] GET /reports/absent → 200 OK
- [ ] GET /reports/advance → 200 OK
- [ ] GET /reports/salary-sheet → 200 OK
- [ ] GET /reports/payment → 200 OK
- [ ] GET /reports/employee-ledger → 200 OK
- [ ] GET /reports/accounts-summary → 200 OK

### User Management Routes
- [ ] GET /users → 200 OK (users list)
- [ ] GET /users/{id}/edit → 200 OK (edit user)
- [ ] PUT /users/{id} → Success (update user)
- [ ] POST /users/{id}/role → Success (assign role)

## Web Route Protection Tests (Accountant User)

Accountant should access ONLY these routes:

### Allowed Routes (200 OK)
- [ ] Dashboard /dashboard
- [ ] Employees (view only): /employees, /employees/{id}
- [ ] Advances: /advances, /advances/create, /advances/{id}, etc.
- [ ] Salaries: /salaries, /salaries/create, /salaries/{id}, etc.
- [ ] Payments: /payments, /payments/{id}, /payments/{id}/pay
- [ ] Reports: All report routes

### Denied Routes (403 Forbidden)
- [ ] POST /employees (create employee) → 403 ✓
- [ ] PUT /employees/{id} (edit employee) → 403 ✓
- [ ] DELETE /employees/{id} (delete employee) → 403 ✓
- [ ] GET /attendance/daily (daily hajira) → 403 ✓
- [ ] POST /attendance (create attendance) → 403 ✓
- [ ] GET /users (user management) → 403 ✓
- [ ] POST /users/{id}/role (assign role) → 403 ✓

## Web Route Protection Tests (Data Entry User)

Data Entry should access ONLY attendance and employee viewing:

### Allowed Routes (200 OK)
- [ ] Dashboard /dashboard
- [ ] Employees (view only): /employees, /employees/{id}
- [ ] Daily Hajira: /attendance/daily
- [ ] POST /attendance/bulk-save (can save attendance)
- [ ] POST /attendance/bulk-action (can do bulk actions)

### Denied Routes (403 Forbidden)
- [ ] POST /employees (create employee) → 403 ✓
- [ ] PUT /employees/{id} (edit employee) → 403 ✓
- [ ] DELETE /employees/{id} → 403 ✓
- [ ] GET /advances → 403 ✓
- [ ] GET /salaries → 403 ✓
- [ ] GET /payments → 403 ✓
- [ ] GET /reports → 403 ✓
- [ ] GET /users → 403 ✓

## Navigation Menu Tests (Admin User)

Admin user sees all menu items:

- [ ] Dashboard → Visible
- [ ] Daily Hajira → Visible
- [ ] Employees → Visible
- [ ] Advances → Visible
- [ ] Salaries → Visible
- [ ] Payments → Visible
- [ ] Reports → Visible
- [ ] Users → Visible (admin only item)

## Navigation Menu Tests (Accountant User)

Accountant sees specific menu items:

- [ ] Dashboard → Visible
- [ ] Daily Hajira → **NOT Visible** ✓
- [ ] Employees → Visible
- [ ] Advances → Visible
- [ ] Salaries → Visible
- [ ] Payments → Visible
- [ ] Reports → Visible
- [ ] Users → **NOT Visible** ✓

## Navigation Menu Tests (Data Entry User)

Data Entry sees minimal menu items:

- [ ] Dashboard → Visible
- [ ] Daily Hajira → Visible
- [ ] Employees → Visible
- [ ] Advances → **NOT Visible** ✓
- [ ] Salaries → **NOT Visible** ✓
- [ ] Payments → **NOT Visible** ✓
- [ ] Reports → **NOT Visible** ✓
- [ ] Users → **NOT Visible** ✓

## Responsive Navigation Tests

### Mobile Menu (All Users)
- [ ] Menu button toggles on small screen
- [ ] Only permitted menu items visible on mobile
- [ ] Menu closes when item clicked
- [ ] User role displays correctly in mobile menu

### Desktop Navigation (All Users)
- [ ] Menu items display horizontally
- [ ] Only permitted items visible
- [ ] Dropdown menus work (if any)
- [ ] Hover effects visible

## API Permission Tests (Bearer Token)

### Setup
- [ ] Get admin token: `php artisan tinker` → `User::where('email', 'a@a.com')->first()->createToken('test')->plainTextToken`
- [ ] Create accountant user and get token
- [ ] Create data_entry user and get token

### Admin API Tests (All endpoints work)

```bash
curl -H "Authorization: Bearer ADMIN_TOKEN" http://localhost:8000/api/employees
# Should return 200 with employee data

curl -H "Authorization: Bearer ADMIN_TOKEN" http://localhost:8000/api/attendances
# Should return 200
```

- [ ] GET /api/employees → 200 ✓
- [ ] GET /api/attendances → 200 ✓
- [ ] GET /api/advances → 200 ✓
- [ ] GET /api/salaries → 200 ✓
- [ ] GET /api/payments → 200 ✓
- [ ] GET /api/reports/* → 200 ✓

### Accountant API Tests

Accountant should access payroll APIs:

```bash
curl -H "Authorization: Bearer ACCOUNTANT_TOKEN" http://localhost:8000/api/advances
# Should return 200

curl -H "Authorization: Bearer ACCOUNTANT_TOKEN" http://localhost:8000/api/attendances
# Should return 403 (permission denied)
```

- [ ] GET /api/employees → 200 ✓
- [ ] GET /api/advances → 200 ✓
- [ ] GET /api/salaries → 200 ✓
- [ ] GET /api/payments → 200 ✓
- [ ] GET /api/attendances → 403 ✓ (denied)
- [ ] GET /api/reports/* → 200 ✓

### Data Entry API Tests

Data Entry should only access attendance and employee viewing:

- [ ] GET /api/employees → 200 ✓
- [ ] GET /api/attendances → 200 ✓
- [ ] POST /api/attendances/bulk-save → 200 ✓
- [ ] GET /api/advances → 403 ✓ (denied)
- [ ] GET /api/salaries → 403 ✓ (denied)
- [ ] GET /api/payments → 403 ✓ (denied)

## User Management Tests (Admin Only)

### Users List (/users)
- [ ] Admin can access /users
- [ ] User list displays all users
- [ ] Shows user name, email, current role
- [ ] Shows permission count for each user
- [ ] Pagination works (if many users)
- [ ] Mobile layout responsive
- [ ] Desktop table layout shows all columns

### Edit User (/users/{id}/edit)
- [ ] Admin can access edit page
- [ ] User name field editable
- [ ] User email field editable
- [ ] Can update name/email
- [ ] Radio buttons show current role
- [ ] Can select different role
- [ ] Selected role highlighted
- [ ] Current permissions display
- [ ] Role descriptions show (Admin, Accountant, Data Entry)

### Assign Role
- [ ] Admin can assign admin role
- [ ] Admin can assign accountant role
- [ ] Admin can assign data_entry role
- [ ] Role change takes effect immediately
- [ ] User permissions update after role change
- [ ] Success message displays
- [ ] Verify user can only access new role permissions

### Test Role Change Flow
1. Create test user with data_entry role
2. User tries to access /employees → 200 OK (can view)
3. User tries to access /advances → 403 (denied)
4. Admin changes user to accountant role
5. User tries to access /advances → 200 OK (now allowed)
6. User tries to access /attendance → 403 (now denied)

## Permission Verification Tests

### Check Permissions in Tinker

```php
php artisan tinker

# Check admin role has all permissions
$admin_role = Role::where('name', 'admin')->first()
$admin_role->permissions->count()  # Should be 19
$admin_role->permissions  # List all permissions

# Check accountant role permissions
$acc_role = Role::where('name', 'accountant')->first()
$acc_role->permissions->count()  # Should be 12
$acc_role->permissions  # Verify correct permissions

# Check data_entry role permissions
$de_role = Role::where('name', 'data_entry')->first()
$de_role->permissions->count()  # Should be 4

# Check user permissions
$user = User::find(1)
$user->permissions->count()  # Count from role
$user->hasPermissionTo('employees.view')  # Should return true/false
```

- [ ] Admin role has 19 permissions ✓
- [ ] Accountant role has 12 permissions ✓
- [ ] Data Entry role has 4 permissions ✓
- [ ] Test user permissions resolve correctly
- [ ] hasPermissionTo() returns correct boolean

## Cache Tests

- [ ] Routes work after cache:clear
- [ ] Routes work after config:clear
- [ ] Navigation updates after role change
- [ ] Permissions cached properly (fast lookups)
- [ ] Cache invalidates on role assignment

## Edge Cases

### Permission Denied Actions
- [ ] Non-authenticated users → 401/redirect to login ✓
- [ ] User with no role → 403 on protected routes ✓
- [ ] User with wrong role → 403 on protected routes ✓
- [ ] Deleted user → No access ✓
- [ ] Suspended user → Access denied (if implemented) ✓

### Boundary Tests
- [ ] View-only permission doesn't allow create/edit/delete
- [ ] Create permission doesn't allow edit/delete
- [ ] Edit permission doesn't allow delete
- [ ] Bulk actions require appropriate permission
- [ ] API endpoints enforce same permissions as web

## Security Tests

### Cross-User Access
- [ ] Admin cannot see other admin's private data (if applicable)
- [ ] Accountant cannot modify admin settings
- [ ] Data Entry cannot view other user's personal info
- [ ] Users can only access own profile

### Session Security
- [ ] Logout clears permissions
- [ ] Login refreshes permissions
- [ ] Stale tokens rejected (API)
- [ ] Permission changes take effect for new requests

### SQL Injection / XSS
- [ ] Role names safe (no XSS in menu)
- [ ] Permission names display safely
- [ ] User input validated in user management

## Performance Tests

- [ ] Dashboard loads fast (< 500ms)
- [ ] Menu renders quickly with permission checks
- [ ] Permission validation doesn't cause N+1 queries
- [ ] Route cache improves performance
- [ ] API endpoints respond quickly with permission checks

## Documentation Tests

- [ ] ROLE_PERMISSION_IMPLEMENTATION.md accurate
- [ ] Examples in documentation work
- [ ] Permission names match actual routes
- [ ] Test cases cover all scenarios

## Completion Checklist

- [ ] All web routes protected
- [ ] All API routes protected
- [ ] Navigation menu filters by permission
- [ ] User management interface works
- [ ] All 3 roles function correctly
- [ ] All permission tests pass
- [ ] No 404 errors on protected routes (should be 403)
- [ ] Admin can assign roles to users
- [ ] Roles persist after user logout/login
- [ ] Documentation complete and accurate

## Rollback Procedure (If Issues Found)

If problems occur:

1. Backup database: `mysqldump -u user -p database > backup.sql`
2. Remove permissions from routes (revert to original routes)
3. Disable permission checks: Comment out middleware
4. Analyze issue with simpler setup
5. Re-apply changes one feature at a time

## Sign-Off

- **Tester Name**: _______________
- **Date**: _______________
- **Status**: [ ] Pass [ ] Fail [ ] Partial
- **Notes**: _______________________________________________________________

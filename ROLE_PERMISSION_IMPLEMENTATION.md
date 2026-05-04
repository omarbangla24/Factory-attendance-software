# Role-Based Permission System - Implementation Guide

## Overview

Implemented a comprehensive role-based access control (RBAC) system using spatie/laravel-permission. The system includes 3 predefined roles with specific permissions and a user management interface for role assignment.

## Roles & Permissions

### 1. Admin Role
**Full access** to all features and administrative functions.

**Permissions** (All 19 permissions):
- `employees.*` (view, create, edit, delete)
- `attendances.*` (view, create, edit, delete)
- `advances.*` (view, create, edit, delete)
- `salaries.*` (view, generate, lock, regenerate)
- `payments.*` (view, create, delete)
- `reports.view`
- `settings.manage`
- `users.manage`
- `roles.manage`

### 2. Accountant Role
**Financial management** - manages payroll, advances, and payments.

**Permissions** (12 permissions):
- `employees.view`
- `advances.*` (view, create, edit, delete)
- `salaries.*` (view, generate, lock, regenerate)
- `payments.*` (view, create, delete)
- `reports.view`

**Cannot do**: Edit employees, manage users, manage settings

### 3. Data Entry Role
**Limited access** - only manages daily attendance.

**Permissions** (4 permissions):
- `employees.view`
- `attendances.view`
- `attendances.create`
- `attendances.edit`

**Cannot do**: Delete attendance, manage advances, manage salary, etc.

## Architecture

### Database Tables (spatie/laravel-permission)

Created by package migration:
- `permissions` - All available permissions
- `roles` - All defined roles
- `model_has_permissions` - Direct permission assignment
- `model_has_roles` - User-to-role mapping
- `role_has_permissions` - Role-to-permission mapping

### User Model Enhancement (app/Models/User.php)

Added `HasRoles` trait from spatie/laravel-permission:
```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable {
    use HasRoles;
}
```

This enables:
- `$user->assignRole('admin')`
- `$user->hasRole('admin')`
- `$user->hasPermissionTo('employees.view')`
- `$user->syncRoles(['accountant'])`

### Seeder (database/seeders/RoleAndPermissionSeeder.php)

Automatically creates:
1. All 19 permissions
2. 3 predefined roles with correct permission mapping
3. Assigns 'admin' role to test user (a@a.com)

**Run seeder:**
```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```

## Web Routes Protection

All routes protected with `@can` middleware based on permission:

### Example Protection Pattern
```php
Route::middleware('permission:employees.view')->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::middleware('permission:employees.create')->group(function () {
        Route::post('/employees', [EmployeeController::class, 'store']);
    });
});
```

### Protected Route Groups

1. **Dashboard**: Accessible to all authenticated users
2. **Attendance**: Requires `attendances.view`
3. **Employees**: Requires `employees.view`
4. **Advances**: Requires `advances.view`
5. **Salaries**: Requires `salaries.view`
6. **Payments**: Requires `payments.view`
7. **Reports**: Requires `reports.view`
8. **Users**: Requires `users.manage` (admin only)

## API Routes Protection

Same permission-based protection applied to API routes:

```php
Route::middleware('permission:employees.view')->group(function () {
    Route::get('/employees', [ApiEmployeeController::class, 'index']);
});
```

**All API routes protected by**:
1. `auth:sanctum` - Bearer token authentication
2. `permission:*` - Specific permission check

## Navigation Menu - Permission-Based Display

Updated `resources/views/layouts/navigation.blade.php` to show/hide menu items:

```php
@can('employees.view')
    <x-nav-link href="/employees">Employees</x-nav-link>
@endcan

@can('users.manage')
    <x-nav-link href="/users">Users</x-nav-link>
@endcan
```

**Menu items shown based on permission**:
- Dashboard - All users
- Daily Hajira - `attendances.view`
- Employees - `employees.view`
- Advances - `advances.view`
- Salaries - `salaries.view`
- Payments - `payments.view`
- Reports - `reports.view`
- Users - `users.manage` (Admin only)

## User Management Interface

### UserController (app/Http/Controllers/UserController.php)

**Methods**:

1. `index()` - List all users with their roles
   - Paginated (15 per page)
   - Shows role badges and permission count
   - Mobile-responsive

2. `edit($user)` - Edit user details and assign role
   - Edit name and email
   - Display all available roles with descriptions
   - Show current permissions

3. `update($request, $user)` - Update user information
   - Validate name and email
   - Update user in database

4. `assignRole($request, $user)` - Assign role to user
   - Validate role exists
   - Replace all existing roles with new role
   - Display success message

### User Views

#### users/index.blade.php
- Table view showing all users (desktop)
- Card view on mobile
- Shows name, email, role, permission count
- Edit button for each user
- Pagination

#### users/edit.blade.php
- Edit user name and email
- Role assignment with radio buttons
- Role descriptions for each option
- Display current permissions
- Show permission count

## Security Features

### Permission Validation
- All routes validate permission before access
- Non-permitted requests abort with 403 Forbidden
- API returns 403 for permission denied

### Role Assignment Security
- Only admin users can access user management
- Roles validated before assignment
- User cannot assign themselves roles

### Permission Caching
- spatie/laravel-permission caches permissions
- Clear cache on seeding: `app()['cache']->forget('spatie.permission.cache')`
- Cache cleared on role/permission updates

## Testing Access Control

### Test Matrix

| Route | Anonymous | Data Entry | Accountant | Admin |
|-------|-----------|-----------|-----------|-------|
| /employees | ✗ | ✓ view | ✓ all | ✓ all |
| /attendance | ✗ | ✓ all | ✗ | ✓ all |
| /advances | ✗ | ✗ | ✓ all | ✓ all |
| /salaries | ✗ | ✗ | ✓ all | ✓ all |
| /payments | ✗ | ✗ | ✓ all | ✓ all |
| /reports | ✗ | ✗ | ✓ | ✓ |
| /users | ✗ | ✗ | ✗ | ✓ |

### Test as Data Entry User
```bash
# Login as data_entry user
# Try to access /advances → 403 Forbidden
# Try to access /attendance → Works ✓
# Menu shows only: Dashboard, Employees, Daily Hajira
```

### Test as Accountant User
```bash
# Login as accountant user
# Access /advances, /salaries, /payments → Works ✓
# Access /users → 403 Forbidden
# Menu shows: Dashboard, Employees, Advances, Salaries, Payments, Reports
```

### Test as Admin User
```bash
# Login as admin user
# Access all routes → Works ✓
# Menu shows all items including Users
# Can assign roles to other users
```

## API Permission Testing

### Test with Bearer Token

```bash
# Get bearer token
php artisan tinker
$user = User::find(1)
$token = $user->createToken('test')->plainTextToken

# Test permission-denied request
curl -H "Authorization: Bearer $token" \
  https://example.com/api/users
# Returns 403 if user lacks permission

# Test allowed request
curl -H "Authorization: Bearer $token" \
  https://example.com/api/employees
# Returns data if user has employees.view permission
```

## Migration Guide

If upgrading existing system:

1. **Backup database** (recommended)

2. **Run migrations**:
   ```bash
   php artisan migrate
   ```
   Creates spatie permission tables

3. **Seed roles and permissions**:
   ```bash
   php artisan db:seed --class=RoleAndPermissionSeeder
   ```
   Creates 3 roles and 19 permissions

4. **Assign admin role to existing admins**:
   ```php
   $admin = User::find(1);
   $admin->assignRole('admin');
   ```

5. **Clear caches**:
   ```bash
   php artisan config:clear
   php artisan route:clear
   ```

## Files Created/Modified

### Created
- `database/seeders/RoleAndPermissionSeeder.php` - Roles & permissions seeder
- `app/Http/Middleware/CheckPermission.php` - Permission middleware (optional)
- `app/Http/Controllers/UserController.php` - User management controller
- `resources/views/users/index.blade.php` - Users list view
- `resources/views/users/edit.blade.php` - User edit view

### Modified
- `app/Models/User.php` - Added HasRoles trait
- `routes/web.php` - Added permission middleware to routes
- `routes/api.php` - Added permission middleware to API routes
- `resources/views/layouts/navigation.blade.php` - Added @can directives

## Usage Examples

### Check Permission in Code
```php
// In controller
if (auth()->user()->hasPermissionTo('employees.create')) {
    // Allow create operation
}

// In Blade view
@can('employees.create')
    <button>Create Employee</button>
@endcan

// Deny access
@cannot('employees.delete')
    This user cannot delete employees
@endcannot
```

### Assign/Remove Roles
```php
// Assign role
$user->assignRole('accountant');

// Remove role
$user->removeRole('accountant');

// Sync roles (replace all)
$user->syncRoles(['data_entry']);

// Check role
$user->hasRole('admin');
```

### Query Permissions
```php
// Get all user permissions
$user->permissions;

// Get all user roles
$user->roles;

// Check multiple permissions
$user->hasAnyPermission(['employees.view', 'advances.view']);
```

## Performance Considerations

- **Caching**: spatie/laravel-permission caches all permissions
- **Queries**: Permission checks use cached data (very fast)
- **Eager Loading**: Use `with('roles', 'permissions')` when listing users
- **Route Caching**: Clear route cache after modifying permissions: `php artisan route:clear`

## Best Practices

1. **Always require auth middleware first**
   ```php
   Route::middleware(['auth', 'permission:employees.view'])
   ```

2. **Use role instead of permission for high-level checks**
   ```php
   // Good - checking high level
   if ($user->hasRole('admin'))
   
   // Less ideal - checking granular
   if ($user->hasPermissionTo('employees.create') && 
       $user->hasPermissionTo('employees.edit') && ...)
   ```

3. **Assign roles, not individual permissions**
   ```php
   // Good
   $user->assignRole('accountant');
   
   // Avoid - hard to maintain
   $user->givePermissionTo('employees.view');
   $user->givePermissionTo('advances.view');
   ```

4. **Cache invalidation**
   ```php
   // Automatically handled by spatie for:
   // - assignRole(), removeRole(), syncRoles()
   // - givePermissionTo(), revokePermissionTo()
   ```

## Troubleshooting

### User can't access route (403 error)
1. Check user has correct role: `$user->roles`
2. Check role has permission: `$role->permissions`
3. Verify permission name matches route protection
4. Clear cache: `php artisan cache:clear`

### Navigation items not showing
1. Verify user has permission: `$user->hasPermissionTo('employees.view')`
2. Check @can directive syntax
3. Clear cache and refresh page

### API endpoint returns 403
1. Verify bearer token is valid
2. Check user's permissions: `$user->permissions`
3. Verify API route has permission middleware
4. Test with `tinker`: `$user->hasPermissionTo('permission-name')`

## Default Setup

**Default test user** (`a@a.com` / `11111111`):
- Role: Admin
- Permissions: All (19)
- Can access: Everything
- Can manage: Users, roles, settings

## Next Steps

1. Test all three roles with different users
2. Verify menu items show/hide correctly
3. Test API endpoints with bearer tokens
4. Verify 403 errors on unauthorized access
5. Create additional roles as needed
6. Customize permissions for your workflow

## Support

For issues:
1. Check spatie/laravel-permission docs: https://spatie.be/docs/laravel-permission
2. Verify permissions seeded: `php artisan tinker` → `Role::all()`
3. Clear all caches: `php artisan cache:clear && php artisan config:clear`
4. Check Laravel logs: `storage/logs/laravel.log`

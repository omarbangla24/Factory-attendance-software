# Employee Module - Web & API Implementation

## Overview

Complete Employee module with web interface and REST API for attendance tracking and salary management.

---

## Web Routes

### Employee CRUD Routes
```
GET    /employees              → employees.index       (List all employees)
GET    /employees/create       → employees.create      (Show create form)
POST   /employees              → employees.store       (Store new employee)
GET    /employees/{id}/edit    → employees.edit        (Show edit form)
PUT    /employees/{id}         → employees.update      (Update employee)
DELETE /employees/{id}         → employees.destroy     (Delete employee)
```

### Bulk Operations
```
POST   /employees/bulk-update-rates   → employees.bulk-update-rates
POST   /employees/bulk-update-status  → employees.bulk-update-status
```

---

## API Routes

All API routes require `auth:sanctum` middleware.

### Standard CRUD
```
GET    /api/employees                 (List with filters)
POST   /api/employees                 (Create)
GET    /api/employees/{id}            (Get single)
PUT    /api/employees/{id}            (Update)
DELETE /api/employees/{id}            (Delete)
```

### Special Endpoints
```
GET    /api/employees/active          (Get active employees only)
POST   /api/employees/bulk-update-rates   (Bulk update rates)
POST   /api/employees/bulk-update-status  (Bulk update status)
```

---

## Web Features

### List View
- **Search**: By name or phone
- **Filter**: By status (active/inactive) and department
- **Desktop View**: Full table with all details
- **Mobile View**: Card-based responsive layout
- **Bulk Actions**: Select multiple employees and:
  - Update hajira rate
  - Update overtime rate
  - Update status
- **Pagination**: 10 employees per page
- **Delete Protection**: Can't delete if has attendance/salary records

### Form Validation
- **Name**: Required, max 255 characters
- **Phone**: Optional, unique
- **Address**: Optional, text
- **Joining Date**: Required, date format
- **Department**: Optional, string
- **Hajira Rate**: Required, numeric ≥ 0
- **Overtime Rate**: Required, numeric ≥ 0
- **Status**: Required, active or inactive

### Create/Edit Forms
- Clean, mobile-friendly design
- Inline validation errors
- Pre-populated values on edit
- Clear call-to-action buttons

---

## API Features

### Query Parameters

#### List Endpoint (`GET /api/employees`)
```
?search=name_or_phone      (Search by name or phone)
?status=active|inactive    (Filter by status)
?department=IT             (Filter by department)
?per_page=15              (Items per page, default: 15)
?page=1                   (Page number)
```

#### Active Endpoint (`GET /api/employees/active`)
```
?per_page=15              (Items per page, default: 15)
?page=1                   (Page number)
```

### Request/Response Format

#### Create Employee (POST /api/employees)
**Request:**
```json
{
  "name": "Ahmed Hassan",
  "phone": "01700000001",
  "address": "Dhaka, Bangladesh",
  "joining_date": "2026-01-15",
  "department": "IT",
  "hajira_rate": 500,
  "overtime_rate": 150,
  "status": "active"
}
```

**Response (201):**
```json
{
  "message": "Employee created successfully",
  "data": {
    "id": 1,
    "name": "Ahmed Hassan",
    "phone": "01700000001",
    "address": "Dhaka, Bangladesh",
    "joining_date": "2026-01-15",
    "department": "IT",
    "hajira_rate": 500.00,
    "overtime_rate": 150.00,
    "status": "active",
    "created_at": "2026-05-03T18:00:00.000000Z",
    "updated_at": "2026-05-03T18:00:00.000000Z"
  }
}
```

#### List Employees (GET /api/employees)
**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "Ahmed Hassan",
      "phone": "01700000001",
      "address": "Dhaka, Bangladesh",
      "joining_date": "2026-01-15",
      "department": "IT",
      "hajira_rate": 500.00,
      "overtime_rate": 150.00,
      "status": "active",
      "created_at": "2026-05-03T18:00:00.000000Z",
      "updated_at": "2026-05-03T18:00:00.000000Z"
    }
  ],
  "first_page_url": "http://localhost:8000/api/employees?page=1",
  "from": 1,
  "last_page": 1,
  "last_page_url": "http://localhost:8000/api/employees?page=1",
  "links": [...],
  "next_page_url": null,
  "path": "http://localhost:8000/api/employees",
  "per_page": 15,
  "prev_page_url": null,
  "to": 5,
  "total": 5
}
```

#### Get Single Employee (GET /api/employees/{id})
**Response (200):**
```json
{
  "id": 1,
  "name": "Ahmed Hassan",
  "phone": "01700000001",
  "address": "Dhaka, Bangladesh",
  "joining_date": "2026-01-15",
  "department": "IT",
  "hajira_rate": 500.00,
  "overtime_rate": 150.00,
  "status": "active",
  "created_at": "2026-05-03T18:00:00.000000Z",
  "updated_at": "2026-05-03T18:00:00.000000Z"
}
```

#### Update Employee (PUT /api/employees/{id})
**Request:** (Any combination of fields)
```json
{
  "hajira_rate": 550,
  "status": "inactive"
}
```

**Response (200):**
```json
{
  "message": "Employee updated successfully",
  "data": {
    "id": 1,
    "name": "Ahmed Hassan",
    "phone": "01700000001",
    "address": "Dhaka, Bangladesh",
    "joining_date": "2026-01-15",
    "department": "IT",
    "hajira_rate": 550.00,
    "overtime_rate": 150.00,
    "status": "inactive",
    "created_at": "2026-05-03T18:00:00.000000Z",
    "updated_at": "2026-05-03T18:01:00.000000Z"
  }
}
```

#### Delete Employee (DELETE /api/employees/{id})
**Response (200):**
```json
{
  "message": "Employee deleted successfully"
}
```

**Response (422 - Has Attendance):**
```json
{
  "message": "Cannot delete employee with attendance records"
}
```

**Response (422 - Has Salary):**
```json
{
  "message": "Cannot delete employee with salary records"
}
```

#### Bulk Update Rates (POST /api/employees/bulk-update-rates)
**Request:**
```json
{
  "employee_ids": [1, 2, 3],
  "hajira_rate": 600,
  "overtime_rate": 200
}
```

**Response (200):**
```json
{
  "message": "Employee rates updated successfully",
  "updated_count": 3
}
```

#### Bulk Update Status (POST /api/employees/bulk-update-status)
**Request:**
```json
{
  "employee_ids": [1, 2, 3],
  "status": "inactive"
}
```

**Response (200):**
```json
{
  "message": "Employee status updated successfully",
  "updated_count": 3
}
```

#### Get Active Employees (GET /api/employees/active)
**Response:** Same format as list endpoint, but only active employees

---

## Manual Test Checklist

### Web Module Tests

#### Search & Filter
- [ ] Search by employee name
- [ ] Search by phone number
- [ ] Filter by Active status
- [ ] Filter by Inactive status
- [ ] Filter by Department
- [ ] Combine multiple filters
- [ ] Clear filters button works

#### CRUD Operations
- [ ] Create employee with all fields
- [ ] Create employee with minimal fields (only required)
- [ ] View employee list with pagination
- [ ] Edit employee details
- [ ] Update only some fields
- [ ] Delete employee successfully
- [ ] Cannot delete employee with attendance (error shown)
- [ ] Cannot delete employee with salary (error shown)

#### Validation
- [ ] Name field is required
- [ ] Hajira rate must be numeric
- [ ] Overtime rate must be numeric
- [ ] Phone must be unique
- [ ] Status must be selected
- [ ] Joining date must be valid date
- [ ] Error messages display inline

#### Bulk Operations
- [ ] Select multiple employees
- [ ] "Select All" checkbox works
- [ ] Bulk controls appear when selected
- [ ] Update hajira rate for multiple employees
- [ ] Update overtime rate for multiple employees
- [ ] Update status for multiple employees
- [ ] Bulk action counts display correctly

#### Mobile Responsiveness
- [ ] Card view displays on mobile (<768px)
- [ ] Desktop table hides on mobile
- [ ] Cards show all key information
- [ ] Edit/Delete buttons accessible on mobile
- [ ] Search/filter works on mobile
- [ ] Pagination works on mobile

---

### API Module Tests

#### Authentication
- [ ] Unauthenticated request returns 401
- [ ] Request with valid Sanctum token succeeds
- [ ] Request with invalid token returns 401

#### List Employees
- [ ] GET /api/employees returns all employees
- [ ] Pagination works (per_page parameter)
- [ ] Search parameter filters by name
- [ ] Search parameter filters by phone
- [ ] Status filter works (active/inactive)
- [ ] Department filter works
- [ ] Returns correct JSON structure
- [ ] Returns correct field types

#### Create Employee
- [ ] POST /api/employees creates employee
- [ ] All required fields validated
- [ ] Phone uniqueness enforced
- [ ] Returns 201 status
- [ ] Returns created employee data

#### Get Single Employee
- [ ] GET /api/employees/{id} returns employee
- [ ] Returns 404 for non-existent employee
- [ ] Returns correct employee data

#### Update Employee
- [ ] PUT /api/employees/{id} updates employee
- [ ] Partial updates work (some fields only)
- [ ] Phone uniqueness validated on update
- [ ] Returns updated employee data
- [ ] Returns 404 for non-existent employee

#### Delete Employee
- [ ] DELETE /api/employees/{id} deletes employee
- [ ] Cannot delete with attendance (422 error)
- [ ] Cannot delete with salary (422 error)
- [ ] Returns 404 for non-existent employee

#### Active Endpoint
- [ ] GET /api/employees/active returns only active
- [ ] Inactive employees not included
- [ ] Pagination works
- [ ] Correct count returned

#### Bulk Update Rates
- [ ] POST /api/employees/bulk-update-rates updates multiple
- [ ] Hajira rate updates correctly
- [ ] Overtime rate updates correctly
- [ ] Both rates can be updated together
- [ ] Returns updated count
- [ ] At least one rate required (422 if neither)

#### Bulk Update Status
- [ ] POST /api/employees/bulk-update-status updates multiple
- [ ] Status updates correctly for all selected
- [ ] Status field required (422 if missing)
- [ ] Returns updated count

---

## curl Commands for API Testing

```bash
# Get authorization token (replace with your token)
TOKEN="your-sanctum-token-here"

# 1. List all employees
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/employees

# 2. List with search
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/employees?search=Ahmed"

# 3. List active employees only
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/employees?status=active"

# 4. Create employee
curl -X POST -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Employee",
    "joining_date": "2026-05-03",
    "hajira_rate": 500,
    "overtime_rate": 150,
    "status": "active"
  }' \
  http://localhost:8000/api/employees

# 5. Get single employee
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/employees/1

# 6. Update employee
curl -X PUT -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "hajira_rate": 550,
    "status": "inactive"
  }' \
  http://localhost:8000/api/employees/1

# 7. Delete employee
curl -X DELETE -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/employees/1

# 8. Get active employees
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/employees/active

# 9. Bulk update rates
curl -X POST -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_ids": [1, 2, 3],
    "hajira_rate": 600,
    "overtime_rate": 200
  }' \
  http://localhost:8000/api/employees/bulk-update-rates

# 10. Bulk update status
curl -X POST -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_ids": [1, 2, 3],
    "status": "inactive"
  }' \
  http://localhost:8000/api/employees/bulk-update-status
```

---

## Files Created/Updated

### Controllers
- `app/Http/Controllers/EmployeeController.php` - Web controller with search, filter, bulk operations
- `app/Http/Controllers/Api/EmployeeController.php` - API controller with full REST endpoints

### Views
- `resources/views/employees/index.blade.php` - List with search/filter/bulk actions (mobile-responsive)
- `resources/views/employees/create.blade.php` - Create form
- `resources/views/employees/edit.blade.php` - Edit form

### Routes
- `routes/web.php` - Updated with bulk action routes
- `routes/api.php` - Updated with all employee API endpoints

---

## Key Features Summary

✅ **Web Module**
- Full-text search by name and phone
- Filter by status and department
- Mobile-first card view
- Desktop table view
- Bulk update rates (hajira and overtime)
- Bulk update status (active/inactive)
- Delete protection (checks for attendance/salary)
- Form validation with inline errors
- Pagination (10 per page)

✅ **API Module**
- Full REST API (CRUD + custom endpoints)
- Pagination support
- Search and filter parameters
- Bulk operations
- Delete protection
- Input validation
- JSON responses
- Proper HTTP status codes

✅ **Validation**
- Name required
- Rates numeric and ≥ 0
- Phone unique (optional)
- Status required (active/inactive)
- Joining date required
- Date format validation

✅ **Security**
- Sanctum authentication for API
- Auth middleware on all web routes
- Input validation on all endpoints
- Delete protection for data integrity

---

## Next Steps

1. Test all endpoints manually using curl or Postman
2. Test mobile responsiveness on actual device
3. Test bulk operations with multiple employees
4. Implement attendance tracking module
5. Implement salary calculation module
6. Add API token generation for mobile app
7. Create API documentation (Swagger/OpenAPI)

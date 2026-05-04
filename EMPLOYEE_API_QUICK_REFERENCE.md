# Employee API - Quick Reference

## Base URL
```
http://localhost:8000/api
```

## Authentication
All endpoints require `Authorization: Bearer TOKEN` header with valid Sanctum token.

---

## Endpoints

### List Employees
```
GET /employees
```
**Query Parameters:**
- `search=name_or_phone` - Search by name or phone
- `status=active|inactive` - Filter by status
- `department=IT` - Filter by department
- `per_page=15` - Items per page (default: 15)
- `page=1` - Page number

**Example:**
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/employees?search=Ahmed&status=active&per_page=20"
```

---

### Create Employee
```
POST /employees
Content-Type: application/json
```

**Required Fields:**
```json
{
  "name": "Employee Name",
  "joining_date": "2026-05-03",
  "hajira_rate": 500,
  "overtime_rate": 150,
  "status": "active"
}
```

**Optional Fields:**
```json
{
  "phone": "01700000001",
  "address": "Address here",
  "department": "IT"
}
```

**Response (201):**
```json
{
  "message": "Employee created successfully",
  "data": {...}
}
```

---

### Get Single Employee
```
GET /employees/{id}
```

**Example:**
```bash
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/employees/1
```

**Response (200):**
```json
{
  "id": 1,
  "name": "Ahmed Hassan",
  "phone": "01700000001",
  "address": "Dhaka",
  "joining_date": "2026-01-15",
  "department": "IT",
  "hajira_rate": "500.00",
  "overtime_rate": "150.00",
  "status": "active",
  "created_at": "2026-05-03T16:00:00Z",
  "updated_at": "2026-05-03T16:00:00Z"
}
```

---

### Update Employee
```
PUT /employees/{id}
Content-Type: application/json
```

**Request (any combination of fields):**
```json
{
  "hajira_rate": 600,
  "status": "inactive"
}
```

**Response (200):**
```json
{
  "message": "Employee updated successfully",
  "data": {...}
}
```

---

### Delete Employee
```
DELETE /employees/{id}
```

**Success (200):**
```json
{
  "message": "Employee deleted successfully"
}
```

**Error - Has Attendance (422):**
```json
{
  "message": "Cannot delete employee with attendance records"
}
```

**Error - Has Salary (422):**
```json
{
  "message": "Cannot delete employee with salary records"
}
```

---

### Get Active Employees
```
GET /employees/active
```

**Query Parameters:**
- `per_page=15` - Items per page
- `page=1` - Page number

**Example:**
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/employees/active?per_page=50"
```

---

### Bulk Update Rates
```
POST /employees/bulk-update-rates
Content-Type: application/json
```

**Request:**
```json
{
  "employee_ids": [1, 2, 3],
  "hajira_rate": 600,
  "overtime_rate": 200
}
```

**Notes:**
- Both rates optional, but at least one required
- Only specified rates are updated

**Response (200):**
```json
{
  "message": "Employee rates updated successfully",
  "updated_count": 3
}
```

---

### Bulk Update Status
```
POST /employees/bulk-update-status
Content-Type: application/json
```

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

---

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Success |
| 201 | Created - New resource created |
| 400 | Bad Request - Invalid input |
| 401 | Unauthorized - Missing/invalid token |
| 404 | Not Found - Resource doesn't exist |
| 422 | Unprocessable - Validation error or business logic error |
| 500 | Server Error |

---

## Error Response Format

```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

---

## Common Validation Errors

**Missing required field:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

**Duplicate phone:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "phone": ["The phone has already been taken."]
  }
}
```

**Invalid rate:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "hajira_rate": ["The hajira rate must be a number."]
  }
}
```

---

## curl Examples

```bash
TOKEN="your-sanctum-token-here"

# 1. List all employees
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/employees

# 2. Search employees
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/employees?search=Ahmed&status=active"

# 3. Create employee
curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "joining_date": "2026-05-03",
    "hajira_rate": 500,
    "overtime_rate": 150,
    "status": "active"
  }' \
  http://localhost:8000/api/employees

# 4. Get single employee
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/employees/1

# 5. Update employee
curl -X PUT \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"hajira_rate": 600}' \
  http://localhost:8000/api/employees/1

# 6. Delete employee
curl -X DELETE \
  -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/employees/1

# 7. Get active employees
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/employees/active

# 8. Bulk update rates
curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_ids": [1, 2, 3],
    "hajira_rate": 600,
    "overtime_rate": 200
  }' \
  http://localhost:8000/api/employees/bulk-update-rates

# 9. Bulk update status
curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_ids": [1, 2, 3],
    "status": "inactive"
  }' \
  http://localhost:8000/api/employees/bulk-update-status
```

---

## Validation Rules

| Field | Rules |
|-------|-------|
| name | required, string, max:255 |
| phone | unique (optional), string |
| address | optional, string |
| joining_date | required, date |
| department | optional, string |
| hajira_rate | required, numeric, min:0 |
| overtime_rate | required, numeric, min:0 |
| status | required, in:active,inactive |

---

## Response Time

Typical response times:
- List employees: 50-100ms
- Get single: 20-50ms
- Create: 50-150ms
- Update: 30-100ms
- Delete: 30-50ms
- Bulk operations: 100-300ms (depends on record count)

---

## Rate Limiting

Not currently implemented. Can be added using Laravel middleware if needed.

---

## Pagination Info

Default response includes:
```json
{
  "current_page": 1,
  "data": [...],
  "first_page_url": "...",
  "from": 1,
  "last_page": 5,
  "last_page_url": "...",
  "links": [...],
  "next_page_url": "...",
  "path": "...",
  "per_page": 15,
  "prev_page_url": null,
  "to": 15,
  "total": 75
}
```

---

## Tips

1. Always include `Authorization: Bearer TOKEN` header
2. Use `Content-Type: application/json` for POST/PUT requests
3. Check status code first, then parse response
4. Handle 422 errors - they contain field-specific validation messages
5. For bulk operations, ensure array of IDs is valid
6. Can combine search, status, and department filters
7. Pagination applies to list and active endpoints
8. Phone field is optional but must be unique if provided
9. Bulk operations require at least one employee_id
10. Rates must be >= 0 (no negative rates)

---

## Testing with Postman

1. Create new collection "Hajira Payroll"
2. Add Authorization tab: Type: "Bearer Token", Token: `your-token-here`
3. Create requests for each endpoint
4. Set base URL variable: `{{base_url}}` = `http://localhost:8000/api`
5. Test all operations

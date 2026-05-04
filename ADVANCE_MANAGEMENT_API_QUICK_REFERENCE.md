# Advance Management API Quick Reference

## Base URL
```
http://localhost:8000/api
```

## Authentication
All endpoints require Bearer token from Laravel Sanctum:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

## Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/advances` | List all advances |
| POST | `/advances` | Create new advance |
| GET | `/advances/{id}` | Get advance details |
| PUT | `/advances/{id}` | Update advance |
| DELETE | `/advances/{id}` | Delete advance |
| GET | `/employees/{id}/advance-balance` | Get employee balance |

---

## 1. List Advances

```http
GET /advances
```

### Query Parameters
- `search` (string) - Search by employee name
- `start_date` (date YYYY-MM-DD) - Filter from date
- `end_date` (date YYYY-MM-DD) - Filter to date
- `per_page` (integer) - Items per page (default 15)

### Example Request
```bash
curl -X GET "http://localhost:8000/api/advances?search=Ahmed&per_page=20" \
  -H "Authorization: Bearer your_token"
```

### Success Response (200)
```json
{
  "data": [
    {
      "id": 1,
      "employee_id": 1,
      "date": "2024-05-01",
      "amount": "5000.00",
      "reason": "Emergency",
      "note": null,
      "created_by": 1,
      "created_at": "2024-05-03T10:30:00Z",
      "updated_at": "2024-05-03T10:30:00Z",
      "employee": {
        "id": 1,
        "name": "Ahmed Khan",
        "department": "Sales"
      },
      "deductions": [
        {
          "id": 1,
          "advance_id": 1,
          "amount": "2000.00"
        }
      ]
    }
  ],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "per_page": 15,
    "to": 5,
    "total": 5
  }
}
```

---

## 2. Create Advance

```http
POST /advances
```

### Request Body
```json
{
  "employee_id": 1,
  "date": "2024-05-03",
  "amount": 5000,
  "reason": "Emergency",
  "note": "Medical emergency"
}
```

### Field Validation
- `employee_id` (required) - Must exist in employees table, active status
- `date` (required) - Valid date format YYYY-MM-DD
- `amount` (required) - Numeric, must be > 0
- `reason` (optional) - String, max 255 characters
- `note` (optional) - String, max 255 characters

### Example Request
```bash
curl -X POST "http://localhost:8000/api/advances" \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": 1,
    "date": "2024-05-03",
    "amount": 5000,
    "reason": "Emergency"
  }'
```

### Success Response (201)
```json
{
  "message": "Advance created successfully",
  "data": {
    "id": 5,
    "employee_id": 1,
    "date": "2024-05-03",
    "amount": "5000.00",
    "reason": "Emergency",
    "note": null,
    "created_by": 1,
    "employee": {
      "id": 1,
      "name": "Ahmed Khan"
    }
  }
}
```

### Validation Error Response (422)
```json
{
  "message": "The given data was invalid",
  "errors": {
    "amount": ["The amount must be greater than 0"]
  }
}
```

---

## 3. Get Advance Details

```http
GET /advances/{id}
```

### Example Request
```bash
curl -X GET "http://localhost:8000/api/advances/1" \
  -H "Authorization: Bearer your_token"
```

### Success Response (200)
```json
{
  "data": {
    "id": 1,
    "employee_id": 1,
    "date": "2024-05-01",
    "amount": "5000.00",
    "reason": "Emergency",
    "note": null,
    "created_by": 1,
    "employee": {...},
    "deductions": [...]
  },
  "deducted_amount": 2000,
  "remaining_amount": 3000
}
```

### Error Response (404)
```json
{
  "message": "Not found"
}
```

---

## 4. Update Advance

```http
PUT /advances/{id}
```

### Request Body
```json
{
  "employee_id": 1,
  "date": "2024-05-05",
  "amount": 5500,
  "reason": "Updated reason"
}
```

### Field Validation
- `employee_id` (sometimes) - Must exist in employees table
- `date` (sometimes) - Valid date format
- `amount` (sometimes) - Numeric, must be > 0
- `reason` (optional) - String, max 255 characters
- `note` (optional) - String, max 255 characters

### Example Request
```bash
curl -X PUT "http://localhost:8000/api/advances/1" \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 5500
  }'
```

### Success Response (200)
```json
{
  "message": "Advance updated successfully",
  "data": {...}
}
```

### Protection Error Response (403)
```json
{
  "message": "Cannot update advance that has been deducted"
}
```

---

## 5. Delete Advance

```http
DELETE /advances/{id}
```

### Example Request
```bash
curl -X DELETE "http://localhost:8000/api/advances/1" \
  -H "Authorization: Bearer your_token"
```

### Success Response (200)
```json
{
  "message": "Advance deleted successfully"
}
```

### Protection Error Response (403)
```json
{
  "message": "Cannot delete advance that has been deducted"
}
```

---

## 6. Get Employee Advance Balance

```http
GET /employees/{id}/advance-balance
```

### Example Request
```bash
curl -X GET "http://localhost:8000/api/employees/1/advance-balance" \
  -H "Authorization: Bearer your_token"
```

### Success Response (200)
```json
{
  "employee_id": 1,
  "employee_name": "Ahmed Khan",
  "total_advance": 15000,
  "total_deducted": 5000,
  "remaining_balance": 10000
}
```

### Balance Calculation
- **total_advance** = Sum of all advances for employee
- **total_deducted** = Sum of all advance deductions for employee
- **remaining_balance** = total_advance - total_deducted

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success (GET, PUT, DELETE) |
| 201 | Created (POST) |
| 400 | Bad request (missing date param) |
| 403 | Forbidden (cannot edit/delete if deducted) |
| 404 | Not found |
| 422 | Validation error |
| 500 | Server error |

---

## Common Workflows

### Workflow 1: Create Advance for Employee

```bash
# Get employee balance first
curl -X GET "http://localhost:8000/api/employees/1/advance-balance" \
  -H "Authorization: Bearer TOKEN"

# Create advance
curl -X POST "http://localhost:8000/api/advances" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": 1,
    "date": "2024-05-03",
    "amount": 5000,
    "reason": "Salary advance"
  }'

# Get updated balance
curl -X GET "http://localhost:8000/api/employees/1/advance-balance" \
  -H "Authorization: Bearer TOKEN"
```

### Workflow 2: Search and Filter Advances

```bash
# Search by employee name for date range
curl -X GET "http://localhost:8000/api/advances?search=Ahmed&start_date=2024-05-01&end_date=2024-05-31" \
  -H "Authorization: Bearer TOKEN"
```

### Workflow 3: Get Advance with Deduction Details

```bash
curl -X GET "http://localhost:8000/api/advances/1" \
  -H "Authorization: Bearer TOKEN"

# Response includes:
# - data: full advance details with relationships
# - deducted_amount: total deducted so far
# - remaining_amount: balance remaining
```

### Workflow 4: Update Advance (only if no deductions)

```bash
# Check if editable
curl -X GET "http://localhost:8000/api/advances/1" \
  -H "Authorization: Bearer TOKEN"

# If deductions > 0, update will fail with 403
# If deductions = 0, update succeeds

curl -X PUT "http://localhost:8000/api/advances/1" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount": 5500}'
```

---

## Error Handling Examples

### Validation Error
```json
{
  "message": "The given data was invalid",
  "errors": {
    "amount": ["The amount must be greater than 0"],
    "employee_id": ["The selected employee id is invalid."]
  }
}
```

### Protection Error
```json
{
  "message": "Cannot update advance that has been deducted"
}
```

### Not Found
```json
{
  "message": "Not found"
}
```

---

## Request Headers

```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
Accept: application/json
```

---

## Response Format

All responses are JSON:
- **Success**: `{ "message": "...", "data": {...} }`
- **List**: `{ "data": [...], "links": {...}, "meta": {...} }`
- **Error**: `{ "message": "...", "errors": {...} }`

---

## Rate Limiting

None implemented (can add in future).

---

## Pagination

Default 15 items per page. Customize with `per_page` parameter.

Response includes:
```json
{
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 15,
    "last_page": 4
  }
}
```

---

## Testing with Postman/Insomnia

### Setup Collection
1. Create collection: "Advance API"
2. Add variable: `base_url` = `http://localhost:8000`
3. Add variable: `token` = (your Sanctum token)

### Test Requests
```
GET    {{base_url}}/api/advances
POST   {{base_url}}/api/advances
GET    {{base_url}}/api/advances/1
PUT    {{base_url}}/api/advances/1
DELETE {{base_url}}/api/advances/1
GET    {{base_url}}/api/employees/1/advance-balance
```

All requests need header:
```
Authorization: Bearer {{token}}
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 401 Unauthorized | Token missing or expired, regenerate |
| 403 Forbidden | Trying to edit/delete advance with deductions |
| 404 Not Found | Advance ID doesn't exist |
| 422 Validation Error | Check field names and values match spec |
| Cannot update employee_id | Employee must exist and be active |
| Amount validation fails | Must be numeric and > 0 |

---

## Related Documentation

- Full implementation: ADVANCE_MANAGEMENT_MODULE.md
- Employee API: EMPLOYEE_API_QUICK_REFERENCE.md
- Daily Hajira API: DAILY_HAJIRA_API_QUICK_REFERENCE.md

# Daily Hajira API Quick Reference

## Base URL
```
http://localhost:8000/api
```

## Authentication
All endpoints require Bearer token from Laravel Sanctum:
```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
```

## Endpoints

### 1. List Attendance by Date
```http
GET /attendances?date=YYYY-MM-DD
```

**Parameters:**
- `date` (required): Date in YYYY-MM-DD format

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/attendances?date=2024-05-03" \
  -H "Authorization: Bearer your_token" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "date": "2024-05-03",
  "count": 5,
  "data": [
    {
      "id": 1,
      "employee_id": 1,
      "employee_name": "Ahmed Khan",
      "date": "2024-05-03",
      "hajira_type": "one",
      "hajira_value": 1,
      "overtime_hours": 2.5,
      "note": "Worked late"
    },
    {
      "id": 2,
      "employee_id": 2,
      "employee_name": "Fatima Ali",
      "date": "2024-05-03",
      "hajira_type": "absent",
      "hajira_value": 0,
      "overtime_hours": 0,
      "note": "Medical leave"
    }
  ]
}
```

**Error Response (400):**
```json
{
  "message": "Date parameter required"
}
```

---

### 2. Save/Update Multiple Attendance Records
```http
POST /attendances/bulk-save
```

**Request Body:**
```json
{
  "attendances": [
    {
      "employee_id": 1,
      "date": "2024-05-03",
      "hajira_type": "one",
      "hajira_value": 1,
      "overtime_hours": 2.5,
      "note": "Normal day"
    },
    {
      "employee_id": 2,
      "date": "2024-05-03",
      "hajira_type": "one_half",
      "hajira_value": 1.5,
      "overtime_hours": 0,
      "note": "Half day"
    }
  ]
}
```

**Field Validation:**
- `employee_id`: required, must exist in employees table
- `date`: required, valid date format
- `hajira_type`: required, must be one of: `absent`, `one`, `one_half`
- `hajira_value`: required, must be one of: `0`, `1`, `1.5`
- `overtime_hours`: optional, must be numeric ≥ 0
- `note`: optional, string max 255

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/attendances/bulk-save" \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{
    "attendances": [
      {
        "employee_id": 1,
        "date": "2024-05-03",
        "hajira_type": "one",
        "hajira_value": 1,
        "overtime_hours": 0,
        "note": null
      },
      {
        "employee_id": 2,
        "date": "2024-05-03",
        "hajira_type": "absent",
        "hajira_value": 0,
        "overtime_hours": 0,
        "note": "Leave"
      }
    ]
  }'
```

**Success Response (201):**
```json
{
  "message": "Attendance saved successfully",
  "created": 1,
  "updated": 1,
  "total": 2
}
```

**Validation Error Response (422):**
```json
{
  "message": "The given data was invalid",
  "errors": {
    "attendances.0.employee_id": ["The employee id must exist in the employees table"]
  }
}
```

---

### 3. Get Daily Summary
```http
GET /attendances/summary?date=YYYY-MM-DD
```

**Parameters:**
- `date` (required): Date in YYYY-MM-DD format

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/attendances/summary?date=2024-05-03" \
  -H "Authorization: Bearer your_token" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "date": "2024-05-03",
  "total_employees": 5,
  "total_present": 4,
  "total_absent": 1,
  "total_hajira": 4.5,
  "total_overtime_hours": 8.5,
  "recorded_count": 5
}
```

**Response Fields:**
- `date`: The date requested
- `total_employees`: Total active employees in system
- `total_present`: Count of non-absent records (hajira_type != 'absent')
- `total_absent`: Count of absent records
- `total_hajira`: Sum of all hajira_value for the date
- `total_overtime_hours`: Sum of all overtime_hours for the date
- `recorded_count`: Total records entered for the date

**Error Response (400):**
```json
{
  "message": "Date parameter required"
}
```

---

## Hajira Value Mapping

| Type | Value | Description |
|------|-------|-------------|
| `absent` | `0` | Employee absent/not worked |
| `one` | `1` | Employee worked full day |
| `one_half` | `1.5` | Employee worked 1.5 days (overtime) |

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success (GET/LIST) |
| 201 | Created/Updated (POST) |
| 400 | Missing required parameter |
| 401 | Unauthorized (missing/invalid token) |
| 403 | Forbidden (insufficient permissions) |
| 422 | Validation error |
| 500 | Server error |

---

## Authentication Setup

### Generate Token for Mobile App

**1. Send login request:**
```bash
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "hr@example.com",
    "password": "password"
  }'
```

**2. Response includes token:**
```json
{
  "token": "YOUR_TOKEN_HERE",
  "user": {
    "id": 1,
    "name": "HR Manager",
    "email": "hr@example.com"
  }
}
```

**3. Use token in all subsequent requests:**
```bash
curl -X GET "http://localhost:8000/api/attendances?date=2024-05-03" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Common Workflows

### Mark Attendance for Entire Team (Single Date)

**Step 1: Get team members**
```bash
curl -X GET "http://localhost:8000/api/employees/active" \
  -H "Authorization: Bearer your_token"
```

**Step 2: Prepare attendance data**
```json
{
  "attendances": [
    {"employee_id": 1, "date": "2024-05-03", "hajira_type": "one", "hajira_value": 1, "overtime_hours": 0},
    {"employee_id": 2, "date": "2024-05-03", "hajira_type": "absent", "hajira_value": 0, "overtime_hours": 0},
    {"employee_id": 3, "date": "2024-05-03", "hajira_type": "one_half", "hajira_value": 1.5, "overtime_hours": 2}
  ]
}
```

**Step 3: Save attendance**
```bash
curl -X POST "http://localhost:8000/api/attendances/bulk-save" \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{...}'
```

### Update Existing Records

Same endpoint as create - if record with same (employee_id, date) exists, it's updated:

```bash
# First save
POST /api/attendances/bulk-save
{
  "attendances": [
    {"employee_id": 1, "date": "2024-05-03", "hajira_type": "one", "hajira_value": 1, "overtime_hours": 0}
  ]
}

# Later, update same record
POST /api/attendances/bulk-save
{
  "attendances": [
    {"employee_id": 1, "date": "2024-05-03", "hajira_type": "one_half", "hajira_value": 1.5, "overtime_hours": 3}
  ]
}

# Record is updated, no duplicate created
```

### Get Daily Statistics

```bash
curl -X GET "http://localhost:8000/api/attendances/summary?date=2024-05-03" \
  -H "Authorization: Bearer your_token"
```

---

## Rate Limiting

None implemented yet (Phase 3). Can be added in future.

---

## Error Handling in Mobile App

**Check for errors in response:**
```javascript
// JavaScript example
const response = await fetch('/api/attendances/bulk-save', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({...})
});

if (response.status === 401) {
  // Token expired, refresh token
  refreshToken();
}

if (response.status === 422) {
  // Validation error
  const errors = await response.json();
  console.error('Validation errors:', errors.errors);
}

if (response.ok) {
  const data = await response.json();
  console.log(`Created: ${data.created}, Updated: ${data.updated}`);
}
```

---

## Testing in Postman/Insomnia

### Setup Collection
1. Create new collection: "Hajira API"
2. Add collection variable: `base_url` = `http://localhost:8000`
3. Add collection variable: `token` = (generated from login)

### Test Endpoints
```
GET {{base_url}}/api/attendances?date=2024-05-03
GET {{base_url}}/api/attendances/summary?date=2024-05-03
POST {{base_url}}/api/attendances/bulk-save
```

All requests should include header:
```
Authorization: Bearer {{token}}
```

---

## Performance Tips for Mobile App

1. **Batch Operations**: Send all employee attendance in one request
2. **Cache Employees**: Fetch employee list once and cache locally
3. **Offline Support**: Queue requests when offline, sync when online
4. **Pagination**: Use pagination for large employee lists (if implemented)
5. **Network**: Use gzip compression for requests/responses

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 401 Unauthorized | Token expired or missing, regenerate token |
| 422 Validation Error | Check field names and values match spec |
| No records returned | Check date format (YYYY-MM-DD) and time zone |
| Duplicate records | Unique constraint enforced, use bulk-save for updates |
| Slow response | Use pagination (if implemented) or filter by date range |

---

## Related Documentation

- Employee API: `EMPLOYEE_API_QUICK_REFERENCE.md`
- Database Schema: `DATABASE_STRUCTURE.md`
- Full Implementation: `DAILY_HAJIRA_MODULE.md`

# Salary Generation API - Quick Reference

## Base URL
```
https://your-domain.com/api
```

## Authentication
All endpoints require Sanctum bearer token:
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
```

---

## Endpoints

### 1. List Salaries
**Request:**
```http
GET /api/salaries?month=2026-05&status=draft&employee_id=1
```

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| month | string | Yes | Format: YYYY-MM |
| status | string | No | draft, locked, partial, paid |
| employee_id | integer | No | Filter by specific employee |

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_id": 1,
      "month": "2026-05",
      "total_hajira": "17.50",
      "total_overtime_hours": "21.75",
      "absent_days": 0,
      "basic_amount": "8750.00",
      "overtime_amount": "4350.00",
      "adjustment_amount": "0.00",
      "advance_deducted": "8000.00",
      "net_salary": "5100.00",
      "paid_amount": "0.00",
      "due_amount": "5100.00",
      "status": "draft",
      "locked_at": null,
      "created_at": "2026-05-03T10:00:00Z",
      "updated_at": "2026-05-03T10:00:00Z",
      "employee": {
        "id": 1,
        "name": "Ahmed Hassan",
        "department": "Sales",
        "hajira_rate": "500.00",
        "overtime_rate": "200.00"
      },
      "deductions": [
        {
          "id": 1,
          "amount": "5000.00",
          "advance": {
            "id": 1,
            "date": "2026-01-15",
            "amount": "5000.00",
            "reason": "Personal Loan"
          }
        }
      ]
    }
  ],
  "meta": {
    "month": "2026-05",
    "total": 1
  }
}
```

---

### 2. Generate Salary Sheets
**Request:**
```http
POST /api/salaries
Content-Type: application/json

{
  "month": "2026-05",
  "employee_ids": [1, 2, 3]
}
```

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| month | string | Yes | Format: YYYY-MM |
| employee_ids | array | No | Employee IDs. If omitted, all active |

**Response (201 Created):**
```json
{
  "success": true,
  "message": "3 salary sheets generated/updated",
  "data": [
    {
      "id": 1,
      "employee_id": 1,
      "month": "2026-05",
      "total_hajira": "17.50",
      "basic_amount": "8750.00",
      "overtime_amount": "4350.00",
      "advance_deducted": "8000.00",
      "net_salary": "5100.00",
      "status": "draft",
      "locked_at": null
    }
  ]
}
```

**Error (400 Bad Request):**
```json
{
  "success": false,
  "message": "Invalid month format or no active employees"
}
```

---

### 3. Get Salary Details
**Request:**
```http
GET /api/salaries/1
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "employee_id": 1,
    "month": "2026-05",
    "total_hajira": "17.50",
    "total_overtime_hours": "21.75",
    "absent_days": 0,
    "basic_amount": "8750.00",
    "overtime_amount": "4350.00",
    "adjustment_amount": "0.00",
    "advance_deducted": "8000.00",
    "net_salary": "5100.00",
    "paid_amount": "0.00",
    "due_amount": "5100.00",
    "status": "draft",
    "locked_at": null,
    "created_at": "2026-05-03T10:00:00Z",
    "updated_at": "2026-05-03T10:00:00Z",
    "employee": {
      "id": 1,
      "name": "Ahmed Hassan",
      "department": "Sales",
      "phone": "0300-1234567",
      "address": "123 Main St",
      "joining_date": "2024-01-01",
      "hajira_rate": "500.00",
      "overtime_rate": "200.00",
      "status": "active"
    },
    "deductions": [
      {
        "id": 1,
        "salary_sheet_id": 1,
        "advance_id": 1,
        "employee_id": 1,
        "amount": "5000.00",
        "created_at": "2026-05-03T10:00:00Z",
        "updated_at": "2026-05-03T10:00:00Z",
        "advance": {
          "id": 1,
          "employee_id": 1,
          "date": "2026-01-15",
          "amount": "5000.00",
          "reason": "Personal Loan",
          "note": "Monthly repayment",
          "created_by": 1
        }
      }
    ]
  }
}
```

**Error (404 Not Found):**
```json
{
  "success": false,
  "message": "Salary sheet not found"
}
```

---

### 4. Lock & Apply Adjustment
**Request:**
```http
PUT /api/salaries/1
Content-Type: application/json

{
  "adjustment_amount": 500
}
```

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| adjustment_amount | decimal | Yes | Positive (bonus) or negative (deduction) |

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Salary sheet locked successfully",
  "data": {
    "id": 1,
    "employee_id": 1,
    "month": "2026-05",
    "total_hajira": "17.50",
    "basic_amount": "8750.00",
    "overtime_amount": "4350.00",
    "adjustment_amount": "500.00",
    "advance_deducted": "8000.00",
    "net_salary": "5600.00",
    "status": "locked",
    "locked_at": "2026-05-03T15:44:44Z"
  }
}
```

**Error (403 Forbidden):**
```json
{
  "success": false,
  "message": "Cannot edit locked salary sheet"
}
```

**Error (400 Bad Request):**
```json
{
  "success": false,
  "message": "Adjustment amount is required and must be numeric"
}
```

---

### 5. Lock Without Adjustment
**Request:**
```http
POST /api/salaries/1/lock
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Salary sheet locked successfully",
  "data": {
    "id": 1,
    "status": "locked",
    "locked_at": "2026-05-03T15:44:44Z",
    "net_salary": "5100.00"
  }
}
```

**Error (403 Forbidden):**
```json
{
  "success": false,
  "message": "Salary sheet is already locked"
}
```

---

### 6. Regenerate Draft Salary
**Request:**
```http
POST /api/salaries/1/regenerate
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Salary sheet regenerated successfully",
  "data": {
    "id": 1,
    "employee_id": 1,
    "month": "2026-05",
    "total_hajira": "17.50",
    "total_overtime_hours": "21.75",
    "absent_days": 0,
    "basic_amount": "8750.00",
    "overtime_amount": "4350.00",
    "adjustment_amount": "0.00",
    "advance_deducted": "8000.00",
    "net_salary": "5100.00",
    "status": "draft",
    "locked_at": null
  }
}
```

**Error (403 Forbidden):**
```json
{
  "success": false,
  "message": "Cannot regenerate locked salary sheet"
}
```

---

## Common Use Cases

### Generate Monthly Salary for All Employees
```bash
curl -X POST https://your-domain.com/api/salaries \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "month": "2026-05"
  }'
```

### Generate for Specific Employees
```bash
curl -X POST https://your-domain.com/api/salaries \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "month": "2026-05",
    "employee_ids": [1, 3, 5]
  }'
```

### Get All Draft Salaries for May
```bash
curl -X GET "https://your-domain.com/api/salaries?month=2026-05&status=draft" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get Locked Salaries for Employee 1
```bash
curl -X GET "https://your-domain.com/api/salaries?month=2026-05&status=locked&employee_id=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Lock Salary with Bonus of 1000
```bash
curl -X PUT https://your-domain.com/api/salaries/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "adjustment_amount": 1000
  }'
```

### Lock Salary with Penalty of 500
```bash
curl -X PUT https://your-domain.com/api/salaries/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "adjustment_amount": -500
  }'
```

### Regenerate with Latest Attendance
```bash
curl -X POST https://your-domain.com/api/salaries/1/regenerate \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Status Codes

| Code | Meaning | Scenario |
|------|---------|----------|
| 200 | OK | Successful GET, PUT, POST |
| 201 | Created | Salary sheet created |
| 400 | Bad Request | Invalid input, validation error |
| 403 | Forbidden | Operation not allowed (locked, etc) |
| 404 | Not Found | Salary sheet doesn't exist |
| 500 | Server Error | Database error, exception |

---

## Calculation Formula

```
net_salary = basic_amount + overtime_amount + adjustment_amount - advance_deducted

Where:
  basic_amount = total_hajira × employee.hajira_rate
  overtime_amount = total_overtime_hours × employee.overtime_rate
  adjustment_amount = manually set (positive/negative)
  advance_deducted = auto-deducted from oldest advances
```

---

## Response Structure

### Success Response
```json
{
  "success": true,
  "message": "Optional message",
  "data": { ...response data },
  "meta": { ...optional metadata }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description"
}
```

---

## Rate Limiting
No explicit rate limiting implemented. Use standard API rate limiting practices.

## Pagination
List endpoint returns all results. For large datasets, implement pagination in future versions.

## Timestamps
All timestamps are in ISO 8601 format (UTC):
- `2026-05-03T15:44:44Z`

## Decimal Precision
All currency fields use 2 decimal places:
- `"amount": "1000.00"`

---

## Migration Guide from Draft

### Flow for Processing Salary
1. **Generate** → Creates draft salary
2. **Review** → Check calculations via GET /api/salaries/1
3. **Regenerate** (if needed) → POST /api/salaries/1/regenerate
4. **Lock** → PUT /api/salaries/1 with adjustment
5. **Record Payment** → (Future enhancement)
6. **Archive** → (Future enhancement)

---

## Notes for Mobile App Integration

1. Always use ISO 8601 dates (YYYY-MM format for month)
2. Parse decimal fields as strings to avoid floating-point errors
3. Handle 403 errors gracefully (locked sheets)
4. Show adjustment in UI for transparency
5. Render deductions list for employee communication
6. Use locked_at timestamp to show when locked
7. Implement offline support for viewing (cache GET responses)
8. Require online for POST/PUT operations

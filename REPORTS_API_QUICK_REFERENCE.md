# Reports API Quick Reference

## Authentication

All API endpoints require Bearer token authentication. Include in header:

```bash
Authorization: Bearer YOUR_TOKEN_HERE
```

Get token via login:
```bash
POST /api/login
{
  "email": "user@example.com",
  "password": "password"
}
```

Response includes `token` field to use in subsequent requests.

## Base URL

```
http://localhost:8000/api/reports/
```

## Endpoints

### 1. Monthly Hajira Report

```bash
GET /api/reports/monthly-hajira
```

**Query Parameters**:
- `month` (optional): YYYY-MM format. Default: current month
- `employee_id` (optional): Integer. Show single employee

**Example Request**:
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/monthly-hajira?month=2026-05&employee_id=1"
```

**Response** (200 OK):
```json
{
  "data": [
    {
      "employee_id": 1,
      "employee_name": "Ahmed Khan",
      "department": "Sales",
      "total_hajira": 20.0,
      "present_days": 20,
      "absent_days": 0
    },
    {
      "employee_id": 2,
      "employee_name": "Fatima Ahmed",
      "department": "HR",
      "total_hajira": 18.5,
      "present_days": 19,
      "absent_days": 1
    }
  ],
  "meta": {
    "month": "2026-05",
    "employee_id": null,
    "record_count": 2,
    "total_hajira": 38.5,
    "total_present": 39,
    "total_absent": 1
  }
}
```

---

### 2. Overtime Report

```bash
GET /api/reports/overtime
```

**Query Parameters**:
- `from_date` (optional): YYYY-MM-DD format. Default: 1 month ago
- `to_date` (optional): YYYY-MM-DD format. Default: today
- `employee_id` (optional): Integer

**Example Request**:
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/overtime?from_date=2026-04-01&to_date=2026-04-30"
```

**Response** (200 OK):
```json
{
  "data": [
    {
      "employee_id": 1,
      "employee_name": "Ahmed Khan",
      "department": "Sales",
      "total_overtime_hours": 5.5,
      "overtime_rate": 100.00,
      "overtime_amount": 550.00
    }
  ],
  "meta": {
    "from_date": "2026-04-01",
    "to_date": "2026-04-30",
    "employee_id": null,
    "record_count": 1,
    "total_hours": 5.5,
    "total_amount": 550.00
  }
}
```

---

### 3. Absent Report

```bash
GET /api/reports/absent
```

**Query Parameters**:
- `from_date` (optional): YYYY-MM-DD format
- `to_date` (optional): YYYY-MM-DD format
- `employee_id` (optional): Integer

**Example Request**:
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/absent?employee_id=1"
```

**Response** (200 OK):
```json
{
  "data": [
    {
      "employee_id": 1,
      "employee_name": "Ahmed Khan",
      "department": "Sales",
      "total_absent_days": 2,
      "absent_dates": ["2026-04-05", "2026-04-10"]
    }
  ],
  "meta": {
    "from_date": "2026-03-03",
    "to_date": "2026-05-03",
    "employee_id": 1,
    "record_count": 1,
    "total_absent_count": 2
  }
}
```

---

### 4. Advance Report

```bash
GET /api/reports/advance
```

**Query Parameters**:
- `from_date` (optional): YYYY-MM-DD format
- `to_date` (optional): YYYY-MM-DD format
- `employee_id` (optional): Integer

**Example Request**:
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/advance"
```

**Response** (200 OK):
```json
{
  "data": [
    {
      "employee_id": 1,
      "employee_name": "Ahmed Khan",
      "department": "Sales",
      "total_advance": 10000.00,
      "total_deducted": 3000.00,
      "remaining_balance": 7000.00
    }
  ],
  "meta": {
    "from_date": "2026-03-03",
    "to_date": "2026-05-03",
    "employee_id": null,
    "record_count": 1,
    "total_advance_given": 10000.00,
    "total_advance_deducted": 3000.00,
    "total_remaining_balance": 7000.00
  }
}
```

---

### 5. Salary Sheet Report

```bash
GET /api/reports/salary-sheet
```

**Query Parameters**:
- `month` (optional): YYYY-MM format. Default: current month
- `employee_id` (optional): Integer

**Example Request**:
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/salary-sheet?month=2026-05"
```

**Response** (200 OK):
```json
{
  "data": [
    {
      "id": 1,
      "employee_id": 1,
      "employee_name": "Ahmed Khan",
      "department": "Sales",
      "month": "2026-05",
      "total_hajira": 20.0,
      "total_overtime_hours": 2.5,
      "absent_days": 0,
      "basic_amount": 25000.00,
      "overtime_amount": 250.00,
      "advance_deducted": 3000.00,
      "adjustment_amount": 0.00,
      "net_salary": 22250.00,
      "paid_amount": 22250.00,
      "due_amount": 0.00,
      "status": "paid",
      "locked_at": "2026-05-10 10:30:00"
    }
  ],
  "meta": {
    "month": "2026-05",
    "employee_id": null,
    "record_count": 1,
    "total_basic": 25000.00,
    "total_overtime": 250.00,
    "total_advance_deducted": 3000.00,
    "total_adjustments": 0.00,
    "total_net_salary": 22250.00,
    "total_paid": 22250.00,
    "total_due": 0.00
  }
}
```

---

### 6. Payment Report

```bash
GET /api/reports/payment
```

**Query Parameters**:
- `from_date` (optional): YYYY-MM-DD format
- `to_date` (optional): YYYY-MM-DD format
- `employee_id` (optional): Integer

**Example Request**:
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/payment?from_date=2026-05-01&to_date=2026-05-31"
```

**Response** (200 OK):
```json
{
  "data": [
    {
      "id": 1,
      "employee_id": 1,
      "employee_name": "Ahmed Khan",
      "payment_date": "2026-05-10",
      "amount": 22250.00,
      "payment_method": "bank",
      "note": "Monthly salary May 2026"
    },
    {
      "id": 2,
      "employee_id": 1,
      "employee_name": "Ahmed Khan",
      "payment_date": "2026-05-15",
      "amount": 5000.00,
      "payment_method": "cash",
      "note": "Advance payment"
    }
  ],
  "meta": {
    "from_date": "2026-05-01",
    "to_date": "2026-05-31",
    "employee_id": null,
    "record_count": 2,
    "total_paid": 27250.00,
    "method_summary": {
      "cash": {
        "count": 1,
        "total": 5000.00
      },
      "bank": {
        "count": 1,
        "total": 22250.00
      },
      "mobile_banking": {
        "count": 0,
        "total": 0.00
      }
    }
  }
}
```

---

### 7. Employee Ledger

```bash
GET /api/reports/employee-ledger/{employee_id}
```

**Path Parameters**:
- `employee_id` (required): Integer

**Example Request**:
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/employee-ledger/1"
```

**Response** (200 OK):
```json
{
  "data": {
    "employee": {
      "id": 1,
      "name": "Ahmed Khan",
      "phone": "03001234567",
      "department": "Sales",
      "hajira_rate": 1250.00,
      "overtime_rate": 100.00,
      "status": "active"
    },
    "attendances": [
      {
        "id": 1,
        "date": "2026-05-01",
        "hajira_type": "one",
        "hajira_value": 1.0,
        "overtime_hours": 0.0,
        "note": ""
      }
    ],
    "advances": [
      {
        "id": 1,
        "date": "2026-04-15",
        "amount": 10000.00,
        "reason": "Emergency",
        "note": "Medical emergency"
      }
    ],
    "salary_sheets": [
      {
        "id": 1,
        "month": "2026-05",
        "net_salary": 22250.00,
        "paid_amount": 22250.00,
        "due_amount": 0.00,
        "status": "paid"
      }
    ],
    "payments": [
      {
        "id": 1,
        "payment_date": "2026-05-10",
        "amount": 22250.00,
        "payment_method": "bank",
        "note": "Monthly salary"
      }
    ],
    "summary": {
      "total_advance_given": 10000.00,
      "total_advance_deducted": 3000.00,
      "current_advance_balance": 7000.00,
      "total_salary_generated": 22250.00,
      "total_paid": 22250.00,
      "total_due": 0.00
    }
  },
  "meta": {
    "employee_id": 1,
    "attendance_count": 20,
    "advance_count": 1,
    "salary_sheet_count": 1,
    "payment_count": 1
  }
}
```

---

### 8. Accounts Summary

```bash
GET /api/reports/accounts-summary
```

**Query Parameters**:
- `month` (optional): YYYY-MM format. Default: current month

**Example Request**:
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/accounts-summary?month=2026-05"
```

**Response** (200 OK):
```json
{
  "data": {
    "summary": {
      "total_salary": 45500.00,
      "employee_count": 2,
      "total_paid": 45500.00,
      "total_due": 0.00
    },
    "salary_components": {
      "total_basic": 50000.00,
      "total_overtime": 500.00,
      "total_adjustments": 0.00,
      "total_advance_deducted": 5000.00
    },
    "advance_summary": {
      "total_advance_given": 15000.00,
      "total_advance_deducted": 5000.00,
      "remaining_balance": 10000.00
    },
    "payment_summary": {
      "total_paid": 45500.00,
      "method_summary": {
        "cash": {
          "count": 0,
          "total": 0.00
        },
        "bank": {
          "count": 2,
          "total": 45500.00
        },
        "mobile_banking": {
          "count": 0,
          "total": 0.00
        }
      }
    }
  },
  "meta": {
    "month": "2026-05",
    "record_count": 2,
    "total_salary": 45500.00,
    "total_paid": 45500.00,
    "total_due": 0.00,
    "completion_percentage": 100.0
  }
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 422 Validation Error
```json
{
  "message": "Invalid month format. Use YYYY-MM",
  "errors": {
    "month": ["Invalid month format. Use YYYY-MM"]
  }
}
```

### 404 Not Found
```json
{
  "message": "Employee not found"
}
```

### 500 Server Error
```json
{
  "message": "Server error occurred"
}
```

---

## Common Use Cases

### Get all payments for specific employee
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/payment?employee_id=1"
```

### Get attendance for specific month
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/monthly-hajira?month=2026-05"
```

### Get advance balance for all employees
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/advance"
```

### Get complete employee financial history
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/employee-ledger/1"
```

### Get monthly financial summary
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reports/accounts-summary?month=2026-05"
```

---

## Filtering Guide

### Filter by Date Range
```bash
# Both from_date and to_date required for most endpoints
?from_date=2026-04-01&to_date=2026-04-30

# Supports any date range (not limited to calendar month)
?from_date=2026-04-15&to_date=2026-05-15
```

### Filter by Employee
```bash
# Most endpoints support employee_id filter
?employee_id=1

# Combine with other filters
?employee_id=1&month=2026-05
```

### Filter by Month
```bash
# Month must be YYYY-MM format
?month=2026-05

# Only supported on: monthly-hajira, salary-sheet, accounts-summary
```

---

## Response Structure

All responses follow this pattern:

```json
{
  "data": [...],          // Array or object with actual data
  "meta": {               // Metadata about the response
    "record_count": 10,   // Number of records returned
    "total_*": 0,         // Totals/summaries (varies by endpoint)
    ...                   // Other relevant metadata
  }
}
```

---

## Rate Limiting

No explicit rate limiting implemented. For production, consider adding:
- Request throttling per user
- Caching layer (Redis)
- Database query optimization

---

## Testing with Postman

1. Create collection: "Hajira Payroll - Reports"
2. Create environment variable: `BASE_URL` = http://localhost:8000
3. Create environment variable: `TOKEN` = your_bearer_token
4. For each endpoint:
   ```
   GET {{BASE_URL}}/api/reports/monthly-hajira?month=2026-05
   Header: Authorization: Bearer {{TOKEN}}
   ```

---

## Integration Notes

- All amounts use 2 decimal precision
- All dates in YYYY-MM-DD format (or YYYY-MM for month)
- All counts are integers
- Employee names include spaces (not escaped)
- Advance balance can be 0 but never negative
- Payment method values: "cash", "bank", "mobile_banking"
- Salary status values: "draft", "locked", "partial", "paid"

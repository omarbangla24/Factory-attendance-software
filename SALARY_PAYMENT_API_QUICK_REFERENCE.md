# Salary Payment API Quick Reference

## Base URL
```
http://localhost:8000/api
```

## Authentication
All endpoints require Bearer token authentication via Laravel Sanctum.

```bash
Authorization: Bearer {token}
```

## Response Format
All responses return JSON with the following structure:

**Success (200/201):**
```json
{
  "data": { ... }
}
```

**Error (422):**
```json
{
  "message": "Error message",
  "errors": {
    "field_name": ["Error description"]
  }
}
```

---

## Endpoints

### 1. List Payments

**Endpoint:** `GET /api/payments`

**Description:** List all salary payments with optional filtering

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| month | string | Filter by month (YYYY-MM format) |
| salary_sheet_id | integer | Filter by salary sheet ID |
| employee_id | integer | Filter by employee ID |
| page | integer | Pagination (default: 1) |

**Examples:**
```bash
# Get all payments
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/payments

# Filter by month
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/payments?month=2026-05"

# Filter by employee
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/payments?employee_id=1"

# Combine filters
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/payments?month=2026-05&employee_id=1"

# Pagination
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/payments?page=2"
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "salary_sheet_id": 1,
      "employee_id": 1,
      "payment_date": "2026-05-15",
      "amount": "5000.00",
      "payment_method": "cash",
      "note": "May payment",
      "created_by": 1,
      "created_at": "2026-05-15T10:30:00Z",
      "updated_at": "2026-05-15T10:30:00Z",
      "salary_sheet": {
        "id": 1,
        "employee_id": 1,
        "month": "2026-05",
        "net_salary": "10000.00",
        "paid_amount": "5000.00",
        "due_amount": "5000.00",
        "status": "partial"
      },
      "employee": {
        "id": 1,
        "name": "Ahmed Khan",
        "department": "Sales",
        "phone": "03001234567",
        "status": "active"
      },
      "created_by_user": {
        "id": 1,
        "name": "HR Manager",
        "email": "hr@example.com"
      }
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/payments?page=1",
    "last": "http://localhost:8000/api/payments?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/payments",
    "per_page": 15,
    "to": 3,
    "total": 3
  }
}
```

---

### 2. Record Payment

**Endpoint:** `POST /api/payments`

**Description:** Record a new salary payment

**Request Body:**
```json
{
  "salary_sheet_id": 1,
  "amount": 5000,
  "payment_date": "2026-05-15",
  "payment_method": "cash",
  "note": "May payment"
}
```

**Fields:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| salary_sheet_id | integer | Yes | ID of salary sheet to pay |
| amount | decimal | Yes | Payment amount (> 0, ≤ due_amount) |
| payment_date | date | Yes | Payment date (YYYY-MM-DD, not future) |
| payment_method | enum | Yes | One of: cash, bank, mobile_banking |
| note | string | No | Optional payment notes |

**Example:**
```bash
curl -X POST -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "salary_sheet_id": 1,
    "amount": 5000,
    "payment_date": "2026-05-15",
    "payment_method": "cash",
    "note": "May payment"
  }' \
  http://localhost:8000/api/payments
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 1,
    "salary_sheet_id": 1,
    "employee_id": 1,
    "payment_date": "2026-05-15",
    "amount": "5000.00",
    "payment_method": "cash",
    "note": "May payment",
    "created_by": 1,
    "created_at": "2026-05-15T10:30:00Z",
    "updated_at": "2026-05-15T10:30:00Z"
  }
}
```

**Validation Errors (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "salary_sheet_id": ["The salary sheet id field is required."],
    "amount": ["The amount must be greater than 0.", "The amount cannot exceed due amount."],
    "payment_method": ["The payment method must be one of: cash, bank, mobile_banking."]
  }
}
```

**Overpayment Error (422):**
```json
{
  "message": "Payment amount exceeds due amount",
  "errors": {
    "amount": ["Cannot pay more than due amount (5000.00). Trying to pay 6000.00"]
  }
}
```

**Side Effects:**
- Creates SalaryPayment record
- Updates SalarySheet.paid_amount
- Updates SalarySheet.due_amount
- Updates SalarySheet.status:
  - If due_amount becomes 0 → "paid"
  - If due_amount > 0 and paid_amount > 0 → "partial"
- Sets created_by to authenticated user ID
- Updates SalaryPayment.created_at and updated_at

---

### 3. Get Salary Payments

**Endpoint:** `GET /api/salaries/{id}/payments`

**Description:** Get all payments for a specific salary sheet

**Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | Salary sheet ID (path parameter) |

**Example:**
```bash
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/salaries/1/payments
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "salary_sheet_id": 1,
      "employee_id": 1,
      "payment_date": "2026-05-15",
      "amount": "5000.00",
      "payment_method": "cash",
      "note": "May payment",
      "created_by": 1,
      "created_at": "2026-05-15T10:30:00Z",
      "updated_at": "2026-05-15T10:30:00Z",
      "employee": {
        "id": 1,
        "name": "Ahmed Khan"
      },
      "created_by_user": {
        "id": 1,
        "name": "HR Manager"
      }
    },
    {
      "id": 2,
      "salary_sheet_id": 1,
      "employee_id": 1,
      "payment_date": "2026-05-16",
      "amount": "3000.00",
      "payment_method": "bank",
      "note": null,
      "created_by": 1,
      "created_at": "2026-05-16T14:00:00Z",
      "updated_at": "2026-05-16T14:00:00Z",
      "employee": {
        "id": 1,
        "name": "Ahmed Khan"
      },
      "created_by_user": {
        "id": 1,
        "name": "HR Manager"
      }
    }
  ]
}
```

**Not Found (404):**
```json
{
  "message": "Salary sheet not found"
}
```

---

### 4. Get Employee Payment History

**Endpoint:** `GET /api/employees/{id}/payments`

**Description:** Get all payments for a specific employee

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| month | string | Filter by month (YYYY-MM format) - optional |

**Examples:**
```bash
# Get all employee payments
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/employees/1/payments

# Filter by month
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/employees/1/payments?month=2026-05"
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "salary_sheet_id": 1,
      "employee_id": 1,
      "payment_date": "2026-05-15",
      "amount": "5000.00",
      "payment_method": "cash",
      "note": "May payment",
      "created_by": 1,
      "created_at": "2026-05-15T10:30:00Z",
      "updated_at": "2026-05-15T10:30:00Z",
      "salary_sheet": {
        "id": 1,
        "employee_id": 1,
        "month": "2026-05",
        "net_salary": "10000.00",
        "paid_amount": "5000.00",
        "due_amount": "5000.00",
        "status": "partial"
      },
      "created_by_user": {
        "id": 1,
        "name": "HR Manager"
      }
    }
  ]
}
```

**Not Found (404):**
```json
{
  "message": "Employee not found"
}
```

---

### 5. Reverse Payment

**Endpoint:** `DELETE /api/payments/{id}`

**Description:** Delete a payment and recalculate salary sheet

**Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | Payment ID (path parameter) |

**Example:**
```bash
curl -X DELETE -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/payments/1
```

**Response (200 OK):**
```json
{
  "data": {
    "message": "Payment reversed successfully"
  }
}
```

**Not Found (404):**
```json
{
  "message": "Payment not found"
}
```

**Side Effects:**
- Deletes SalaryPayment record
- Recalculates SalarySheet.paid_amount (sum of remaining payments)
- Recalculates SalarySheet.due_amount (net_salary - paid_amount)
- Updates SalarySheet.status:
  - If due_amount = net_salary → "locked" (back to locked)
  - If due_amount > 0 and paid_amount > 0 → "partial"
  - If due_amount = 0 → "paid" (if other payments remain)

---

## Common Workflows

### Workflow 1: Full Payment

```bash
# 1. Get salary sheet to pay
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/salaries/1

# Response shows: net_salary: 10000, paid_amount: 0, due_amount: 10000

# 2. Record full payment
curl -X POST -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "salary_sheet_id": 1,
    "amount": 10000,
    "payment_date": "2026-05-15",
    "payment_method": "cash"
  }' \
  http://localhost:8000/api/payments

# Response: Payment created, salary status now "paid"

# 3. Verify payment
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/salaries/1/payments

# Response: Shows 1 payment for 10000
```

### Workflow 2: Partial Payments

```bash
# 1. Record first payment (5000 of 10000)
curl -X POST -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "salary_sheet_id": 1,
    "amount": 5000,
    "payment_date": "2026-05-15",
    "payment_method": "cash"
  }' \
  http://localhost:8000/api/payments

# Response: Status now "partial"

# 2. Record second payment (3000 of remaining 5000)
curl -X POST -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "salary_sheet_id": 1,
    "amount": 3000,
    "payment_date": "2026-05-16",
    "payment_method": "bank"
  }' \
  http://localhost:8000/api/payments

# Response: Status still "partial", due_amount now 2000

# 3. Record final payment (2000)
curl -X POST -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "salary_sheet_id": 1,
    "amount": 2000,
    "payment_date": "2026-05-17",
    "payment_method": "mobile_banking"
  }' \
  http://localhost:8000/api/payments

# Response: Status now "paid", due_amount = 0
```

### Workflow 3: Reverse Payment

```bash
# 1. Get payment to reverse
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/salaries/1/payments

# Response shows payment ID 2 for 3000

# 2. Reverse the payment
curl -X DELETE -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/payments/2

# Response: Payment reversed

# 3. Verify reversal
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/salaries/1

# Response: paid_amount reduced, due_amount increased, status recalculated
```

---

## Status Codes Reference

| Code | Meaning |
|------|---------|
| 200 | Success (GET, DELETE) |
| 201 | Created (POST) |
| 400 | Bad Request |
| 401 | Unauthorized (missing/invalid token) |
| 404 | Not Found (salary sheet, payment, employee) |
| 422 | Unprocessable Entity (validation error) |
| 500 | Server Error |

---

## Error Handling

**Authentication Errors (401):**
```json
{
  "message": "Unauthenticated."
}
```

**Validation Errors (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

**Overpayment Error (422):**
```json
{
  "message": "Payment amount exceeds due amount",
  "errors": {
    "amount": ["Cannot pay more than due amount"]
  }
}
```

---

## Implementation Notes

1. **Decimal Precision**: All amounts use `decimal:2` casting
2. **Status Auto-Update**: Payment POST/DELETE automatically updates salary status
3. **Audit Trail**: created_by tracks which user recorded the payment
4. **Idempotency**: Payment IDs are unique; duplicate submissions create new records
5. **Timestamp Tracking**: created_at/updated_at maintained automatically
6. **Cascade Behavior**: Payment deletion does not delete salary sheet; only recalculates totals

---

## Testing Tips

1. **Use Postman**: Import this collection for easier testing
2. **Verify Database**: Check `salary_payments` and `salary_sheets` tables after operations
3. **Check Logs**: Laravel logs are in `storage/logs/laravel.log`
4. **Test Edge Cases**:
   - Overpayment attempts
   - Decimal amounts
   - Future dates
   - Invalid payment methods
5. **Reverse Order**: Test reversing payments in reverse chronological order

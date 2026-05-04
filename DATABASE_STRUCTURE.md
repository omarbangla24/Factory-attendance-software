# Database Structure - Hajira Payroll System

## Overview
The Hajira Payroll database is designed to manage employee attendance tracking, advance requests, and salary calculation. The schema supports flexible attendance types, advance deductions, and multi-payment salary processing.

## Database Schema

### 1. Employees Table
Stores employee master data with rate information.

```sql
CREATE TABLE employees (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(255) UNIQUE NOT NULL,
    address TEXT,
    joining_date DATE NOT NULL,
    department VARCHAR(255),
    hajira_rate DECIMAL(10,2) NOT NULL,      -- Daily attendance rate
    overtime_rate DECIMAL(10,2) NOT NULL,    -- Hourly overtime rate
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX (status),
    INDEX (department)
)
```

**Fields:**
- `hajira_rate`: Daily rate paid per attendance day (1 full day = 1 hajira)
- `overtime_rate`: Rate per overtime hour
- `status`: Active/Inactive for payroll processing

---

### 2. Attendances Table
Daily attendance records with hajira types and overtime hours.

```sql
CREATE TABLE attendances (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT NOT NULL FOREIGN KEY,
    date DATE NOT NULL,
    hajira_type ENUM('absent', 'one', 'one_half') DEFAULT 'absent',
    hajira_value DECIMAL(3,1) DEFAULT 0,     -- 0, 1, or 1.5
    overtime_hours DECIMAL(5,2) DEFAULT 0,
    note TEXT,
    created_by BIGINT FOREIGN KEY REFERENCES users(id),
    updated_by BIGINT FOREIGN KEY REFERENCES users(id),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY (employee_id, date),
    INDEX (date)
)
```

**Fields:**
- `hajira_type`: Classification of attendance type
  - `absent`: Employee did not attend (hajira_value = 0)
  - `one`: Full day attendance (hajira_value = 1)
  - `one_half`: Half day or early leave (hajira_value = 1.5)
- `hajira_value`: Numeric value for calculation (0, 1, or 1.5)
- `overtime_hours`: Decimal hours worked beyond standard hours
- **Unique Constraint**: Prevents duplicate attendance entries for same employee on same date

---

### 3. Advances Table
Employee salary advance requests.

```sql
CREATE TABLE advances (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT NOT NULL FOREIGN KEY,
    date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    reason VARCHAR(255),
    note TEXT,
    created_by BIGINT FOREIGN KEY REFERENCES users(id),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX (employee_id),
    INDEX (date)
)
```

**Fields:**
- `amount`: Advance amount to be deducted from salary
- `reason`: Type of advance (Emergency, Medical, etc.)

---

### 4. Salary Sheets Table
Monthly salary calculation and payment status tracking.

```sql
CREATE TABLE salary_sheets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT NOT NULL FOREIGN KEY,
    month VARCHAR(7) NOT NULL,                -- Format: YYYY-MM
    total_hajira DECIMAL(5,2) DEFAULT 0,
    total_overtime_hours DECIMAL(8,2) DEFAULT 0,
    absent_days INT DEFAULT 0,
    basic_amount DECIMAL(12,2) DEFAULT 0,    -- hajira_rate * total_hajira
    overtime_amount DECIMAL(12,2) DEFAULT 0, -- overtime_rate * total_overtime_hours
    advance_deducted DECIMAL(12,2) DEFAULT 0,
    adjustment_amount DECIMAL(12,2) DEFAULT 0,
    net_salary DECIMAL(12,2) DEFAULT 0,      -- basic + overtime - advance + adjustment
    paid_amount DECIMAL(12,2) DEFAULT 0,
    due_amount DECIMAL(12,2) DEFAULT 0,      -- net_salary - paid_amount
    status ENUM('draft', 'locked', 'partial', 'paid') DEFAULT 'draft',
    locked_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY (employee_id, month),
    INDEX (status),
    INDEX (month)
)
```

**Salary Calculation Flow:**
```
basic_amount = hajira_rate × total_hajira
overtime_amount = overtime_rate × total_overtime_hours
advance_deducted = SUM(advances linked to this month)
net_salary = basic_amount + overtime_amount - advance_deducted + adjustment_amount
due_amount = net_salary - paid_amount
```

**Status Values:**
- `draft`: Salary sheet created, not finalized
- `locked`: Salary finalized, cannot modify attendance for this month
- `partial`: Partial payment received
- `paid`: Fully paid

**Unique Constraint**: Prevents duplicate salary sheets for same employee and month

---

### 5. Advance Deductions Table
Links advances to specific salary sheets for deduction tracking.

```sql
CREATE TABLE advance_deductions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    advance_id BIGINT NOT NULL FOREIGN KEY,
    employee_id BIGINT NOT NULL FOREIGN KEY,
    salary_sheet_id BIGINT NOT NULL FOREIGN KEY,
    amount DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX (employee_id),
    INDEX (salary_sheet_id)
)
```

**Purpose**: Audit trail linking which advances were deducted from which salary sheets

---

### 6. Salary Payments Table
Individual payment transactions for salary sheets.

```sql
CREATE TABLE salary_payments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    salary_sheet_id BIGINT NOT NULL FOREIGN KEY,
    employee_id BIGINT NOT NULL FOREIGN KEY,
    payment_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'bank', 'mobile_banking') DEFAULT 'cash',
    note TEXT,
    created_by BIGINT FOREIGN KEY REFERENCES users(id),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX (salary_sheet_id),
    INDEX (employee_id),
    INDEX (payment_date)
)
```

**Purpose**: Track multiple payment transactions per salary sheet (for partial payments)

---

## Database Relationships

### Entity Relationship Diagram

```
┌─────────────┐
│  Employees  │
├─────────────┤
│ id (PK)     │
│ name        │
│ phone       │
│ department  │
│ hajira_rate │
└─────────────┘
      │ 1
      │
      ├──────────── * ──────────┬─────────────────┬──────────────┐
      │                         │                 │              │
      │                         ▼                 ▼              ▼
   Attendances         Advances             SalarySheets   AdvanceDeductions
      │              │                              │              │
      └──────────────┘  └──────────────┬──────────────┘              │
                                       │                            │
                                       ▼                            │
                              SalaryPayments◄──────────────────────┘
```

### Model Relationships

**Employee** relationships:
- `hasMany` Attendance
- `hasMany` Advance
- `hasMany` SalarySheet
- `hasMany` SalaryPayment
- `hasMany` AdvanceDeduction

**SalarySheet** relationships:
- `belongsTo` Employee
- `hasMany` AdvanceDeduction
- `hasMany` SalaryPayment

**AdvanceDeduction** relationships:
- `belongsTo` Advance
- `belongsTo` Employee
- `belongsTo` SalarySheet

**SalaryPayment** relationships:
- `belongsTo` SalarySheet
- `belongsTo` Employee

**Attendance** relationships:
- `belongsTo` Employee
- `belongsTo` User (created_by)
- `belongsTo` User (updated_by)

**Advance** relationships:
- `belongsTo` Employee
- `belongsTo` User (created_by)
- `hasMany` AdvanceDeduction

---

## Key Constraints & Indexes

### Unique Constraints
1. **Attendances**: `UNIQUE(employee_id, date)` - One record per employee per day
2. **SalarySheets**: `UNIQUE(employee_id, month)` - One salary sheet per employee per month
3. **Employees**: `UNIQUE(phone)` - Unique phone numbers

### Indexes
1. **Attendances**: `(date)` - Query by date range
2. **SalarySheets**: `(status)`, `(month)` - Filter by status/month
3. **Salary Payments**: `(payment_date)` - Query by payment date
4. **Employees**: `(status)`, `(department)` - Filter active employees
5. **Advances/Attendances**: `(employee_id)` - Foreign key lookups

---

## Test Data

### Seeded Data
- **5 Employees** (4 active, 1 inactive) across 5 departments
- **85 Attendance Records** for current month (skips weekends)
- **2 Advance Requests** (50% of employees)
- **5 Salary Sheets** (one per employee for current month)
- **2 Advance Deductions** linked to salary sheets
- **3 Salary Payments** (50% partial paid)

### Sample Calculation
```
Employee: Ahmed Hassan
Hajira Rate: 500 TK/day
Overtime Rate: 150 TK/hour

Attendance (for month):
  - Present days: 18 days
  - Absent days: 2 days
  - Overtime hours: 12 hours

Calculation:
  basic_amount = 500 × 18 = 9,000 TK
  overtime_amount = 150 × 12 = 1,800 TK
  advance_deducted = 5,000 TK (if advance exists)
  net_salary = 9,000 + 1,800 - 5,000 = 5,800 TK
```

---

## What to Test

### 1. Data Integrity
- [ ] Create employee and verify all fields saved correctly
- [ ] Create attendance record and verify unique constraint (try duplicate date)
- [ ] Create advance and verify linked to correct employee
- [ ] Create salary sheet and verify unique constraint (try duplicate month)

### 2. Relationships
- [ ] Load employee with all relationships populated
- [ ] Load salary sheet and verify advance deductions are linked
- [ ] Load advance and verify deductions are linked to salary sheets
- [ ] Load attendance and verify created_by/updated_by users

### 3. Salary Calculations
- [ ] Verify basic_amount = hajira_rate × total_hajira
- [ ] Verify overtime_amount = overtime_rate × total_overtime_hours
- [ ] Verify net_salary calculation with and without advances
- [ ] Verify due_amount updates when paid_amount changes

### 4. Partial Payments
- [ ] Create multiple salary payments for single salary sheet
- [ ] Verify due_amount decreases with each payment
- [ ] Verify status changes to 'partial' when first payment made

### 5. Data Constraints
- [ ] Cannot create two attendances for same employee on same date
- [ ] Cannot create two salary sheets for same employee in same month
- [ ] Phone numbers are unique across employees
- [ ] Foreign key constraints prevent orphaned records

### 6. Queries
- [ ] Query all attendances for employee in date range
- [ ] Query salary sheet by month
- [ ] Query advance deductions for specific salary sheet
- [ ] Query payment history for employee

### 7. Status Workflow
- [ ] Salary sheet starts as 'draft'
- [ ] Can lock salary sheet (status → 'locked')
- [ ] Can update status to 'partial' when partial payment made
- [ ] Can mark as 'paid' when fully paid

---

## Database Verification Commands

```bash
# Check table count
mysql> SELECT COUNT(*) FROM employees;
mysql> SELECT COUNT(*) FROM attendances;
mysql> SELECT COUNT(*) FROM salary_sheets;

# Check relationships
mysql> SELECT e.name, COUNT(a.id) as attendances 
        FROM employees e 
        LEFT JOIN attendances a ON e.id = a.employee_id 
        GROUP BY e.id;

# Check salary calculation
mysql> SELECT employee_id, month, total_hajira, basic_amount, 
              overtime_amount, advance_deducted, net_salary, due_amount, status
       FROM salary_sheets;

# Check advance deductions
mysql> SELECT ad.id, ad.advance_id, ad.salary_sheet_id, ad.amount
       FROM advance_deductions ad;
```

---

## Laravel Tinker Verification

```php
// Count records
Employee::count();          // Should be 5
Attendance::count();        // Should be 85+
Advance::count();           // Should be ~2
SalarySheet::count();       // Should be 5
SalaryPayment::count();     // Should be ~3

// Load with relationships
Employee::with(['attendances', 'advances', 'salarySheets'])->first();

// Check salary calculations
$sheet = SalarySheet::with('advanceDeductions', 'salaryPayments')->first();
$sheet->net_salary;
$sheet->due_amount;

// Verify unique constraints
Attendance::where(['employee_id' => 1, 'date' => '2026-05-03'])->count(); // Should be 1
SalarySheet::where(['employee_id' => 1, 'month' => '2026-05'])->count();  // Should be 1
```

---

## Next Steps
1. Create controllers for CRUD operations on each model
2. Build views for attendance tracking and salary management
3. Implement salary calculation logic
4. Create reports for HR dashboard
5. Add API endpoints for mobile app

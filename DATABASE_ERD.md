# Database Entity Relationship Diagram

## ERD (Text Format)

```
┌──────────────────────────────────────┐
│          EMPLOYEES                   │
├──────────────────────────────────────┤
│ PK  id                     BIGINT    │
│     name                   VARCHAR   │
│     phone                  VARCHAR U │ ← Unique
│     address                TEXT      │
│     joining_date           DATE      │
│     department             VARCHAR   │
│     hajira_rate            DECIMAL   │ ← Daily rate
│     overtime_rate          DECIMAL   │ ← Hourly rate
│     status                 ENUM      │ (active/inactive)
│     created_at, updated_at TIMESTAMP │
└──────────────────────────────────────┘
    │ 1
    │
    ├─────────────────────────────────────────────────────────────┬──────────────────────┐
    │                                                              │                      │
    │ *                                                            │ *                    │ *
┌───▼─────────────────────────────┐  ┌──────────────────────────┐│──────────────────────▼─────────────┐
│       ATTENDANCES               │  │      ADVANCES            ││    SALARY_SHEETS                  │
├─────────────────────────────────┤  ├──────────────────────────┤├───────────────────────────────────┤
│ PK  id                 BIGINT   │  │ PK  id            BIGINT ││ PK  id               BIGINT        │
│ FK  employee_id        BIGINT   │  │ FK  employee_id   BIGINT ││ FK  employee_id      BIGINT        │
│     date               DATE  U* │  │     date          DATE   ││     month            VARCHAR    U* │
│     hajira_type        ENUM     │  │     amount        DECIMAL││     total_hajira     DECIMAL      │
│ (absent/one/one_half)          │  │     reason        VARCHAR││     total_overtime   DECIMAL      │
│     hajira_value       DECIMAL  │  │     note          TEXT   ││     absent_days      INT          │
│ (0/1/1.5)                      │  │ FK  created_by    BIGINT ││     basic_amount     DECIMAL      │
│     overtime_hours     DECIMAL  │  │     created_at,         ││     overtime_amount  DECIMAL      │
│ FK  created_by        BIGINT   │  │     updated_at    TIME   ││     advance_deducted DECIMAL      │
│ FK  updated_by        BIGINT   │  └──────────────────────────┤│     adjustment_amount DECIMAL     │
│     created_at,                │          │ 1                 ││     net_salary       DECIMAL      │
│     updated_at         TIME    │          │                  ││     paid_amount      DECIMAL      │
└─────────────────────────────────┘          │                  ││     due_amount       DECIMAL      │
    Unique: (emp_id,date)*                   │                  ││     status           ENUM         │
                                             │                  ││ (draft/locked/      │
    * Prevents duplicate attendance         │                  ││  partial/paid)      │
      for same employee on same date        │                  ││     locked_at        TIMESTAMP    │
                                             │                  ││     created_at,     │
    FK employees.id         ◄────────────────┘                  ││     updated_at       TIMESTAMP    │
    FK users(created_by)                                        ││
    FK users(updated_by)                                        └───────────────────────────────────┘
                                                                    Unique: (emp_id, month)*
                                                                    * One salary sheet per
                                                                      employee per month


                                                                    * One salary per employee per month
                                                                    │ *
    ┌─────────────────────────────────────────────────────────────┴───────────────────────┐
    │                                                                                       │
    │ *                                                                                    │ *
┌───▼────────────────────────────────┐  ┌──────────────────────────────────────────────────▼─────┐
│   ADVANCE_DEDUCTIONS               │  │        SALARY_PAYMENTS                                 │
├────────────────────────────────────┤  ├────────────────────────────────────────────────────────┤
│ PK  id                   BIGINT    │  │ PK  id                 BIGINT                         │
│ FK  advance_id           BIGINT    │  │ FK  salary_sheet_id    BIGINT                         │
│ FK  employee_id          BIGINT    │  │ FK  employee_id        BIGINT                         │
│ FK  salary_sheet_id      BIGINT    │  │     payment_date       DATE                           │
│     amount               DECIMAL   │  │     amount             DECIMAL                        │
│     created_at, updated_at TIMESTAMP│  │     payment_method    ENUM (cash/bank/mobile_banking)│
└────────────────────────────────────┘  │ FK  created_by         BIGINT                         │
                                        │     created_at,                                       │
    Linking table:                      │     updated_at         TIMESTAMP                      │
    - Links Advances to SalarySheets    └────────────────────────────────────────────────────────┘
    - Tracks which advances are
      deducted from which salary      Purpose:
      - Tracks individual payments
      - Supports partial payments
```

---

## Relationship Summary

### Parent-Child (1:Many)

```
Employee (1) ──────────────► Attendance (Many)
Employee (1) ──────────────► Advance (Many)
Employee (1) ──────────────► SalarySheet (Many)
Employee (1) ──────────────► SalaryPayment (Many)
Employee (1) ──────────────► AdvanceDeduction (Many)

SalarySheet (1) ───────────► AdvanceDeduction (Many)
SalarySheet (1) ───────────► SalaryPayment (Many)

Advance (1) ────────────────► AdvanceDeduction (Many)
```

### Foreign Key References

```
attendances.employee_id     → employees.id
attendances.created_by      → users.id
attendances.updated_by      → users.id

advances.employee_id        → employees.id
advances.created_by         → users.id

salary_sheets.employee_id   → employees.id

advance_deductions.advance_id       → advances.id
advance_deductions.employee_id      → employees.id
advance_deductions.salary_sheet_id  → salary_sheets.id

salary_payments.salary_sheet_id → salary_sheets.id
salary_payments.employee_id     → employees.id
salary_payments.created_by      → users.id
```

---

## Data Flow Diagram

```
                    ┌─────────────────┐
                    │  ATTENDANCE     │
                    │  (Daily Entry)  │
                    └────────┬────────┘
                             │
                             │ Aggregated by
                             ▼
                    ┌─────────────────┐
                    │  SALARY_SHEET   │
                    │  (Monthly       │
                    │   Calculation)  │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
              ▼              ▼              ▼
    ┌──────────────────┐  ┌──────────┐  ┌──────────────┐
    │  ADVANCES        │  │ADJUSTMENT│  │  DEDUCTIONS  │
    │  (Deducted from  │  │ (Manual  │  │  (Calculated)│
    │   this sheet)    │  │  adjust) │  │              │
    └────────┬─────────┘  └──────────┘  └──────────────┘
             │                │              │
             └────────────────┼──────────────┘
                              │
                              ▼
                    ┌─────────────────────┐
                    │   NET_SALARY        │
                    │   (Calculated)      │
                    └──────────┬──────────┘
                               │
                    ┌──────────▼─────────┐
                    │ SALARY_PAYMENT     │
                    │ (One or multiple)  │
                    └────────────────────┘
```

---

## Status State Machine

```
SALARY SHEET Status Workflow:

                    ┌─────────────────────────────────────┐
                    │ New Salary Sheet                    │
                    │ status: 'draft'                     │
                    └────────┬────────────────────────────┘
                             │
                    ┌────────▼─────────┐
                    │ Can edit         │
                    │ attendance data  │
                    └────────┬─────────┘
                             │ Lock
                    ┌────────▼─────────────────────────┐
                    │ Salary Locked                     │
                    │ status: 'locked'                 │
                    │ locked_at: timestamp             │
                    │ Cannot edit attendance           │
                    └────────┬─────────────────────────┘
                             │
                    ┌────────▼──────────────────┐
                    │ First Payment Received    │
                    │ status: 'partial'        │
                    │ due_amount: remaining    │
                    └────────┬─────────────────┘
                             │ (Optional)
                    ┌────────▼──────────────────┐
                    │ Fully Paid                │
                    │ status: 'paid'           │
                    │ paid_amount: net_salary  │
                    │ due_amount: 0            │
                    └──────────────────────────┘
```

---

## Attendance Type Mapping

```
hajira_type ──────► hajira_value ──────► Use in Calculation
                    
'absent'      ──────► 0        ──────► Basic salary reduced
'one'         ──────► 1        ──────► Full day hajira
'one_half'    ──────► 1.5      ──────► One and half day hajira


overtime_hours ──────► Separate field ──────► Overtime amount
(decimal hours)
```

---

## Indexes for Performance

```
EMPLOYEES table:
  - Index on (status)           → Filter active employees
  - Index on (department)       → Group by department
  - Unique on (phone)           → Validate uniqueness

ATTENDANCES table:
  - Index on (date)             → Query by date range
  - Unique on (employee_id, date) → Prevent duplicates

SALARY_SHEETS table:
  - Index on (status)           → Filter by payment status
  - Index on (month)            → Query by month
  - Unique on (employee_id, month) → Prevent duplicates

SALARY_PAYMENTS table:
  - Index on (payment_date)     → Query by payment date
  - Index on (salary_sheet_id)  → Payments for sheet
  - Index on (employee_id)      → Employee payment history

ADVANCES table:
  - Index on (employee_id)      → Employee advances
  - Index on (date)             → Advances by month

ADVANCE_DEDUCTIONS table:
  - Index on (employee_id)      → Employee deductions
  - Index on (salary_sheet_id)  → Deductions per sheet
```

---

## Cascade Delete Behavior

```
DELETE Employee
  ├── Deletes all Attendance records
  ├── Deletes all Advance records
  │   └── Cascade deletes all AdvanceDeduction records
  ├── Deletes all SalarySheet records
  │   └── Cascade deletes all SalaryPayment records
  │   └── Cascade deletes all AdvanceDeduction records (if not already)
  └── Deletes all SalaryPayment records
  └── Deletes all AdvanceDeduction records
```

---

## Data Validation Rules

```
EMPLOYEES:
  - name: required, string
  - phone: required, unique, string
  - joining_date: required, date
  - hajira_rate: required, decimal > 0
  - overtime_rate: required, decimal > 0
  - status: enum(active, inactive)

ATTENDANCES:
  - date: required, date
  - hajira_type: enum(absent, one, one_half)
  - hajira_value: 0 or 1 or 1.5
  - (employee_id, date): unique

ADVANCES:
  - date: required, date
  - amount: required, decimal > 0

SALARY_SHEETS:
  - month: format YYYY-MM
  - status: enum(draft, locked, partial, paid)
  - (employee_id, month): unique

SALARY_PAYMENTS:
  - payment_date: required, date
  - amount: required, decimal > 0
  - payment_method: enum(cash, bank, mobile_banking)
```

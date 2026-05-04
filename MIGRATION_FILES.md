# Database Migration Files

## Migration Order & Dependencies

```
Migration Timeline (Execution Order):
├── 0001_01_01_000000_create_users_table.php (Laravel default)
├── 0001_01_01_000001_create_cache_table.php (Laravel default)
├── 0001_01_01_000002_create_jobs_table.php (Laravel default)
├── 2026_05_03_100140_create_personal_access_tokens_table.php (Sanctum)
│
├── 2026_05_03_100300_create_employees_table.php ✓ FOUNDATION TABLE
│   └─ No dependencies, runs first
│   └─ Creates employee master data
│
├── 2026_05_03_100301_create_attendances_table.php ✓
│   ├─ Depends on: employees
│   ├─ Depends on: users (for created_by, updated_by)
│   └─ Unique constraint: (employee_id, date)
│
├── 2026_05_03_100302_create_advances_table.php ✓
│   ├─ Depends on: employees
│   ├─ Depends on: users (for created_by)
│   └─ 1:Many relationship to AdvanceDeduction
│
├── 2026_05_03_100303_create_salary_sheets_table.php ✓
│   ├─ Depends on: employees
│   └─ Unique constraint: (employee_id, month)
│
├── 2026_05_03_100304_create_advance_deductions_table.php ✓
│   ├─ Depends on: advances
│   ├─ Depends on: employees
│   └─ Depends on: salary_sheets
│
└── 2026_05_03_100305_create_salary_payments_table.php ✓
    ├─ Depends on: salary_sheets
    ├─ Depends on: employees
    └─ Depends on: users (for created_by)
```

## File Details

### 1. Employees Table Migration
**File**: `2026_05_03_100300_create_employees_table.php`

```php
Schema::create('employees', function (Blueprint $table) {
    $table->id();                              // PK
    $table->string('name');
    $table->string('phone')->unique();         // Unique constraint
    $table->text('address')->nullable();
    $table->date('joining_date');
    $table->string('department')->nullable();
    $table->decimal('hajira_rate', 10, 2);    // Daily rate
    $table->decimal('overtime_rate', 10, 2);  // Hourly rate
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->timestamps();
    
    $table->index('status');
    $table->index('department');
});
```

---

### 2. Attendances Table Migration
**File**: `2026_05_03_100301_create_attendances_table.php`

```php
Schema::create('attendances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
    $table->date('date');
    $table->enum('hajira_type', ['absent', 'one', 'one_half'])->default('absent');
    $table->decimal('hajira_value', 3, 1)->default(0);      // 0, 1, or 1.5
    $table->decimal('overtime_hours', 5, 2)->default(0);
    $table->text('note')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
    
    $table->unique(['employee_id', 'date']);   // UNIQUE CONSTRAINT
    $table->index('date');
});
```

**Key Features**:
- `hajira_type` enum with three values
- `hajira_value` auto-calculated: 0 (absent), 1 (full), 1.5 (half)
- Unique constraint prevents duplicate records per employee per day
- Audit trail: created_by, updated_by

---

### 3. Advances Table Migration
**File**: `2026_05_03_100302_create_advances_table.php`

```php
Schema::create('advances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
    $table->date('date');
    $table->decimal('amount', 12, 2);
    $table->string('reason')->nullable();
    $table->text('note')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
    
    $table->index('employee_id');
    $table->index('date');
});
```

---

### 4. Salary Sheets Table Migration
**File**: `2026_05_03_100303_create_salary_sheets_table.php`

```php
Schema::create('salary_sheets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
    $table->string('month');                    // Format: YYYY-MM
    $table->decimal('total_hajira', 5, 2)->default(0);
    $table->decimal('total_overtime_hours', 8, 2)->default(0);
    $table->integer('absent_days')->default(0);
    $table->decimal('basic_amount', 12, 2)->default(0);
    $table->decimal('overtime_amount', 12, 2)->default(0);
    $table->decimal('advance_deducted', 12, 2)->default(0);
    $table->decimal('adjustment_amount', 12, 2)->default(0);
    $table->decimal('net_salary', 12, 2)->default(0);
    $table->decimal('paid_amount', 12, 2)->default(0);
    $table->decimal('due_amount', 12, 2)->default(0);
    $table->enum('status', ['draft', 'locked', 'partial', 'paid'])->default('draft');
    $table->timestamp('locked_at')->nullable();
    $table->timestamps();
    
    $table->unique(['employee_id', 'month']);   // UNIQUE CONSTRAINT
    $table->index('status');
    $table->index('month');
});
```

**Status Values**:
- `draft`: Editable, initial state
- `locked`: Finalized, cannot edit attendance for this month
- `partial`: Partial payment received
- `paid`: Fully paid

---

### 5. Advance Deductions Table Migration
**File**: `2026_05_03_100304_create_advance_deductions_table.php`

```php
Schema::create('advance_deductions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('advance_id')->constrained('advances')->onDelete('cascade');
    $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
    $table->foreignId('salary_sheet_id')->constrained('salary_sheets')->onDelete('cascade');
    $table->decimal('amount', 12, 2);
    $table->timestamps();
    
    $table->index('employee_id');
    $table->index('salary_sheet_id');
});
```

**Purpose**: Audit trail linking advances to specific salary sheets

---

### 6. Salary Payments Table Migration
**File**: `2026_05_03_100305_create_salary_payments_table.php`

```php
Schema::create('salary_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('salary_sheet_id')->constrained('salary_sheets')->onDelete('cascade');
    $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
    $table->date('payment_date');
    $table->decimal('amount', 12, 2);
    $table->enum('payment_method', ['cash', 'bank', 'mobile_banking'])->default('cash');
    $table->text('note')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
    
    $table->index('salary_sheet_id');
    $table->index('employee_id');
    $table->index('payment_date');
});
```

**Purpose**: Track individual payment transactions per salary sheet (supports partial payments)

---

## Foreign Key Relationships

### Cascade Delete (ON DELETE CASCADE)
```
employees → attendances
employees → advances
employees → salary_sheets
employees → salary_payments
employees → advance_deductions
advances → advance_deductions
salary_sheets → advance_deductions
salary_sheets → salary_payments
```

When an employee is deleted, all related records are automatically deleted.

### Set Null (ON DELETE SET NULL)
```
users → attendances.created_by
users → attendances.updated_by
users → advances.created_by
users → salary_payments.created_by
```

When a user is deleted, foreign key is set to NULL (preserves audit trail).

---

## Running Migrations

```bash
# Fresh migration (drops all tables and remigrates)
php artisan migrate:fresh

# Fresh migration with seed data
php artisan migrate:fresh --seed

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# See migration status
php artisan migrate:status

# Rollback specific migration
php artisan migrate:rollback --step=1
```

---

## Verifying Migrations

```bash
# Connect to MySQL
mysql -u root fms

# Show tables
SHOW TABLES;

# Describe each table
DESCRIBE employees;
DESCRIBE attendances;
DESCRIBE advances;
DESCRIBE salary_sheets;
DESCRIBE advance_deductions;
DESCRIBE salary_payments;

# Check constraints
SHOW CREATE TABLE attendances\G
SHOW CREATE TABLE salary_sheets\G
```

---

## Key Migration Features

### Timestamps
All tables include `created_at` and `updated_at` timestamps for audit trail.

### Indexes
Strategic indexes on foreign keys and frequently queried fields for performance:
- `employees(status, department)` - Filter active employees by department
- `attendances(date, employee_id)` - Query by date range
- `salary_sheets(status, month)` - Filter by payment status and month
- `salary_payments(payment_date)` - Query payment history

### Unique Constraints
- `attendances(employee_id, date)` - One record per employee per day
- `salary_sheets(employee_id, month)` - One salary sheet per employee per month
- `employees(phone)` - Unique phone numbers

### Enum Fields
- `attendances.hajira_type` - Limited to: 'absent', 'one', 'one_half'
- `salary_sheets.status` - Limited to: 'draft', 'locked', 'partial', 'paid'
- `salary_payments.payment_method` - Limited to: 'cash', 'bank', 'mobile_banking'
- `employees.status` - Limited to: 'active', 'inactive'

### Decimal Precision
- `hajira_rate`, `overtime_rate`: DECIMAL(10,2) - Up to 99,999,999.99
- `amounts` (salary, payment): DECIMAL(12,2) - Up to 9,999,999,999.99
- `hajira_value`: DECIMAL(3,1) - Values: 0, 1, 1.5

---

## Migration History

```
Created: 2026-05-03
Laravel Version: 13
MySQL: 8.0+
PHP: 8.2+

Total Tables: 6
Total Records Seeded: 102
Total Migration Files: 6
```

---

## Rollback Procedure

If you need to rollback and recreate:

```bash
# Option 1: Fresh migration
php artisan migrate:fresh --seed

# Option 2: Manual rollback
php artisan migrate:rollback
php artisan migrate:refresh --seed

# Option 3: Specific rollback
php artisan migrate:rollback --step=6
```

---

## Troubleshooting

### Foreign Key Constraint Error
```
SQLSTATE[HY000]: General error: 1025 Error on rename
```
**Solution**: Ensure parent tables are created first. Migrations run in sequence.

### Unique Constraint Violation
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
```
**Solution**: Clear database first: `php artisan migrate:fresh`

### Missing Table
**Solution**: 
```bash
php artisan migrate
php artisan migrate:status  # Check which migrations ran
```

---

## Performance Notes

- Indexes on foreign keys improve JOIN performance
- `status` index on salary_sheets speeds up filtering (draft/locked/paid)
- `date` index on attendances enables efficient date range queries
- Unique constraints prevent duplicates at database level (enforced)

---

## Backup & Restore

```bash
# Backup database
mysqldump -u root fms > fms_backup.sql

# Restore database
mysql -u root fms < fms_backup.sql

# Export specific table
mysqldump -u root fms employees > employees.sql

# Import table
mysql -u root fms < employees.sql
```

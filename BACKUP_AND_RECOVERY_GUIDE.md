# Hajira Payroll - Backup and Recovery Guide

## ⚠️ Important: Regular Backups

Your Hajira Payroll data is critical. Implement a regular backup strategy to protect against data loss.

---

## Backup Types

### 1. Database Backup (Critical)

The database contains all:
- Employee records
- Attendance data
- Salary information
- Payments
- Advances
- All configuration

**Weekly minimum recommended**

### 2. Full System Backup

Everything needed to run the system:
- Database
- Application code
- Uploaded files
- Configuration files

**Before major updates**

### 3. Manual Quick Backup

Export important reports and records:
- Current month salary sheet
- Payment history
- Employee list
- Attendance summary

**As needed for record-keeping**

---

## How to Backup on Linux/Mac (Server)

### Database-Only Backup

**Using mysqldump** (MySQL/MariaDB):

```bash
# Backup to file
mysqldump -u username -p database_name > backup.sql

# Example with password
mysqldump -u hajira -pYourPassword hajira_payroll > hajira_backup_$(date +%Y%m%d).sql

# Compressed backup (saves space)
mysqldump -u hajira -pYourPassword hajira_payroll | gzip > hajira_backup_$(date +%Y%m%d).sql.gz
```

**Using Laravel command** (Recommended):

```bash
# Create database dump
php artisan db:dump

# Dump specific tables
php artisan db:dump --compress
```

### Full System Backup

```bash
# Backup everything
tar -czf hajira_payroll_backup_$(date +%Y%m%d).tar.gz /path/to/project

# Example
cd /var/www
tar -czf hajira_backup_$(date +%Y%m%d).tar.gz html/fms/
```

### Automated Daily Backup (cron)

Create file: `/opt/backups/backup.sh`

```bash
#!/bin/bash

BACKUP_DIR="/opt/backups"
DB_NAME="hajira_payroll"
DB_USER="hajira"
DB_PASS="YourPassword"
DATE=$(date +%Y%m%d_%H%M%S)

# Create directory if doesn't exist
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 30 days of backups
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete

echo "Backup completed: db_$DATE.sql.gz"
```

Make executable and add to crontab:

```bash
chmod +x /opt/backups/backup.sh

# Add to crontab (runs at 2 AM daily)
crontab -e
# Add line: 0 2 * * * /opt/backups/backup.sh
```

---

## How to Backup on Windows

### Using MySQL Command Line

```batch
REM Open Command Prompt and run:
mysqldump -u hajira -p hajira_payroll > C:\backups\hajira_backup.sql

REM You'll be prompted for password
```

### Using Windows File Backup

1. Right-click project folder
2. Properties → Advanced
3. Enable version history or backup

### Scheduled Backup (Task Scheduler)

1. Open Task Scheduler
2. Create Basic Task
3. Name: "Hajira Daily Backup"
4. Trigger: Daily at 2:00 AM
5. Action: Run batch script

Create `C:\backups\backup.bat`:

```batch
@echo off
setlocal
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set mydate=%%c%%a%%b)
for /f "tokens=1-2 delims=/:" %%a in ('time /t') do (set mytime=%%a%%b)

mysqldump -u hajira -p[password] hajira_payroll > C:\backups\hajira_%mydate%_%mytime%.sql

echo Backup completed at %date% %time%
```

---

## How to Restore from Backup

### Database Restore

**From SQL dump**:

```bash
# Restore from uncompressed backup
mysql -u hajira -p hajira_payroll < hajira_backup_20260504.sql

# Restore from compressed backup
gunzip < hajira_backup_20260504.sql.gz | mysql -u hajira -p hajira_payroll

# Or decompress first
gzip -d hajira_backup_20260504.sql.gz
mysql -u hajira -p hajira_payroll < hajira_backup_20260504.sql
```

**Using Laravel**:

```bash
php artisan db:restore
```

### Full System Restore

```bash
# Extract backup
tar -xzf hajira_payroll_backup_20260504.tar.gz -C /var/www

# Fix permissions
chown -R www-data:www-data /var/www/fms
chmod -R 755 /var/www/fms/storage
```

### Restore Steps

1. **Stop the application**
   ```bash
   # Stop web server
   sudo systemctl stop nginx
   ```

2. **Restore database**
   ```bash
   mysql -u hajira -p hajira_payroll < backup.sql
   ```

3. **Restore files** (if needed)
   ```bash
   tar -xzf backup.tar.gz
   ```

4. **Run migrations** (if database structure changed)
   ```bash
   php artisan migrate
   ```

5. **Clear caches**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

6. **Start application**
   ```bash
   sudo systemctl start nginx
   ```

---

## Backup Storage Best Practices

### ✅ DO:

- ✅ Store backups on **different server** (offsite)
- ✅ Keep **multiple versions** (daily, weekly, monthly)
- ✅ Test restore process **monthly**
- ✅ Encrypt backups before sending offsite
- ✅ Document backup procedure
- ✅ Monitor backup completion
- ✅ Keep backups for at least 3 months

### ❌ DON'T:

- ❌ Store backups on same server (no protection from hardware failure)
- ❌ Keep only one backup (no redundancy)
- ❌ Never test restore (you won't know if backup works)
- ❌ Send unencrypted backups over internet
- ❌ Delete old backups immediately
- ❌ Store backups on removable media only

---

## Cloud Backup Options

### Amazon S3

```bash
# Install AWS CLI
pip install awscli

# Configure
aws configure

# Backup to S3
mysqldump -u hajira -p hajira_payroll | gzip | \
  aws s3 cp - s3://my-backups/hajira_$(date +%Y%m%d).sql.gz
```

### Google Cloud Storage

```bash
# Install gsutil
curl https://sdk.cloud.google.com | bash

# Backup to GCS
mysqldump -u hajira -p hajira_payroll | gzip | \
  gsutil cp - gs://my-backups/hajira_$(date +%Y%m%d).sql.gz
```

### Backblaze B2

```bash
# Install B2 CLI
pip install b2

# Authenticate
b2 authorize_account

# Backup
mysqldump -u hajira -p hajira_payroll | gzip | \
  b2 file-upload-stdin my-backup-bucket hajira_$(date +%Y%m%d).sql.gz
```

---

## Database Export to CSV/Excel

### Export Employees

```bash
# From terminal
mysql -u hajira -p hajira_payroll \
  -e "SELECT * FROM employees;" \
  --csv > employees.csv
```

### Export Salary Data

```bash
php artisan tinker

# Get salary sheet data
$salaries = SalarySheet::with('employee')->get();
$salaries->toArray()

# Or export to CSV programmatically
```

---

## Disaster Recovery Plan

### If Database Corrupted

1. Stop application immediately
2. Restore latest clean backup
3. Verify data integrity
4. Check activity logs for corruption source
5. Resume operations

### If Server Fails

1. Set up new server
2. Install dependencies (PHP, MySQL, etc.)
3. Deploy application code
4. Restore database backup
5. Configure web server
6. Test thoroughly
7. Switch DNS/IP

### If Ransomware Attack

1. **Disconnect** from network immediately
2. **Do NOT** pay ransom
3. Restore from clean backup
4. Review security logs
5. Update passwords
6. Patch vulnerabilities
7. Resume operations

---

## Verification Checklist

**Weekly:**
- [ ] Backup file created successfully
- [ ] Backup file size is reasonable
- [ ] No errors in backup log

**Monthly:**
- [ ] Test restore to test environment
- [ ] Verify data integrity after restore
- [ ] Check backup encryption
- [ ] Confirm offsite copy received

**Quarterly:**
- [ ] Full system restore test
- [ ] Document recovery time
- [ ] Update disaster plan
- [ ] Brief team on procedures

---

## Emergency Recovery - Quick Steps

### Immediate Data Loss

**Restore within minutes:**

```bash
# 1. Stop app
sudo systemctl stop nginx

# 2. Get latest backup
ls -lh /opt/backups/

# 3. Restore
mysql -u hajira -p hajira_payroll < /opt/backups/latest.sql

# 4. Clear caches
php artisan cache:clear

# 5. Restart
sudo systemctl start nginx

# 6. Verify
# Test key features in application
```

---

## Contact & Support

If you encounter issues:

1. **Database won't restore**
   - Verify MySQL is running
   - Check user permissions
   - Try smaller backup first

2. **Backup file corrupt**
   - Try older backup
   - Check disk space
   - Verify mysqldump version

3. **Missing data after restore**
   - Check backup date
   - Verify you restored correct database
   - Review activity logs for deletion source

---

## Backup Strategy Summary

| Frequency | Type | Location | Retention |
|-----------|------|----------|-----------|
| Daily | Database | Local server | 7 days |
| Daily | Database | Cloud storage | 30 days |
| Weekly | Full system | External drive | 12 weeks |
| Monthly | Full system | Cloud archive | 12 months |
| Before updates | Full system | Secure storage | Until after verification |

---

## Testing Your Backups

**Test restore process at least once per month:**

```bash
# Create test database
mysql -u root -p
> CREATE DATABASE hajira_payroll_test;
> EXIT;

# Restore to test database
mysql -u root -p hajira_payroll_test < latest_backup.sql

# Connect and verify
mysql -u root -p hajira_payroll_test
> SELECT COUNT(*) FROM employees;
> SELECT COUNT(*) FROM salary_sheets;
> EXIT;

# If all looks good, delete test database
mysql -u root -p
> DROP DATABASE hajira_payroll_test;
> EXIT;
```

---

**Last Updated**: May 4, 2026
**Frequency**: Review quarterly and after major updates
**Owner**: System Administrator
**Status**: Ready for production use

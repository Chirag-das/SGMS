# DEPLOYMENT START GUIDE
## Security Guard Management System (SGMS) v1.0

**Read this file first for quick setup!**

---

## ⚡ QUICK START (3 STEPS)

### Step 1: Database Setup (2 minutes)
```bash
# Open phpMyAdmin at: http://localhost/phpmyadmin

# 1. Create a new database named: sgms_db
# 2. Import file: config/schema.sql
# 3. Optionally import: config/sample_data.sql (for test data)
```

### Step 2: Verify Installation (1 minute)
```
Visit: http://localhost/dashboard/sgms/test.php
```
All checks should show "PASSED" (see green checkmarks)

### Step 3: Login & Start Using (1 minute)
```
URL: http://localhost/dashboard/sgms/
Username: admin
Password: admin@123
```

---

## 📋 PRE-DEPLOYMENT CHECKLIST

### ✓ Before Installing
- [ ] XAMPP or PHP 8.0+ installed
- [ ] MySQL/MariaDB 5.7+ running
- [ ] PHP mysqli extension enabled
- [ ] File permissions set correctly

### ✓ Database Setup
- [ ] Created database `sgms_db`
- [ ] Imported `config/schema.sql`
- [ ] Verified admin account exists (admin / admin@123)
- [ ] Test connection works

### ✓ File System
- [ ] `uploads/` exists and is writable
- [ ] `uploads/guards/` exists and is writable
- [ ] All PHP files present (verify with test.php)
- [ ] `.htaccess` file is configured

### ✓ Application Setup
- [ ] Database connection string is correct
- [ ] BASE_URL constant matches your installation
- [ ] Session directory is writable
- [ ] Error reporting configured for development

### ✓ Security
- [ ] Default admin password changed (IMPORTANT!)
- [ ] .htaccess properly configured
- [ ] test.php file removed or password-protected
- [ ] Database backups scheduled

### ✓ Features Verified
- [ ] Login page works
- [ ] Dashboard displays statistics
- [ ] Guard management (add/edit/delete) works
- [ ] Photo upload functions
- [ ] Salary calculation is accurate
- [ ] Reports generate correctly

---

## 🔧 COMMON SETUP ISSUES & SOLUTIONS

### Issue: "Database connection failed"
**Solution:**
1. Check MySQL is running (XAMPP MySQL button should be green)
2. Verify database `sgms_db` exists in phpMyAdmin
3. Verify credentials in `config/database.php` are correct
4. Default: localhost, port 3306, user: root, password: (empty)

### Issue: "Call to undefined function" error
**Solution:**
1. Verify all files are present using test.php
2. Check that `core/init.php` is properly included
3. Verify all model files exist in `models/`

### Issue: "Cannot upload files"
**Solution:**
1. Ensure `uploads/guards/` exists and is writable
2. Check file size doesn't exceed 5MB limit
3. Verify file format is jpg, jpeg, png, or gif
4. Check proper file permissions: `chmod 755 uploads/`

### Issue: "Permission Denied" error
**Solution:**
```bash
# On Linux/Mac:
chmod 755 uploads/
chmod 755 uploads/guards/
chmod 644 assets/css/style.css
chmod 644 assets/js/main.js

# On Windows: Right-click folder > Properties > Security > Edit Permissions
```

### Issue: "Blank page or 500 error"
**Solution:**
1. Check PHP error log in XAMPP
2. Verify PHP 8.0+ is installed
3. Check all required files exist (use test.php)
4. Verify MySQL is running

### Issue: "Session expires too quickly"
**Solution:**
Edit `config/constants.php`:
```php
define('SESSION_TIMEOUT', 3600); // Change 3600 to desired seconds
```

---

## 🚀 FIRST TIME SETUP WALKTHROUGH

### After successfully logging in (admin/admin@123):

**1. Update Admin Profile** (Recommended)
   - Click profile picture in top-right
   - Edit profile details
   - Change password (IMPORTANT!)

**2. Add a Client Company**
   - Click "Clients" in sidebar
   - Click "Add Client" button
   - Fill in: Name, Contact Person, Email, Phone, Address
   - Save

**3. Add a Site**
   - Click "Sites" in sidebar
   - Click "Add Site" button
   - Select the client you just created
   - Enter location details
   - Save

**4. Add Guards**
   - Click "Guards" in sidebar
   - Click "Add Guard" button
   - Fill in: Name, Email, Phone, Aadhaar, DOB, Salary
   - Select assigned site
   - Upload photo
   - Save

**5. Mark Attendance**
   - Click "Attendance" in sidebar
   - Click "Add Attendance" button
   - Select guard, date, and times
   - Save

**6. Generate Monthly Salary**
   - Click "Salary" in sidebar
   - Click "Generate Salary" button
   - Select guard and month
   - System auto-calculates based on attendance
   - Save

**7. View Reports**
   - Click "Reports" in sidebar
   - Select report type (Attendance/Salary/Performance)
   - Choose date range
   - Click Export to CSV or Print

---

## 📁 IMPORTANT FILES & LOCATIONS

```
Security Guard Management System/
├── config/
│   ├── database.php          # Database connection
│   ├── constants.php         # All system constants
│   ├── schema.sql           # Database structure (IMPORT THIS)
│   └── sample_data.sql      # Test data (optional import)
│
├── core/
│   ├── init.php             # Application bootstrap
│   ├── Database.php         # Database class
│   ├── Auth.php             # Authentication class
│   └── helpers.php          # Utility functions
│
├── app/
│   ├── models/              # Data access layer
│   │   ├── Guard.php
│   │   ├── Attendance.php
│   │   ├── Salary.php
│   │   ├── Client.php
│   │   └── Site.php
│   └── components/          # UI components
│   ├── components/          # UI components
│   │   ├── sidebar.php
│   │   └── topnav.php
│   └── init.php             # Application bootstrap
│
├── assets/
│   ├── css/style.css    # All styling
│   └── js/main.js       # Client-side functions
│
├── uploads/                 # Must be writable!
│   └── guards/              # Guard photos directory
│
├── models/                  # Data access layer
│   ├── Auth.php             # Authentication class
│   ├── Guard.php
│   ├── Attendance.php
│   ├── Salary.php
│   ├── Client.php
│   └── Site.php
│

├── *.php                    # All pages (login, dashboard, etc)
├── test.php                 # System health check (remove in production)
├── SETUP.md                 # Detailed setup guide
├── README.md                # Full documentation
└── QUICK_REFERENCE.md       # Code snippets reference
```

---

## 🔐 SECURITY RECOMMENDATIONS

### Before Going Live:
1. **Change Default Admin Password** ⚠️ CRITICAL
2. **Remove test.php file** or protect with .htaccess
3. **Enable HTTPS** - Uncomment lines in .htaccess
4. **Set proper file permissions** - 644 for files, 755 for directories
5. **Configure database backups** - Automated daily backups recommended
6. **Set strong database password** (currently blank for development)
7. **Configure firewall** - Only allow necessary ports
8. **Set SESSION_TIMEOUT** to appropriate value (currently 1 hour)
9. **Disable PHP error display** - Set in php.ini for production
10. **Regular security updates** - Keep PHP, MySQL, and libraries updated

### Database Backup (Linux/Mac):
```bash
# Backup
mysqldump -u root sgms_db > backup_$(date +%Y%m%d).sql

# Restore
mysql -u root sgms_db < backup_2026-02-17.sql
```

### Database Backup (Windows - using phpMyAdmin):
1. Open phpMyAdmin
2. Select database `sgms_db`
3. Click "Export" tab
4. Click "Go" button
5. Save SQL file

---

## 📞 SUPPORT & TROUBLESHOOTING

### Use the Health Check Page
Visit: `http://localhost/dashboard/sgms/test.php`

This page will verify:
- PHP version compatibility
- Database connection
- File permissions
- Required files presence
- MySQL extensions
- All 8 database tables

### Check Application Logs
- **PHP Errors**: XAMPP Apache error log
- **Database Errors**: MySQL error log
- **Session Errors**: PHP session directory (usually /tmp or c:\xampp\tmp)

### Restart Services
```bash
# On XAMPP application:
1. Stop Apache
2. Stop MySQL
3. Wait 5 seconds
4. Start MySQL
5. Start Apache
```

### Test Individual Components
- Database: Use phpMyAdmin to run test query
- PHP: Create test.php file with `<?php phpinfo(); ?>`
- Functions: Use QUICK_REFERENCE.md for function testing

---

## 🎯 NEXT STEPS (AFTER SETUP)

### Phase 1: Essential Setup (Week 1)
- [ ] Import database schema
- [ ] Create client companies
- [ ] Create guard locations (sites)
- [ ] Add guard profiles with photos


### Phase 2: Daily Operations (Ongoing)
- [ ] Mark daily attendance
- [ ] Monitor guard performance via dashboard
- [ ] Track site coverage and guard deployment

### Phase 3: Monthly Tasks
- [ ] Generate monthly salaries
- [ ] Process salary payments
- [ ] Generate attendance and salary reports
- [ ] Backup database
- [ ] Review system logs

### Phase 4: Maintenance (Quarterly)
- [ ] Review and optimize database
- [ ] Check security logs
- [ ] Update system configurations
- [ ] Plan infrastructure improvements

---

## 📚 DOCUMENTATION FILES

1. **SETUP.md** - Detailed setup instructions and API documentation
2. **README.md** - Full feature documentation and API examples
3. **QUICK_REFERENCE.md** - Code snippets and useful queries
4. **test.php** - Automatic system health checker
5. **config/schema.sql** - Database structure with comments

---

## 🎓 TRAINING RESOURCES

### For Administrators:
1. Login to system as admin
2. Explore each module in sidebar
3. Try adding test data
4. View sample reports
5. Check dashboard statistics

### For IT/Developers:
1. Review config files for settings
2. Study models in models/

4. Check CSS styling in assets/css/style.css
5. Review JavaScript functions in assets/js/main.js

### For End Users:
1. Learn to login and access dashboard
2. Practice marking attendance
3. Review personal salary records
4. Generate and export reports
5. Update own profile information

---

## ✅ SUCCESS VERIFICATION

Your SGMS is ready when:

- ✅ test.php shows all green checkmarks
- ✅ Login with admin/admin@123 works
- ✅ Dashboard displays with statistics and charts
- ✅ Can add a guard with photo upload
- ✅ Can mark attendance
- ✅ Can generate salary calculations
- ✅ Reports generate and export


---

## 📊 SYSTEM SPECIFICATIONS

| Component | Value |
|-----------|-------|
| Database | MySQL 5.7+ or MariaDB |
| PHP Version | 8.0 or higher |
| Bootstrap | 5.3.0 |
| Chart.js | 4.4.0 |
| Font Awesome | 6.4.0 |
| Session Timeout | 3600 seconds (1 hour) |
| Max Upload | 5 MB per file |
| Supported Formats | jpg, jpeg, png, gif |

---

## 🆘 GETTING HELP

When troubleshooting, include:
1. PHP version (`php -v`)
2. MySQL version (phpMyAdmin info page)
3. Error message (exact text from test.php)
4. Error log entries
5. Steps to reproduce issue

---

## 📝 VERSION HISTORY

**SGMS v1.0** (Current)
- Initial release with 6 core modules
- Authentication system with role-based access
- Complete CRUD operations for all modules

- Comprehensive reporting and analytics
- Responsive professional dashboard UI
- Complete documentation and setup guides

---

## 📄 LICENSE & TERMS

This Security Guard Management System is provided as-is. 
- ✓ Free to use and modify
- ✓ Can be deployed in production
- ✓ Can be customized for specific needs
- ✓ Support available via documentation

---

**Ready to deploy? Start with Step 1 above! 🚀**

For detailed setup help, see **SETUP.md**
For complete features, see **README.md**
For code examples, see **QUICK_REFERENCE.md**

---

**Last Updated:** February 2026
**System Version:** 1.0
**Status:** Production Ready ✓

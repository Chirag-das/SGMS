# Security Guard Management System - Setup Guide

## Quick Start Guide

### Step 1: Database Setup

1. **Open phpMyAdmin**
   - Navigate to: http://localhost/phpmyadmin
   - Create a new database named: `sgms_db`

2. **Import Database Schema**
   - In phpMyAdmin, select the `sgms_db` database
   - Go to "Import" tab
   - Choose file: `config/schema.sql`
   - Click "Import"

3. **Import Sample Data (Optional)**
   - Good for testing and demonstration
   - Go to "Import" tab again
   - Choose file: `config/sample_data.sql`
   - Click "Import"

### Step 2: File Permissions

Ensure proper permissions for upload folders:

```bash
# Windows/XAMPP - Ensure folders are writable
# Right-click folder → Properties → Security → Edit → Allow Full Control
```

### Step 3: Access the Application

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL

2. **Login to Application**
   - Open Browser: http://localhost/Security\ Guard\ Management\ System/
   - You'll be redirected to login page
   - Default Credentials:
     - Username: `admin`
     - Password: `admin@123`

3. **First Login**
   - Dashboard loads with sample data
   - Explore all modules
   - Add real data as needed

## Key Features Tour

### 1. Dashboard
- View overall statistics
- See today's attendance
- Recent guards list
- Attendance trends chart

### 2. Guard Management
- Add new guards with photo
- Edit guard details
- Delete guards
- Search guards by name/ID/phone
- View salary information
- Assign to sites

### 3. Clients & Sites
- Add client companies
- Manage site locations
- Track guards per site
- GPS coordinates for sites
- Update site information

### 4. Attendance
- Mark daily attendance
- Track check-in/check-out times
- Auto-calculate hours worked
- Detect overtime
- View attendance history
- Monthly attendance reports

### 5. Salary Management
- Generate monthly salary automatically
- Calculate based on:
  - Attendance (present/absent days)
  - Overtime hours
  - Bonus and deductions
- Mark salary as paid
- View payment history
- Download salary details

### 6. Reports
- Attendance reports with date range
- Salary reports with breakdown
- Performance summary
- Attendance percentage
- Export reports to CSV
- Print reports


## Common Tasks

### Create New Guard
1. Go to Guards Management
2. Click "Add New Guard"
3. Fill in all details
4. Upload photo (recommended)
5. Assign to site
6. Click "Add Guard"

### Mark Attendance
1. Go to Attendance
2. Click "Mark Attendance"
3. Select guard and date
4. Enter check-in/out times
5. Set status
6. Click "Mark Attendance"

### Generate Salary
1. Go to Salary Management
2. Click "Generate Salary"
3. Select guard and month
4. Add bonus/deductions if needed
5. Click "Generate Salary"
6. Mark as paid after processing

### View Reports
1. Go to Reports & Analytics
2. Select report type
3. Choose guard (optional)
4. Set date range
5. Click "Filter"
6. Export or print as needed

## Troubleshooting

### Problem: "Cannot connect to database"
**Solution:**
- Verify MySQL is running
- Check credentials in `config/database.php`
- Ensure `sgms_db` database exists

### Problem: "Upload fails"
**Solution:**
- Check folder permissions
- Ensure `uploads/guards` exists and is writable
- Maximum file size: 5MB

### Problem: "Session timeout immediately"
**Solution:**
- Check PHP session settings
- Clear browser cookies
- Restart browser

### Problem: "Blank page after login"
**Solution:**
- Check PHP error logs
- Verify all models are properly included
- Clear browser cache

## Database Backup

### Backup Database
1. Open phpMyAdmin
2. Select `sgms_db`
3. Go to "Export" tab
4. Choose "Quick" export
5. Save as SQL file

### Restore Database
1. Open phpMyAdmin
2. Create new database
3. Go to "Import" tab
4. Choose saved SQL file
5. Click "Import"

## Security Recommendations

1. **Change Default Password**
   - Login as admin
   - Change password immediately
   - Use strong password

2. **Create Additional Admins**
   - Create separate admin accounts for different managers
   - Use role-based permissions

3. **Regular Backups**
   - Backup database regularly
   - Store backups securely

4. **HTTPS Setup**
   - Configure SSL certificate
   - Uncomment HTTPS redirect in .htaccess

5. **File Permissions**
   - Restrict public folder access
   - Use appropriate file permissions

## Customization

### Change System Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary-color: #1e3c72;    /* Change this color */
    --primary-light: #2a5298;
    /* ... other colors ... */
}
```

### Change Company Details
Edit `config/constants.php`:
```php
define('APP_NAME', 'Your Company Name');
define('APP_DESCRIPTION', 'Your Description');
```

### Modify Salary Formula
Edit `models/Salary.php` in `calculateSalary()` method

## Support & Help

- Check README.md for detailed feature documentation
- Review code comments for implementation details

- Contact system administrator for issues

## Version History

- **v1.0.0** - Initial Release (Feb 17, 2026)
  - Complete SGMS system
  - All core modules
  - Professional UI


---

**System**: Security Guard Management System (SGMS)
**Version**: 1.0.0
**Date**: February 17, 2026

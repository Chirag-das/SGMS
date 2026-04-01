# Security Guard Management System (SGMS)

A comprehensive, professional web-based security guard management solution with modern responsive UI, built with HTML5, CSS3, JavaScript (Vanilla), Bootstrap 5, PHP 8+, and MySQL.

## 🎯 Features

### 1. **Admin Authentication**
- Secure login/logout with password hashing (bcrypt)
- Session management with timeout
- Role-based access control (Super Admin, Admin, Manager)
- Last login tracking

### 2. **Guard Management**
- Add/Edit/Delete guards
- Guard profiles with detailed information (Aadhaar, DOB, Photo, Salary)
- Site assignment
- Status tracking (Active/Inactive/On Leave)
- Photo upload functionality
- Employee ID auto-generation

### 3. **Client & Site Management**
- Add and manage client companies
- Manage multiple site locations
- Assign guards to sites
- Location tracking with GPS coordinates
- Contact information management

### 4. **Attendance Management**
- Real-time attendance marking
- Check-in/Check-out time tracking
- Hours worked & overtime calculation
- Monthly attendance reports
- Attendance status tracking (Present/Absent/Leave/Half-day)

### 5. **Salary Management**
- Auto-calculate salary based on attendance
- Support for fixed salary and per-day salary options
- Overtime pay calculation
- Deduction for absences
- Bonus management
- Payment tracking (Pending/Paid/Hold)
- Salary slip generation (ready for PDF export)
- Salary history records

### 6. **Reports & Analytics**
- Attendance reports (Daily/Monthly)
- Salary reports with detailed breakdown
- Guard performance summary
- Attendance percentage calculation
- Export to CSV functionality
- Print-friendly reports
- Date range filtering

### 7. **Professional Dashboard UI**
- Clean, modern corporate design
- Responsive layout (Desktop/Tablet/Mobile)
- Sidebar navigation with icons
- Top navigation bar with profile dropdown
- Statistics cards with data visualization
- Charts using Chart.js
- Real-time search functionality
- Pagination support

## 📋 Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **CSS Framework**: Bootstrap 5
- **Charts**: Chart.js 4.4.0
- **Backend**: PHP 8+
- **Database**: MySQL
- **Architecture**: Modular MVC Structure

## 🚀 Setup Instructions

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP (Recommended for local development)

### Installation Steps

1. **Clone/Extract the project**
   ```bash
   cd c:\xampp\htdocs\Security Guard Management System
   ```

2. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database schema from `config/schema.sql`
   ```sql
   -- Create database and import schema
   ```

3. **Configure Database Connection**
   - Edit `config/database.php`
   - Set your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'sgms_db');
   ```

4. **Set File Permissions**
   - Ensure `uploads` directory is writable
   - Ensure `uploads/guards` directory is writable

5. **Access the Application**
   - Open browser and navigate to: `http://localhost/Security Guard Management System/`
   - Default login credentials:
     - Username: `admin`
     - Password: `admin@123`

## 📁 Project Structure

```
Security\ Guard\ Management\ System/
├── app/
│   ├── controllers/           # Business logic controllers
│   ├── models/               # Database models
│   ├── components/           # Reusable UI components
│   └── views/                # View templates

├── config/
│   ├── database.php          # Database configuration
│   ├── constants.php         # Application constants
│   └── schema.sql            # Database schema
├── core/
│   ├── Database.php          # Database class
│   ├── Auth.php              # Authentication class
│   ├── helpers.php           # Helper functions
│   └── init.php              # Application bootstrap
├── assets/
├── uploads/
│   ├── assets/
│   │   ├── css/style.css     # Main stylesheet
│   │   ├── js/main.js        # Main JavaScript
│   │   ├── images/           # Image assets
│   │   └── icons/            # Icon assets
│   ├── uploads/
│   │   └── guards/           # Guard photos
│   ├── salary.php            # Salary management
│   ├── reports.php           # Reports & analytics
│   └── logout.php            # Logout page
└── README.md                 # This file
```



## 💰 Salary Calculation Formula

The system automatically calculates salary based on:

```
Basic Salary = Per Day Rate × Present Days

Overtime Allowance = (Per Day Rate × 1.5) × Overtime Hours

Total Deductions = (Per Day Rate × 0.5) × Absent Days + Other Deductions

Final Salary = Basic Salary + Overtime Allowance + Bonus - Total Deductions
```

### Example:
- Monthly Salary: ₹20,000
- Per Day Rate: ₹769.23 (20000 ÷ 26)
- Present Days: 25
- Absent Days: 1
- Overtime Hours: 3
- Overtime Rate: 1.5x

```
Basic: 25 × ₹769.23 = ₹19,230.75
Overtime: (₹769.23 × 1.5) × 3 = ₹3,461.54
Deductions: (₹769.23 × 0.5) × 1 = ₹384.62
Final: ₹19,230.75 + ₹3,461.54 - ₹384.62 = ₹22,307.67
```

## 🗄️ Database Schema

### Main Tables:

1. **admins**: Admin user accounts
2. **guards**: Guard information and assignment
3. **clients**: Client companies
4. **sites**: Guard assignment sites
5. **attendance**: Daily attendance records
6. **salaries**: Monthly salary calculations
7. **leaves**: Leave request tracking
8. **audit_logs**: System activity logging

## 🎨 UI/UX Features

- **Professional Color Scheme**: Navy Blue (#1e3c72), White, Light Gray
- **Responsive Design**: Fully responsive on all devices
- **Modal Forms**: Clean, modal-based forms for data entry
- **Data Tables**: Advanced tables with search and pagination
- **Charts**: Visual data representation with Chart.js
- **Notifications**: Toast-style notifications for user feedback
- **Loading States**: Visual feedback for long operations
- **Accessibility**: Semantic HTML, ARIA labels, keyboard navigation

## 🔐 Security Features

- **Password Hashing**: bcrypt for secure password storage
- **Session Management**: Secure session handling with timeout
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Tokens**: Token-based CSRF protection (ready for implementation)
- **Role-Based Access**: Different permission levels

## 📊 Key Metrics

### Dashboard Statistics:
- Total Guards (Active/Inactive)
- Today's Present Count
- Total Clients
- Pending Salary Payments
- Overtime Summary
- Attendance Trends

### Reports Available:
- Monthly Attendance Report
- Salary Report with Breakdown
- Guard Performance Summary
- Overtime Analysis
- Attendance Percentage

## 🔄 Usage Workflows

### Guard Management Workflow:
1. Add new guard with details
2. Upload guard photo
3. Assign to site
4. Track attendance
5. Process monthly salary
6. Generate salary slip

### Attendance Workflow:
1. Admin manually marks attendance via dashboard
2. Records attendance with chosen date
3. Status updated to Present/Absent/Leave

### Salary Workflow:
1. Attendance data collected for the month
2. Admin generates monthly salary
3. System auto-calculates based on attendance
4. Optional bonuses/deductions added
5. Salary marked as paid
6. Salary slip available for download

## ⚙️ Configuration

### Important Settings (config/constants.php):
```php
// Salary Settings
define('OVERTIME_RATE', 1.5);                    // 1.5x base rate
define('ATTENDANCE_ABSENT_DEDUCTION', 0.5);     // 50% deduction for absence

// Session Settings
define('SESSION_TIMEOUT', 3600);                // 1 hour (in seconds)
define('REMEMBER_ME_TIME', 30 * 24 * 60 * 60); // 30 days

// File Upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);    // 5MB
```

## 🐛 Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check database credentials in `config/database.php`
- Ensure `sgms_db` database exists

### Upload Error
- Check `uploads` and `uploads/guards` permissions
- Ensure uploads folder is writable (chmod 755)

### Login Issues
- Clear browser cookies
- Verify admin account exists in database
- Check PHP session settings

## 🚀 Future Enhancements

- PDF export for salary slips
- Email notifications for attendance/salary
- SMS alerts for guards
- Advanced attendance analytics
- Performance metrics dashboard
- Leave request management UI
- Multi-language support
- Dark mode theme
- Mobile app integration
- Real-time notifications

## 📝 License

This project is provided as-is for commercial and personal use.

## 👤 Support

For issues, questions, or feature requests, please contact the system administrator.

---

**Version**: 1.0.0  
**Last Updated**: February 17, 2026  
**Built with**: HTML5, CSS3, JavaScript, PHP 8+, MySQL

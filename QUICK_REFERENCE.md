// QUICK REFERENCE GUIDE - SGMS
// Useful code snippets and quick references

// ===== DATABASE QUERIES REFERENCE =====

// Get all guards with their assigned sites
SELECT g.*, s.name as site_name 
FROM guards g 
LEFT JOIN sites s ON g.assigned_site_id = s.id 
WHERE g.status = 'active';

// Get monthly attendance summary for a guard
SELECT 
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
    SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) as leave_days,
    SUM(COALESCE(overtime_hours, 0)) as total_overtime
FROM attendance 
WHERE guard_id = ? 
AND YEAR(date) = YEAR(CURDATE())
AND MONTH(date) = MONTH(CURDATE());

// Get salary history for a guard
SELECT * FROM salaries 
WHERE guard_id = ? 
ORDER BY year_month DESC 
LIMIT 12;

// Get all pending salaries
SELECT s.*, g.full_name, g.employee_id 
FROM salaries s 
JOIN guards g ON s.guard_id = g.id 
WHERE s.payment_status = 'pending';

// ===== PHP FUNCTION QUICK REFERENCE =====

// Format currency
formatCurrency(15000); // Output: 15000.00 INR

// Format date
formatDate('2026-02-17'); // Output: Feb 17, 2026

// Calculate hours
calculateHours('09:00:00', '17:00:00'); // Output: 8 (hours)

// Calculate attendance percentage
getAttendancePercentage(24, 26); // Output: 92.31 (%)

// Format hours to readable format
formatHours(8.5); // Output: 8h 30m

// Calculate age from DOB
calculateAge('1990-05-15'); // Output: 35 (age)


// ===== FILE UPLOAD PATHS =====

// Guard Photos
// Location: uploads/guards/
// Max Size: 5MB
// Allowed: jpg, jpeg, png, gif

// ===== SESSION VARIABLES =====

// After login, these are available
$_SESSION['admin_id']        // Admin user ID
$_SESSION['username']        // Login username
$_SESSION['email']          // Admin email
$_SESSION['full_name']      // Admin full name
$_SESSION['role']           // Role (super_admin, admin, manager)
$_SESSION['photo']          // Admin photo path
$_SESSION['login_time']     // Last activity time

// ===== AUTHENTICATION USAGE =====

// In your PHP files:
require_once '../../core/init.php';

// Check if logged in
$auth->requireLogin();

// Check if admin
$auth->requireAdmin();

// Manually login
$auth->login($username, $password);

// Logout
$auth->logout();

// Get current user
$user = $auth->getUser();

// ===== DATABASE OPERATIONS =====

// Using Database class
$db = new Database($conn);

// Query
$result = $db->query("SELECT * FROM guards WHERE status = 'active'");
$guards = $result->resultSet();

// Single row
$guard = $db->findById('guards', 1);

// Count
$total = $db->count('guards');

// ===== MODEL USAGE =====

// Initialize guard model
$guardModel = new Guard($conn);

// Get all guards
$guards = $guardModel->getAll(10, 0); // limit 10, offset 0

// Get by ID
$guard = $guardModel->getById(1);

// Create guard
$guardModel->create([
    'employee_id' => 'G202100001',
    'full_name' => 'John Doe',
    'phone' => '9876543210',
    // ... other fields
]);

// Update guard
$guardModel->update(1, [
    'full_name' => 'Jane Doe',
    'phone' => '9876543211'
]);

// Delete guard
$guardModel->delete(1);

// Search guards
$results = $guardModel->search('John', 10);

// Count guards
$total = $guardModel->count();

// ===== CONSTANTS & SETTINGS =====

// From config/constants.php
BASE_URL                         // Base URL of application
ASSETS_URL                       // Assets URL
GUARDS_UPLOADS_PATH             // Guards uploads directory
SESSION_TIMEOUT                 // Session timeout in seconds
RECORDS_PER_PAGE                // Records per page in pagination
OVERTIME_RATE                   // Overtime multiplier (1.5)
ATTENDANCE_ABSENT_DEDUCTION     // Absence deduction rate (0.5)

// Status values
STATUS_ACTIVE                   // 'active'
STATUS_INACTIVE                 // 'inactive'
STATUS_PRESENT                  // 'present'
STATUS_ABSENT                   // 'absent'
STATUS_LEAVE                    // 'leave'

// ===== HTML/JS UTILITIES =====

// JavaScript functions available in assets/js/main.js

// Show alert
showAlert('Message', 'info');           // info, success, danger, warning

// Export table to CSV
exportTableToCSV('filename', 'tableId');

// Print table
printTable('tableId');

// Setup table search
setupTableSearch('searchInputId', 'tableId');

// Format currency (JS)
formatCurrency(15000, 'INR');

// Format date (JS)
formatDate('2026-02-17');

// Validate email
validateEmail('test@example.com');

// Validate phone
validatePhone('9876543210');

// ===== CREATING NEW PAGES =====

// Basic page template:

<?php
require_once '../../core/init.php';

// Require login
$auth->requireLogin();

$user = $auth->getUser();

// Include your logic here

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title - SGMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'components/sidebar.php'; ?>
            <div class="col-md-9 col-lg-10 main-content">
                <?php include 'components/topnav.php'; ?>
                <div class="dashboard-content">
                    <!-- Your content here -->
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
</body>
</html>

// ===== FORM VALIDATION EXAMPLE =====

// HTML Form
<form method="POST">
    <div class="mb-3">
        <label class="form-label">Email *</label>
        <input type="email" class="form-control" name="email" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Phone *</label>
        <input type="tel" class="form-control" name="phone" pattern="[0-9]{10}" required>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

// PHP Validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    
    if (empty($email) || !validateEmail($email)) {
        $error = 'Invalid email';
    } elseif (empty($phone) || !validatePhone($phone)) {
        $error = 'Invalid phone number';
    } else {
        // Process form
    }
}

// ===== PAGINATION USAGE =====

// In your controller:
$total = $model->count();
$pagination = getPagination($total);

// In your view:
<?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
    <a href="?page=<?php echo $i; ?>">Page <?php echo $i; ?></a>
<?php endfor; ?>

// ===== SALARY CALCULATION EXAMPLE =====

$guard_data = $guardModel->getById(1);
$salary_data = $salaryModel->calculateSalary(1, '2026-02', $guard_data);

// Returns:
// [
//   'present_days' => 24,
//   'absent_days' => 1,
//   'leave_days' => 1,
//   'half_day_count' => 0,
//   'overtime_hours' => 12.5,
//   'basic_salary' => 18461.54,
//   'overtime_allowance' => 1807.69,
//   'deductions' => 384.62,
//   'net_salary' => 19884.61
// ]

// ===== RECOMMENDED PRACTICES =====

1. Always use sanitize() for user input
2. Always use prepared statements for queries
3. Always check authentication before processing
4. Always validate email and phone numbers
5. Always use proper error handling
6. Always log important actions
7. Always backup database regularly
8. Always use HTTPS in production
9. Always keep passwords hashed
10. Always follow MVC pattern

// ===== USEFUL LINUX/UNIX COMMANDS =====

// Set proper permissions
chmod 755 uploads/
chmod 755 uploads/guards/

// Create database backup
mysqldump -u root -p sgms_db > backup.sql

// Restore database
mysql -u root -p sgms_db < backup.sql

// Tail PHP error log
tail -f /var/log/php-errors.log

// ===== USEFUL WINDOWS COMMANDS =====

// Start Apache (XAMPP)
C:\xampp\apache_start.bat

// Start MySQL (XAMPP)
C:\xampp\mysql_start.bat

// List directory (equivalent to ls)
dir

// Copy file
copy source.txt destination.txt

// ===== VERSION INFORMATION =====

PHP: 8.0+
MySQL: 5.7+
Bootstrap: 5.3.0
Chart.js: 4.4.0
Font Awesome: 6.4.0

// ===== SUPPORT & CONTACT =====

For issues: Contact system administrator
For features: Create a pull request
For bugs: Report in issue tracker

// ===== FINAL CHECKLIST =====

✓ Database created and schema imported
✓ File permissions set correctly
✓ Application accessible at http://localhost
✓ Login with default credentials works
✓ Dashboard displays without errors
✓ All modules accessible
✓ Sample data loaded (if tested)
✓ Upload folders working

✓ Reports generating correctly


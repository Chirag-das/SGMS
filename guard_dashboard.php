<?php
require_once 'core/init.php';

// Require user to be logged in
$auth->requireLogin();

$user = $auth->getUser();

// Check if guard ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'guards.php');
    exit;
}

$guard_id = (int)$_GET['id'];

// Initialize models
$guardModel = new Guard($conn);
$attendanceModel = new Attendance($conn);
$salaryModel = new Salary($conn);

// Get guard details
$guard = $guardModel->getById($guard_id);

if (!$guard) {
    // Guard not found
    header('Location: ' . BASE_URL . 'guards.php');
    exit;
}

// Get recent attendance (last 30 days)
$recent_attendance = $attendanceModel->getDateRangeAttendance($guard_id, date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));

// Get summary for current month
$attendance_summary = $attendanceModel->getAttendanceSummary($guard_id, date('Y-m'));

// Get recent salaries (last 6 months)
$recent_salaries = $salaryModel->getGuardSalaryHistory($guard_id, 6);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guard Dashboard - Security Guard Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'app/components/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Navigation -->
                <?php include 'app/components/topnav.php'; ?>

                <!-- Page Content -->
                <div class="dashboard-content">
                    <div class="page-header d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="page-title">Guard Dashboard</h1>
                            <p class="page-subtitle">Detailed Profile and Activity for <?php echo htmlspecialchars($guard['full_name']); ?></p>
                        </div>
                        <a href="guards.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Guards
                        </a>
                    </div>

                    <div class="row g-4">
                        <!-- Personal Info Card -->
                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fas fa-user border-0 text-primary me-2"></i> Personal Details</h6>
                                </div>
                                <div class="card-body text-center mt-3">
                                    <?php if ($guard['photo']): ?>
                                        <img src="<?php echo ASSETS_URL; ?>../uploads/guards/<?php echo $guard['photo']; ?>" class="rounded-circle mb-3 border shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                                    <?php
else: ?>
                                        <div class="rounded-circle bg-light d-inline-flex justify-content-center align-items-center mb-3 border shadow-sm" style="width: 120px; height: 120px;">
                                            <i class="fas fa-user-circle fa-4x text-secondary"></i>
                                        </div>
                                    <?php
endif; ?>
                                    
                                    <h4 class="mb-1"><?php echo htmlspecialchars($guard['full_name']); ?></h4>
                                    <p class="text-muted mb-2">ID: <?php echo htmlspecialchars($guard['employee_id']); ?></p>
                                    <span class="badge bg-<?php echo $guard['status'] === 'active' ? 'success' : 'secondary'; ?> mb-4">
                                        <?php echo ucfirst(str_replace('_', ' ', $guard['status'])); ?>
                                    </span>
                                    
                                    <ul class="list-group list-group-flush text-start">
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="fas fa-phone text-muted me-2"></i> Phone:</span>
                                            <strong><?php echo htmlspecialchars($guard['phone']); ?></strong>
                                        </li>
                                        <?php if ($guard['email']): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="fas fa-envelope text-muted me-2"></i> Email:</span>
                                            <strong><?php echo htmlspecialchars($guard['email']); ?></strong>
                                        </li>
                                        <?php
endif; ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="fas fa-id-card text-muted me-2"></i> Aadhaar:</span>
                                            <strong><?php echo htmlspecialchars($guard['aadhaar_number'] ?? 'N/A'); ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                             <span><i class="fas fa-building text-muted me-2"></i> Assigned Site:</span>
                                             <?php if (!empty($guard['assigned_site_id'])): ?>
                                                 <a href="site_dashboard.php?id=<?php echo $guard['assigned_site_id']; ?>" class="fw-bold text-decoration-none">
                                                     <?php echo htmlspecialchars($guard['site_name']); ?>
                                                 </a>
                                             <?php else: ?>
                                                 <strong>Not Assigned</strong>
                                             <?php endif; ?>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="fas fa-calendar-alt text-muted me-2"></i> Joining Date:</span>
                                            <strong><?php echo $guard['joining_date'] ? date('M d, Y', strtotime($guard['joining_date'])) : 'N/A'; ?></strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics & Data -->
                        <div class="col-lg-8">
                            
                            <!-- Monthly Attendance Summary -->
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6 col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body py-3 text-center">
                                            <h6 class="card-title fw-normal mb-1">Present (Month)</h6>
                                            <h3 class="mb-0"><?php echo isset($attendance_summary['present_days']) ? $attendance_summary['present_days'] : '0'; ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body py-3 text-center">
                                            <h6 class="card-title fw-normal mb-1">Absent (Month)</h6>
                                            <h3 class="mb-0"><?php echo isset($attendance_summary['absent_days']) ? $attendance_summary['absent_days'] : '0'; ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="card bg-warning text-dark">
                                        <div class="card-body py-3 text-center">
                                            <h6 class="card-title fw-normal mb-1">Leave (Month)</h6>
                                            <h3 class="mb-0"><?php echo isset($attendance_summary['leave_days']) ? $attendance_summary['leave_days'] : '0'; ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body py-3 text-center">
                                            <h6 class="card-title fw-normal mb-1">Overtime Hrs</h6>
                                            <h3 class="mb-0"><?php echo isset($attendance_summary['total_overtime_hours']) ? $attendance_summary['total_overtime_hours'] : '0'; ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <!-- Recent Attendance List -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0">Recent Attendance (30 Days)</h6>
                                            <a href="attendance.php?guard_id=<?php echo $guard['id']; ?>" class="btn-link text-decoration-none">View All</a>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Status</th>
                                                            <th>In / Out</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($recent_attendance)): ?>
                                                            <?php foreach (array_slice($recent_attendance, 0, 7) as $att): ?>
                                                            <tr>
                                                                <td><?php echo date('M d, Y', strtotime($att['date'])); ?></td>
                                                                <td>
                                                                    <span class="badge bg-<?php
        if ($att['status'] === 'present')
            echo 'success';
        elseif ($att['status'] === 'absent')
            echo 'danger';
        else
            echo 'warning';
?>">
                                                                        <?php echo ucfirst(str_replace('_', ' ', $att['status'])); ?>
                                                                    </span>
                                                                </td>
                                                                <td class="text-muted small">
                                                                    <?php
        if ($att['check_in_time']) {
            echo date('H:i', strtotime($att['check_in_time']));
            if ($att['check_out_time']) {
                echo ' - ' . date('H:i', strtotime($att['check_out_time']));
            }
        }
        else {
            echo '--';
        }
?>
                                                                </td>
                                                            </tr>
                                                            <?php
    endforeach; ?>
                                                        <?php
else: ?>
                                                            <tr>
                                                                <td colspan="3" class="text-center py-3 text-muted">No recent attendance records found.</td>
                                                            </tr>
                                                        <?php
endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recent Salary History -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0">Salary History</h6>
                                            <a href="salary.php?guard_id=<?php echo $guard['id']; ?>" class="btn-link text-decoration-none">View All</a>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Month</th>
                                                            <th>Net Salary</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($recent_salaries)): ?>
                                                            <?php foreach ($recent_salaries as $sal): ?>
                                                            <tr>
                                                                <td><?php echo date('M Y', strtotime($sal['year_month'] . '-01')); ?></td>
                                                                <td><strong>₹<?php echo number_format($sal['net_salary'], 2); ?></strong></td>
                                                                <td>
                                                                    <span class="badge bg-<?php echo $sal['payment_status'] === 'paid' ? 'success' : ($sal['payment_status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                                        <?php echo ucfirst($sal['payment_status']); ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <?php
    endforeach; ?>
                                                        <?php
else: ?>
                                                            <tr>
                                                                <td colspan="3" class="text-center py-3 text-muted">No salary records found.</td>
                                                            </tr>
                                                        <?php
endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address Details -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fas fa-map-marker-alt text-danger me-2"></i> Address Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Street Address</small>
                                            <span><?php echo htmlspecialchars($guard['address'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">City</small>
                                            <span><?php echo htmlspecialchars($guard['city'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">State</small>
                                            <span><?php echo htmlspecialchars($guard['state'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Pincode</small>
                                            <span><?php echo htmlspecialchars($guard['pincode'] ?? 'N/A'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
</body>
</html>

<?php
require_once 'core/init.php';

// Require guard to be logged in
$auth->requireGuardLogin();

$guard_id = $_SESSION['guard_id'];

// Initialize models
$guardModel = new Guard($conn);
$leaveModel = new Leave($conn);
$attendanceModel = new Attendance($conn);
$salaryModel = new Salary($conn);
$siteModel = new Site($conn);

// Get guard details
$guard = $guardModel->getById($guard_id);

// Handle Leave Request Submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_leave') {
    // Calculate number of days
    $start = new DateTime($_POST['start_date']);
    $end = new DateTime($_POST['end_date']);
    $interval = $start->diff($end);
    $no_of_days = $interval->days + 1; // Inclusive

    $data = [
        'guard_id' => $guard_id,
        'leave_type' => sanitize($_POST['leave_type']),
        'start_date' => sanitize($_POST['start_date']),
        'end_date' => sanitize($_POST['end_date']),
        'no_of_days' => $no_of_days,
        'reason' => sanitize($_POST['reason'])
    ];

    if ($leaveModel->create($data)) {
        $message = '<div class="alert alert-success mt-3">Leave request submitted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger mt-3">Failed to submit leave request.</div>';
    }
}

// Get guard's leave history
$leaves = $leaveModel->getGuardLeaves($guard_id);

// Get summary for current month
$attendance_summary = $attendanceModel->getAttendanceSummary($guard_id, date('Y-m'));

// Get salary history
$salary_history = $salaryModel->getGuardSalaryHistory($guard_id);

// Get site details if assigned
$site_details = null;
if (!empty($guard['assigned_site_id'])) {
    $site_details = $siteModel->getById($guard['assigned_site_id']);
}

// Get upcoming shifts
$shiftModel = new Shift($conn);
$upcoming_shifts = $shiftModel->getGuardShifts($guard_id, date('Y-m-d'), date('Y-m-d', strtotime('+30 days')));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guard Portal - <?php echo htmlspecialchars($guard['full_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #3730a3;
            --bg-body: #f1f5f9;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Portal Header */
        .portal-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            padding: 60px 0 80px 0;
            position: relative;
            overflow: hidden;
        }

        .portal-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -10%;
            width: 50%;
            height: 200%;
            background: rgba(255, 255, 255, 0.05);
            transform: rotate(30deg);
            pointer-events: none;
        }

        .profile-image-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .profile-img:hover {
            transform: scale(1.05);
        }

        .badge-glass {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 500;
            margin: 5px;
        }

        /* Main Content Offset */
        .main-content {
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }

        /* Cards */
        .premium-card {
            background: var(--card-bg);
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            overflow: hidden;
        }

        .premium-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        .card-header-premium {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 20px 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .card-header-premium i {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin-right: 15px;
        }

        .card-body-premium {
            padding: 25px;
        }

        /* Stat Boxes */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .stat-icon.present { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .stat-icon.absent { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .stat-icon.leave { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .stat-icon.overtime { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }

        .stat-details h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .stat-details p {
            color: var(--text-muted);
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: 500;
        }

        /* Tables */
        .premium-table {
            margin: 0;
        }

        .premium-table th {
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 2px solid #f1f5f9;
            padding: 15px 20px;
            background: #f8fafc;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .premium-table td {
            padding: 15px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-main);
        }

        .premium-table tbody tr {
            transition: background 0.2s;
        }

        .premium-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        
        .badge-soft-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .badge-soft-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .badge-soft-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .badge-soft-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }

        /* Form Controls */
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: #f8fafc;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            background-color: white;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .btn-premium {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-premium:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
            color: white;
        }

        .custom-alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="portal-header">
        <div class="container text-center">
            <div class="profile-image-container">
                <?php if ($guard['photo']): ?>
                    <img src="<?php echo BASE_URL; ?>uploads/guards/<?php echo $guard['photo']; ?>" class="profile-img" alt="Profile">
                <?php else: ?>
                    <div class="profile-img bg-white text-primary d-inline-flex align-items-center justify-content-center">
                        <i class="fas fa-user-shield fa-4x"></i>
                    </div>
                <?php endif; ?>
            </div>
            <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($guard['full_name']); ?></h1>
            <p class="mb-4 text-light opacity-75">Personal Guard Dashboard</p>
            
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <span class="badge-glass"><i class="fas fa-building me-2"></i><?php echo htmlspecialchars($guard['site_name'] ?? 'Unassigned'); ?></span>
                <span class="badge-glass"><i class="fas fa-id-badge me-2"></i>ID: <?php echo htmlspecialchars($guard['employee_id']); ?></span>
            </div>
            
            <div class="mt-4">
                <a href="logout.php" class="btn btn-outline-light rounded-pill px-4 py-2" style="border-width: 2px;">
                    <i class="fas fa-sign-out-alt me-2"></i> Secure Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="container main-content">
        <?php if($message): ?>
            <div class="mb-4">
                <?php echo str_replace('alert', 'alert custom-alert', $message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats Row -->
        <div class="stat-grid">
            <div class="stat-box">
                <div class="stat-icon present"><i class="fas fa-check-circle"></i></div>
                <div class="stat-details">
                    <h3><?php echo $attendance_summary['present_days'] ?? 0; ?></h3>
                    <p>Present Days</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon absent"><i class="fas fa-times-circle"></i></div>
                <div class="stat-details">
                    <h3><?php echo $attendance_summary['absent_days'] ?? 0; ?></h3>
                    <p>Absent Days</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon leave"><i class="fas fa-calendar-minus"></i></div>
                <div class="stat-details">
                    <h3><?php echo $attendance_summary['leave_days'] ?? 0; ?></h3>
                    <p>On Leave</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon overtime"><i class="fas fa-clock"></i></div>
                <div class="stat-details">
                    <h3><?php echo $attendance_summary['total_overtime_hours'] ?? 0; ?></h3>
                    <p>OT Hours</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Left Column: Site Info & Leaves -->
            <div class="col-lg-6">
                <!-- Site Details -->
                <div class="premium-card mb-4">
                    <div class="card-header-premium">
                        <i class="fas fa-map-marked-alt"></i>
                        <h5 class="mb-0">Assigned Site Information</h5>
                    </div>
                    <div class="card-body-premium">
                        <?php if ($site_details): ?>
                            <div class="bg-light rounded-4 p-4 mb-0">
                                <h4 class="fw-bold text-primary mb-3"><?php echo htmlspecialchars($site_details['site_name']); ?></h4>
                                <div class="d-flex align-items-start mb-3">
                                    <i class="fas fa-map-marker-alt text-muted mt-1 me-3"></i>
                                    <div>
                                        <span class="d-block text-dark"><?php echo htmlspecialchars($site_details['site_address_line1']); ?></span>
                                        <?php if($site_details['site_address_line2']): ?>
                                            <span class="d-block text-dark"><?php echo htmlspecialchars($site_details['site_address_line2']); ?></span>
                                        <?php endif; ?>
                                        <span class="d-block text-muted"><?php echo htmlspecialchars($site_details['city']); ?>, <?php echo htmlspecialchars($site_details['state']); ?> - <?php echo htmlspecialchars($site_details['pincode']); ?></span>
                                    </div>
                                </div>
                                <div class="row mt-4 pt-3 border-top border-secondary-subtle">
                                    <div class="col-6">
                                        <p class="text-muted mb-1 small text-uppercase fw-bold">Supervisor</p>
                                        <span class="fw-semibold text-dark"><i class="fas fa-user-tie me-2 text-primary"></i><?php echo htmlspecialchars($site_details['site_supervisor'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="col-6 border-start border-secondary-subtle">
                                        <p class="text-muted mb-1 small text-uppercase fw-bold">Contact</p>
                                        <span class="fw-semibold text-dark"><i class="fas fa-phone-alt me-2 text-primary"></i><?php echo htmlspecialchars($site_details['supervisor_contact'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-info-circle text-muted fa-2x"></i>
                                </div>
                                <h5 class="text-dark">No Site Assigned</h5>
                                <p class="text-muted mb-0">You are currently placed on reserve. Please contact your administrator for deployment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Leave Request Form -->
                <div class="premium-card">
                    <div class="card-header-premium">
                        <i class="fas fa-calendar-plus"></i>
                        <h5 class="mb-0">Request Time Off</h5>
                    </div>
                    <div class="card-body-premium">
                        <form method="POST">
                            <input type="hidden" name="action" value="request_leave">
                            <div class="mb-4">
                                <label class="form-label">Type of Leave</label>
                                <select class="form-select" name="leave_type" required>
                                    <option value="" disabled selected>Select reason...</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="casual">Casual Leave</option>
                                    <option value="earned">Earned Leave</option>
                                    <option value="unpaid">Unpaid Leave</option>
                                </select>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="start_date" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="end_date" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Reason Details</label>
                                <textarea class="form-control" name="reason" rows="3" required placeholder="Please provide additional details here..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-premium w-100"><i class="fas fa-paper-plane me-2"></i> Submit Leave Request</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Shifts, Leave History, Salary -->
            <div class="col-lg-6">
                <!-- Upcoming Shifts -->
                <div class="premium-card mb-4">
                    <div class="card-header-premium">
                        <i class="fas fa-clock"></i>
                        <h5 class="mb-0">Upcoming Schedule</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table premium-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Site</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($upcoming_shifts)): ?>
                                    <?php foreach ($upcoming_shifts as $s): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo date('M d', strtotime($s['shift_date'])); ?></div>
                                                <small class="text-muted"><?php echo date('l', strtotime($s['shift_date'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="fw-medium"><?php echo htmlspecialchars($s['site_name']); ?></div>
                                            </td>
                                            <td>
                                                <span class="status-badge badge-soft-info d-inline-block mb-1"><?php echo $s['shift_type']; ?></span>
                                                <div class="small fw-medium text-muted">
                                                    <?php echo date('H:i', strtotime($s['start_time'])); ?> - <?php echo date('H:i', strtotime($s['end_time'])); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <i class="fas fa-calendar-alt text-muted fa-2x mb-3"></i>
                                            <p class="text-muted mb-0 fw-medium">No upcoming shifts scheduled.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Leave History -->
                <div class="premium-card mb-4">
                    <div class="card-header-premium">
                        <i class="fas fa-history"></i>
                        <h5 class="mb-0">Recent Leave Requests</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table premium-table">
                            <thead>
                                <tr>
                                    <th>Duration</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($leaves)): ?>
                                    <?php foreach (array_slice($leaves, 0, 5) as $l): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-medium text-dark"><?php echo date('M d', strtotime($l['start_date'])); ?> to <?php echo date('M d', strtotime($l['end_date'])); ?></div>
                                                <small class="text-muted"><?php echo $l['no_of_days']; ?> Day(s)</small>
                                            </td>
                                            <td><span class="text-capitalize fw-medium text-secondary"><?php echo $l['leave_type']; ?></span></td>
                                            <td>
                                                <?php 
                                                $badge_class = 'badge-soft-warning';
                                                if($l['status'] == 'approved') $badge_class = 'badge-soft-success';
                                                if($l['status'] == 'rejected') $badge_class = 'badge-soft-danger';
                                                ?>
                                                <span class="status-badge <?php echo $badge_class; ?>"><?php echo ucfirst($l['status']); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <i class="fas fa-calendar-times text-muted fa-2x mb-3"></i>
                                            <p class="text-muted mb-0 fw-medium">No leave requests found.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Width Salary Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="premium-card">
                    <div class="card-header-premium d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-check-alt"></i>
                            <h5 class="mb-0">Payroll History</h5>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table premium-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Attendance</th>
                                    <th>Earnings</th>
                                    <th>Deductions</th>
                                    <th>Net Pay</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($salary_history)): ?>
                                    <?php foreach ($salary_history as $s): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo date('F Y', strtotime($s['year_month'] . '-01')); ?></div>
                                            </td>
                                            <td>
                                                <span class="fw-medium"><?php echo $s['present_days']; ?></span> <span class="text-muted">/ <?php echo $s['total_working_days']; ?> days</span>
                                            </td>
                                            <td>
                                                <div class="fw-medium text-dark">₹<?php echo number_format($s['basic_salary'], 2); ?></div>
                                                <?php if(($s['overtime_allowance'] + ($s['bonus'] ?? 0)) > 0): ?>
                                                <small class="text-success"><i class="fas fa-plus me-1"></i>₹<?php echo number_format($s['overtime_allowance'] + ($s['bonus'] ?? 0), 2); ?> OT</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-danger fw-medium">
                                                <?php echo $s['deductions'] > 0 ? '-₹'.number_format($s['deductions'], 2) : '—'; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-primary fs-5">₹<?php echo number_format($s['net_salary'], 2); ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                $status_class = 'badge-soft-warning';
                                                if($s['payment_status'] == 'paid') $status_class = 'badge-soft-success';
                                                if($s['payment_status'] == 'hold') $status_class = 'badge-soft-danger';
                                                ?>
                                                <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($s['payment_status']); ?></span>
                                            </td>
                                            <td>
                                                <a href="payslip.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-light rounded-circle shadow-sm" style="width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; color: var(--primary);" title="Download Statement">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-file-invoice text-muted fa-3x mb-3"></i>
                                            <p class="text-muted mb-0 fw-medium">No payroll records have been generated yet.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 text-center mt-auto" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(10px);">
        <div class="container text-muted fw-medium small">
            &copy; <?php echo date('Y'); ?> Security Guard Management System. All rights reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

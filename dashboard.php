<?php
require_once 'core/init.php';


$auth->requireLogin();

$user = $auth->getUser();


$guardModel = new Guard($conn);
$attendanceModel = new Attendance($conn);
$salaryModel = new Salary($conn);
$clientModel = new Client($conn);
$leaveModel = new Leave($conn);

$total_guards = $guardModel->count();
$active_guards = $guardModel->countActive();
$total_attendance = $attendanceModel->count();
$today_present = $attendanceModel->getTodayPresent();
$total_clients = $clientModel->count();
$pending_salaries = $salaryModel->getPendingSalaries();
$pending_leaves = $leaveModel->getPendingCount();

$recent_guards = $guardModel->getAll(5, 0);
$recent_attendance = $attendanceModel->getAll(10, 0);
$recent_leaves = $leaveModel->getRecentLeaves(8);

// Chart Data
$weekly_attendance = $attendanceModel->getWeeklyStats(7);
$status_stats = $guardModel->getStatusStats();
$site_distribution = $guardModel->getSiteDistribution();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Security Guard Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
    <!-- Local Chart.js -->
    <script src="<?php echo ASSETS_URL; ?>js/chart.min.js"></script>
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

                <!-- Dashboard Content -->
                <div class="dashboard-content">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div>
                            <h1 class="page-title">Dashboard</h1>
                            <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
                        </div>
                        <div class="header-actions">
                            <span class="date-time"><?php echo date('l, F j, Y'); ?></span>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card stat-card-blue">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <h6>Total Guards</h6>
                                    <h3><?php echo $total_guards; ?></h3>
                                    <p class="stat-footer"><i class="fas fa-arrow-up"></i> <?php echo $active_guards; ?> Active</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card stat-card-green">
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-content">
                                    <h6>Today's Present</h6>
                                    <h3><?php echo $today_present; ?></h3>
                                    <p class="stat-footer"><i class="fas fa-calendar"></i> <?php echo date('M d, Y'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card stat-card-orange">
                                <div class="stat-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="stat-content">
                                    <h6>Total Clients</h6>
                                    <h3><?php echo $total_clients; ?></h3>
                                    <p class="stat-footer"><i class="fas fa-briefcase"></i> Active Partners</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card stat-card-red">
                                <div class="stat-icon">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="stat-content">
                                    <h6>Pending Salary</h6>
                                    <h3><?php echo $pending_salaries; ?></h3>
                                    <p class="stat-footer"><i class="fas fa-hourglass"></i> To Be Processed</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card stat-card-orange">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                                <div class="stat-content">
                                    <h6>Pending Leaves</h6>
                                    <h3><?php echo $pending_leaves; ?></h3>
                                    <p class="stat-footer"><i class="fas fa-bell"></i> New Requests</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-4">
                            <div class="card chart-card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Attendance (Last 7 Days)</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="attendanceChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card chart-card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Guard Status</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="guardStatusChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card chart-card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Guards by Site</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="siteDistributionChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Data Section -->
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Recent Guards</h6>
                                    <a href="guards.php" class="btn-link">View All</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_guards as $guard): ?>
                                                <tr>
                                                    <td><a href="guard_dashboard.php?id=<?php echo $guard['id']; ?>" class="text-decoration-none text-dark" target="_blank"><strong><?php echo htmlspecialchars($guard['full_name']); ?></strong></a></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $guard['status'] === 'active' ? 'success' : 'secondary'; ?> badge-sm">
                                                            <?php echo ucfirst($guard['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Recent Attendance</h6>
                                    <a href="attendance.php" class="btn-link">View All</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Guard</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($recent_attendance, 0, 5) as $record): ?>
                                                <tr>
                                                    <td><a href="guard_dashboard.php?id=<?php echo $record['guard_id']; ?>" class="text-decoration-none text-dark" target="_blank"><strong><?php echo htmlspecialchars($record['full_name']); ?></strong></a></td>
                                                    <td>
                                                        <span class="badge bg-<?php
    if ($record['status'] === 'present') echo 'success';
    elseif ($record['status'] === 'absent') echo 'danger';
    else echo 'warning';
?> badge-sm">
                                                            <?php echo ucfirst($record['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Recent Leaves</h6>
                                    <a href="leaves.php" class="btn-link">View All</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Guard</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($recent_leaves, 0, 5) as $leave): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($leave['full_name']); ?></strong></td>
                                                    <td>
                                                        <span class="badge bg-<?php
    echo match($leave['status']) {
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        default => 'secondary'
    };
?> badge-sm">
                                                            <?php echo ucfirst($leave['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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
    <script>
        console.log('Dashboard Charts: Initializing...');
        
        document.addEventListener('DOMContentLoaded', function() {
            try {
                if (typeof Chart === 'undefined') {
                    console.error('Dashboard Charts: Chart.js library is NOT loaded!');
                    return;
                }

                console.log('Dashboard Charts: Chart.js detected version ' + Chart.version);

                // Set Global Defaults
                Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, sans-serif";
                Chart.defaults.color = '#64748b';
                Chart.defaults.plugins.tooltip.padding = 12;
                Chart.defaults.plugins.tooltip.cornerRadius = 8;
                Chart.defaults.plugins.tooltip.backgroundColor = '#1e293b';
                Chart.defaults.plugins.tooltip.titleFont = { weight: '600' };

                // Attendance Chart
                const attendanceData = <?php echo json_encode($weekly_attendance); ?>;
                console.log('Dashboard Charts: Attendance Data:', attendanceData);
                
                const attendanceCtx = document.getElementById('attendanceChart');
                if (attendanceCtx) {
                    new Chart(attendanceCtx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode(array_map(function($date) { return date('M d', strtotime($date)); }, array_keys($weekly_attendance))); ?>,
                            datasets: [
                                {
                                    label: 'Present',
                                    data: <?php echo json_encode(array_column($weekly_attendance, 'present')); ?>,
                                    backgroundColor: 'rgba(99, 102, 241, 0.08)',
                                    borderColor: '#6366f1',
                                    borderWidth: 2.5,
                                    pointBackgroundColor: '#6366f1',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    tension: 0.4,
                                    fill: true
                                },
                                {
                                    label: 'Absent',
                                    data: <?php echo json_encode(array_column($weekly_attendance, 'absent')); ?>,
                                    backgroundColor: 'rgba(244, 63, 94, 0.08)',
                                    borderColor: '#f43f5e',
                                    borderWidth: 2.5,
                                    pointBackgroundColor: '#f43f5e',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    tension: 0.4,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                                y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
                            },
                            plugins: {
                                legend: { position: 'top', labels: { usePointStyle: true, font: { size: 12, weight: '500' } } }
                            }
                        }
                    });
                    console.log('Dashboard Charts: Attendance Chart Rendered');
                }

                // Guard Status Chart
                const statusStats = <?php echo json_encode($status_stats); ?>;
                console.log('Dashboard Charts: Status Stats:', statusStats);
                
                const statusCtx = document.getElementById('guardStatusChart');
                if (statusCtx) {
                    new Chart(statusCtx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: <?php echo json_encode(array_column($status_stats, 'status')); ?>,
                            datasets: [{
                                data: <?php echo json_encode(array_column($status_stats, 'count')); ?>,
                                backgroundColor: ['#6366f1', '#cbd5e1', '#f59e0b', '#10b981'],
                                borderColor: '#fff',
                                borderWidth: 3,
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '72%',
                            plugins: {
                                legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 12 }, padding: 16 } }
                            }
                        }
                    });
                    console.log('Dashboard Charts: Guard Status Chart Rendered');
                }

                // Site Distribution Chart
                const siteDist = <?php echo json_encode($site_distribution); ?>;
                console.log('Dashboard Charts: Site Distribution:', siteDist);
                
                const siteCtx = document.getElementById('siteDistributionChart');
                if (siteCtx) {
                    new Chart(siteCtx.getContext('2d'), {
                        type: 'polarArea',
                        data: {
                            labels: <?php echo json_encode(array_column($site_distribution, 'site_name')); ?>,
                            datasets: [{
                                data: <?php echo json_encode(array_column($site_distribution, 'count')); ?>,
                                backgroundColor: [
                                    'rgba(99, 102, 241, 0.75)', 'rgba(20, 184, 166, 0.75)',
                                    'rgba(245, 158, 11, 0.75)', 'rgba(244, 63, 94, 0.75)',
                                    'rgba(139, 92, 246, 0.75)', 'rgba(14, 165, 233, 0.75)'
                                ],
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 12 }, padding: 16 } }
                            }
                        }
                    });
                    console.log('Dashboard Charts: Site Distribution Chart Rendered');
                }
            } catch (err) {
                console.error('Dashboard Charts: Error during initialization:', err);
            }
        });
    </script>
</body>
</html>

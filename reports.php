<?php
require_once 'core/init.php';

$auth->requireLogin();
$user = $auth->getUser();

$guardModel = new Guard($conn);
$attendanceModel = new Attendance($conn);
$salaryModel = new Salary($conn);


$guard_id = isset($_GET['guard_id']) ? (int)$_GET['guard_id'] : null;
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? sanitize($_GET['report_type']) : 'attendance';

$guards = $guardModel->getAll(null, 0);
$report_data = [];


if ($report_type === 'attendance') {
    $report_data = $attendanceModel->getDateRangeAttendance($guard_id, $start_date, $end_date);
} elseif ($report_type === 'salary') {
    $report_data = $salaryModel->getSalaryHistory($guard_id, $start_date, $end_date);
} elseif ($report_type === 'performance') {
    
    if ($guard_id) {
        $attendance = $attendanceModel->getDateRangeAttendance($guard_id, $start_date, $end_date);
        $report_data = [
            'attendance' => $attendance,
            'summary' => $attendanceModel->getAttendanceSummary($guard_id, date('Y-m', strtotime($start_date)))
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - SGMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'app/components/sidebar.php'; ?>
            <div class="col-md-9 col-lg-10 main-content">
                <?php include 'app/components/topnav.php'; ?>
                <div class="dashboard-content">
                    <div class="page-header">
                        <div>
                            <h1 class="page-title">Reports & Analytics</h1>
                            <p class="page-subtitle">View detailed reports and analytics</p>
                        </div>
                    </div>

                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" class="needs-validation">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Report Type *</label>
                                        <select class="form-control" name="report_type">
                                            <option value="attendance" <?php echo $report_type === 'attendance' ? 'selected' : ''; ?>>Attendance Report</option>
                                            <option value="salary" <?php echo $report_type === 'salary' ? 'selected' : ''; ?>>Salary Report</option>
                                            <option value="performance" <?php echo $report_type === 'performance' ? 'selected' : ''; ?>>Performance Summary</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Select Guard</label>
                                        <select class="form-control" name="guard_id">
                                            <option value="">-- All Guards --</option>
                                            <?php foreach ($guards as $guard): ?>
                                            <option value="<?php echo $guard['id']; ?>" <?php echo $guard_id === $guard['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($guard['full_name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">From Date</label>
                                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">To Date</label>
                                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    
                    <?php if (!empty($report_data)): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">
                                    <?php echo ucfirst(str_replace('_', ' ', $report_type)); ?> Report
                                </h6>
                                <div>
                                    <a href="report_pdf.php?<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-outline-danger" target="_blank">
                                        <i class="fas fa-file-pdf"></i> Export PDF
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary" onclick="exportTableToCSV('report', 'reportTable')">
                                        <i class="fas fa-file-csv"></i> Export CSV
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="printTable('reportTable')">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="reportTable">
                                    <thead>
                                        <tr>
                                            <?php if ($report_type === 'attendance'): ?>
                                                <th>Date</th>
                                                <th>Check-In</th>
                                                <th>Check-Out</th>
                                                <th>Hours Worked</th>
                                                <th>Overtime</th>
                                                <th>Status</th>
                                            <?php elseif ($report_type === 'salary'): ?>
                                                <th>Month</th>
                                                <th>Present Days</th>
                                                <th>Basic Salary</th>
                                                <th>Overtime</th>
                                                <th>Deductions</th>
                                                <th>Net Salary</th>
                                                <th>Status</th>
                                            <?php else: ?>
                                                <th>Metric</th>
                                                <th>Value</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($report_type === 'attendance'): ?>
                                            <?php foreach ($report_data as $record): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                                <td><?php echo $record['check_in_time'] ? date('H:i', strtotime($record['check_in_time'])) : '-'; ?></td>
                                                <td><?php echo $record['check_out_time'] ? date('H:i', strtotime($record['check_out_time'])) : '-'; ?></td>
                                                <td><?php echo formatHours($record['hours_worked']); ?></td>
                                                <td><?php echo formatHours($record['overtime_hours']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $record['status'] === 'present' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($record['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php elseif ($report_type === 'salary'): ?>
                                            <?php foreach ($report_data as $record): ?>
                                            <tr>
                                                <td><?php echo date('M Y', strtotime($record['year_month'] . '-01')); ?></td>
                                                <td><?php echo $record['present_days']; ?></td>
                                                <td><?php echo formatCurrency($record['basic_salary']); ?></td>
                                                <td><?php echo formatCurrency($record['overtime_allowance']); ?></td>
                                                <td><?php echo formatCurrency($record['deductions']); ?></td>
                                                <td><strong><?php echo formatCurrency($record['net_salary']); ?></strong></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $record['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($record['payment_status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <?php if (isset($report_data['summary'])): ?>
                                            <tr>
                                                <td>Total Days</td>
                                                <td><?php echo $report_data['summary']['total_days']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Present Days</td>
                                                <td><?php echo $report_data['summary']['present_days']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Absent Days</td>
                                                <td><?php echo $report_data['summary']['absent_days']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Attendance %</td>
                                                <td><?php echo getAttendancePercentage($report_data['summary']['present_days'], $report_data['summary']['total_days']); ?>%</td>
                                            </tr>
                                            <tr>
                                                <td>Total Overtime Hours</td>
                                                <td><?php echo formatHours($report_data['summary']['total_overtime_hours']); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['report_type'])): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No data found for the selected criteria. Please adjust your filters.
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Select report type and filter to generate report.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
</body>
</html>

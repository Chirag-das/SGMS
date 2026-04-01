<?php
require_once 'core/init.php';

// Require user to be logged in
$auth->requireLogin();

$user = $auth->getUser();

// Check if site ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'sites.php');
    exit;
}

$site_id = (int)$_GET['id'];

// Initialize models
$siteModel = new Site($conn);
$guardModel = new Guard($conn);
$attendanceModel = new Attendance($conn);

// Get site details
$site = $siteModel->getById($site_id);

if (!$site) {
    // Site not found
    header('Location: ' . BASE_URL . 'sites.php');
    exit;
}

// Get guards currently assigned to this site
$assigned_guards = $guardModel->getBySiteId($site_id);

// Get today's attendance for this site
$today = date('Y-m-d');
$today_attendance_query = "SELECT a.*, g.full_name, g.employee_id 
                           FROM attendance a 
                           JOIN guards g ON a.guard_id = g.id 
                           WHERE g.assigned_site_id = {$site_id} AND a.date = '{$today}'";
$today_attendance_result = $conn->query($today_attendance_query);
$today_attendance = $today_attendance_result ? $today_attendance_result->fetch_all(MYSQLI_ASSOC) : [];

// Calculate stats
$guards_on_duty = 0;
foreach ($today_attendance as $att) {
    if ($att['status'] === 'present') {
        $guards_on_duty++;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Dashboard - Security Guard Management System</title>
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
                            <h1 class="page-title">Site Dashboard</h1>
                            <p class="page-subtitle">Detailed View for <?php echo htmlspecialchars($site['site_name']); ?> (<?php echo htmlspecialchars($site['site_code'] ?? 'N/A'); ?>)</p>
                        </div>
                        <div class="d-flex gap-2">
                           <a href="client_dashboard.php?id=<?php echo $site['client_id']; ?>" class="btn btn-info text-white">
                                <i class="fas fa-building"></i> View Client
                            </a>
                            <a href="sites.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Sites
                            </a>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Site Info Card -->
                        <div class="col-lg-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="card-title mb-0"><i class="fas fa-map-marked-alt text-primary me-2"></i> Site Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-4 pt-3">
                                        <div class="avatar-lg bg-light rounded-circle d-inline-flex align-items-center justify-content-center border-3 border-white shadow-sm" style="width: 100px; height: 100px;">
                                            <i class="fas fa-industry fa-4x text-muted"></i>
                                        </div>
                                        <h4 class="mt-3 mb-1 fw-bold"><?php echo htmlspecialchars($site['site_name']); ?></h4>
                                        <p class="text-muted"><i class="fas fa-hashtag me-1"></i><?php echo htmlspecialchars($site['site_code'] ?? 'N/A'); ?></p>
                                        <span class="badge bg-<?php echo strtolower($site['site_status']) === 'active' ? 'success' : 'secondary'; ?> px-3 py-2">
                                            <?php echo ucfirst(strtolower($site['site_status'])); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="list-group list-group-flush border-top">
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                            <span class="text-muted"><i class="fas fa-building me-2"></i> Client</span>
                                            <span class="fw-bold"><?php echo htmlspecialchars($site['client_name']); ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                            <span class="text-muted"><i class="fas fa-tags me-2"></i> Site Type</span>
                                            <span class="fw-bold"><?php echo htmlspecialchars($site['site_type']); ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-start px-0 py-3">
                                            <span class="text-muted"><i class="fas fa-user-shield me-2"></i> Supervisor</span>
                                            <div class="text-end">
                                                <div class="fw-bold"><?php echo htmlspecialchars($site['site_supervisor'] ?? 'N/A'); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($site['supervisor_contact'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                        <div class="list-group-item px-0 py-3">
                                            <span class="text-muted d-block mb-2"><i class="fas fa-map-marker-alt me-2 text-danger"></i> Location</span>
                                            <div class="bg-light p-2 rounded small fw-medium">
                                                <?php echo htmlspecialchars($site['site_address_line1']); ?><br>
                                                <?php if($site['site_address_line2']) echo htmlspecialchars($site['site_address_line2']) . '<br>'; ?>
                                                <?php echo htmlspecialchars($site['city']); ?>, <?php echo htmlspecialchars($site['state']); ?> - <?php echo htmlspecialchars($site['pincode']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Site Stats & Personnel -->
                        <div class="col-lg-8">
                            <!-- Statistics row -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white border-0 shadow-sm h-100">
                                        <div class="card-body py-4 d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 mb-1 fw-normal">Required Guards</h6>
                                                <h2 class="mb-0 fw-bold"><?php echo (int)$site['total_guards_required']; ?></h2>
                                            </div>
                                            <i class="fas fa-users-cog fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success text-white border-0 shadow-sm h-100">
                                        <div class="card-body py-4 d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 mb-1 fw-normal">Guards On Duty</h6>
                                                <h2 class="mb-0 fw-bold"><?php echo $guards_on_duty; ?></h2>
                                            </div>
                                            <i class="fas fa-user-check fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-info text-white border-0 shadow-sm h-100">
                                        <div class="card-body py-4 d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 mb-1 fw-normal">Current Shift</h6>
                                                <h2 class="mb-0 fw-bold fs-4"><?php echo htmlspecialchars($site['shift_type']); ?></h2>
                                                <small class="opacity-75"><?php echo date('H:i', strtotime($site['start_time'])); ?> - <?php echo date('H:i', strtotime($site['end_time'])); ?></small>
                                            </div>
                                            <i class="fas fa-clock fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Guards List Table -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0 fw-bold">Deployed Personnel (<?php echo count($assigned_guards); ?>)</h6>
                                    <a href="guards.php" class="btn btn-sm btn-outline-primary">Manage Personnel</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3">Guard Name</th>
                                                    <th>Emp ID</th>
                                                    <th>Phone</th>
                                                    <th>Status Today</th>
                                                    <th class="text-end pe-3">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(!empty($assigned_guards)): ?>
                                                    <?php foreach($assigned_guards as $g): ?>
                                                    <?php 
                                                        $status_today = 'not_marked';
                                                        foreach($today_attendance as $ta) {
                                                            if($ta['guard_id'] == $g['id']) {
                                                                $status_today = $ta['status'];
                                                                break;
                                                            }
                                                        }
                                                    ?>
                                                    <tr>
                                                        <td class="ps-3">
                                                            <div class="d-flex align-items-center">
                                                                <?php if($g['photo']): ?>
                                                                    <img src="<?php echo ASSETS_URL; ?>../uploads/guards/<?php echo htmlspecialchars($g['photo']); ?>" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover;">
                                                                <?php else: ?>
                                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 fw-bold text-primary" style="width:32px; height:32px; font-size:12px;">
                                                                        <?php echo strtoupper(substr($g['full_name'], 0, 1)); ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div>
                                                                    <span class="fw-bold d-block"><?php echo htmlspecialchars($g['full_name']); ?></span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><code class="text-primary"><?php echo htmlspecialchars($g['employee_id']); ?></code></td>
                                                        <td><?php echo htmlspecialchars($g['phone']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo $status_today === 'present' ? 'success' : ($status_today === 'absent' ? 'danger' : 'secondary'); ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $status_today)); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-end pe-3">
                                                            <a href="guard_dashboard.php?id=<?php echo $g['id']; ?>" class="btn btn-sm btn-light border" title="View Profile">
                                                                <i class="fas fa-eye text-primary"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center py-5 text-muted">
                                                            <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                                            <p class="mb-0">No guards currently assigned to this site.</p>
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
</body>
</html>

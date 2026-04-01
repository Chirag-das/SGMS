<?php
require_once 'core/init.php';

$auth->requireLogin();
$user = $auth->getUser();

$shiftModel = new Shift($conn);
$guardModel = new Guard($conn);
$siteModel = new Site($conn);

$guards = $guardModel->getAll(null, 0);
$sites = $siteModel->getAll();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $data = [
                'guard_id' => (int)$_POST['guard_id'],
                'site_id' => (int)$_POST['site_id'],
                'shift_date' => sanitize($_POST['shift_date']),
                'shift_type' => sanitize($_POST['shift_type']),
                'start_time' => sanitize($_POST['start_time']),
                'end_time' => sanitize($_POST['end_time']),
                'status' => 'scheduled'
            ];
            if ($shiftModel->create($data)) {
                $_SESSION['success'] = "Shift scheduled successfully!";
            } else {
                $_SESSION['error'] = "Error scheduling shift.";
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($shiftModel->delete((int)$_POST['id'])) {
                $_SESSION['success'] = "Shift deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting shift.";
            }
        }
        header("Location: shifts.php");
        exit;
    }
}

$all_shifts = $shiftModel->getAll(50);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Scheduling - SGMS</title>
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
                    <div class="page-header d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="page-title">Shift Scheduling</h1>
                            <p class="page-subtitle">Manage guard assignments and rotations</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShiftModal">
                            <i class="fas fa-calendar-plus me-2"></i> Schedule Shift
                        </button>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Guard</th>
                                            <th>Site</th>
                                            <th>Type</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($all_shifts)): ?>
                                            <tr><td colspan="7" class="text-center">No shifts scheduled.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($all_shifts as $shift): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($shift['shift_date'])); ?></td>
                                                <td><strong><?php echo htmlspecialchars($shift['full_name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($shift['site_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $shift['shift_type']; ?></span>
                                                </td>
                                                <td><?php echo date('H:i', strtotime($shift['start_time'])); ?> - <?php echo date('H:i', strtotime($shift['end_time'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $shift['status'] === 'scheduled' ? 'primary' : 'success'; ?>">
                                                        <?php echo ucfirst($shift['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this shift?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $shift['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
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

    <!-- Add Shift Modal -->
    <div class="modal fade" id="addShiftModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Schedule New Shift</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Guard</label>
                            <select name="guard_id" class="form-select" required>
                                <?php foreach ($guards as $g): ?>
                                    <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['full_name']); ?> (<?php echo $g['employee_id']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site</label>
                            <select name="site_id" class="form-select" required>
                                <?php foreach ($sites as $s): ?>
                                    <option value="<?php echo $s['site_id']; ?>"><?php echo htmlspecialchars($s['site_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="shift_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shift Type</label>
                            <select name="shift_type" class="form-select" id="shiftType" required>
                                <option value="Morning">Morning (06:00 - 14:00)</option>
                                <option value="Evening">Evening (14:00 - 22:00)</option>
                                <option value="Night">Night (22:00 - 06:00)</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time" id="startTime" class="form-control" value="06:00" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" id="endTime" class="form-control" value="14:00" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule Shift</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-fill times based on shift type
        document.getElementById('shiftType').addEventListener('change', function() {
            const start = document.getElementById('startTime');
            const end = document.getElementById('endTime');
            switch(this.value) {
                case 'Morning': start.value = '06:00'; end.value = '14:00'; break;
                case 'Evening': start.value = '14:00'; end.value = '22:00'; break;
                case 'Night': start.value = '22:00'; end.value = '06:00'; break;
            }
        });
    </script>
</body>
</html>

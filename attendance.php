<?php
require_once 'core/init.php';

// Check login
$auth->requireLogin();

$user = $auth->getUser();

// Initialize Models
$attendanceModel = new Attendance($conn);
$guardModel = new Guard($conn);

// Get all guards for selection
$guards = $guardModel->getAll();

$message = '';
$messageType = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'mark') {
            $data = [
                'guard_id' => (int)$_POST['guard_id'],
                'date' => sanitize($_POST['date']),
                'check_in_time' => !empty($_POST['check_in_time']) ? sanitize($_POST['check_in_time']) : null,
                'check_out_time' => !empty($_POST['check_out_time']) ? sanitize($_POST['check_out_time']) : null,
                'status' => sanitize($_POST['status']),
                'remarks' => sanitize($_POST['remarks']),
                'recorded_by' => $user['id']
            ];

            // Auto-calculate hours if times are present
            if ($data['check_in_time'] && $data['check_out_time']) {
                $start = strtotime($data['check_in_time']);
                $end = strtotime($data['check_out_time']);
                $diff = ($end - $start) / 3600;
                $data['hours_worked'] = round($diff, 2);
                $data['overtime_hours'] = ($data['hours_worked'] > 8) ? round($data['hours_worked'] - 8, 2) : 0;
            }
            else {
                $data['hours_worked'] = 0;
                $data['overtime_hours'] = 0;
            }

            // Check if record already exists for this guard and date
            if ($attendanceModel->getByGuardAndDate($data['guard_id'], $data['date'])) {
                $message = "Attendance record already exists for this guard on this date.";
                $messageType = "warning";
            }
            else {
                if ($attendanceModel->create($data)) {
                    $message = "Attendance marked successfully!";
                    $messageType = "success";
                }
                else {
                    $message = "Error marking attendance.";
                    $messageType = "danger";
                }
            }
        }

        if ($action === 'delete') {
            $id = (int)$_POST['id'];
            $sql = "DELETE FROM attendance WHERE id = $id";
            if ($conn->query($sql)) {
                $message = "Attendance record deleted successfully!";
                $messageType = "success";
            }
            else {
                $message = "Error deleting record.";
                $messageType = "danger";
            }
        }
    }
    // Session redirect to fix reloading issue
    if ($message) {
        $_SESSION['msg'] = $message;
        $_SESSION['msg_type'] = $messageType;
        header("Location: attendance.php");
        exit;
    }
}

// Check for session messages
if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    $messageType = $_SESSION['msg_type'];
    unset($_SESSION['msg']);
    unset($_SESSION['msg_type']);
}

// Pagination logic
$total = $attendanceModel->count();
$pagination = getPagination($total);
$attendanceList = $attendanceModel->getAll($pagination['per_page'], $pagination['offset']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - SGMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
</head>
<body>
    <div class="row g-0">
        <?php include 'app/components/sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 main-content">
            <?php include 'app/components/topnav.php'; ?>
            
            <div class="dashboard-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Attendance Management</h1>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                        <i class="fas fa-plus"></i> Mark Attendance
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php
endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Guard</th>
                                        <th>Check In/Out</th>
                                        <th>Hours</th>
                                        <th>OT</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendanceList as $a): ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($a['date'])); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($a['full_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($a['employee_id']); ?></small>
                                        </td>
                                        <td><?php echo $a['check_in_time'] ?? '-'; ?> / <?php echo $a['check_out_time'] ?? '-'; ?></td>
                                        <td><?php echo $a['hours_worked']; ?></td>
                                        <td><?php echo $a['overtime_hours']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php
    echo $a['status'] === 'present' ? 'success' :
        ($a['status'] === 'absent' ? 'danger' : 
        ($a['status'] === 'half_day' ? 'info' : 'warning'));
?>"><?php echo ucfirst(str_replace('_', ' ', $a['status'])); ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars($a['remarks'] ?? ''); ?></small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteAttendance(<?php echo $a['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mark Attendance Modal -->
    <div class="modal fade" id="markAttendanceModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="mark">
                    <div class="mb-3">
                        <label class="form-label">Guard</label>
                        <select name="guard_id" class="form-select" required>
                            <?php foreach ($guards as $g): ?>
                            <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['full_name']); ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Check-in</label>
                            <input type="time" name="check_in_time" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">Check-out</label>
                            <input type="time" name="check_out_time" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="leave">Leave</option>
                            <option value="half_day">Half Day</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                </div>
                <div class="modal-body">
                    Are you sure?
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteAttendance(id) {
            document.getElementById('delete_id').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>

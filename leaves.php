<?php
require_once 'core/init.php';

$auth->requireLogin();
$user = $auth->getUser();

$leaveModel = new Leave($conn);
$guardModel = new Guard($conn);

$guards = $guardModel->getAll();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $data = [
                'guard_id' => (int)$_POST['guard_id'],
                'leave_type' => sanitize($_POST['leave_type']),
                'start_date' => sanitize($_POST['start_date']),
                'end_date' => sanitize($_POST['end_date']),
                'no_of_days' => (int)$_POST['no_of_days'],
                'reason' => sanitize($_POST['reason']),
                'status' => 'pending'
            ];

            if ($leaveModel->create($data)) {
                $message = 'Leave request added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error adding leave request.';
                $messageType = 'danger';
            }
        }

        if ($action === 'update_status') {
            $leave_id = (int)$_POST['leave_id'];
            $status = sanitize($_POST['status']);
            $remarks = sanitize($_POST['remarks']);
            
            if ($leaveModel->updateStatus($leave_id, $status, $user['id'], $remarks)) {
                $message = 'Leave status updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating status.';
                $messageType = 'danger';
            }
        }
    }
}

$total_pending = $leaveModel->getPendingCount();
$all_leaves = $leaveModel->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaves Management - SGMS</title>
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
                            <h1 class="page-title">Leaves Management</h1>
                            <p class="page-subtitle">Track and manage guard leave requests</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
                            <i class="fas fa-plus"></i> Request Leave
                        </button>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-0">Pending Requests</h6>
                                        <h3 class="mb-0"><?php echo $total_pending; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">All Leave Records</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Guard Name</th>
                                            <th>Type</th>
                                            <th>Duration</th>
                                            <th>Days</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_leaves as $leave): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($leave['full_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($leave['employee_id']); ?></small>
                                            </td>
                                            <td><?php echo ucfirst($leave['leave_type']); ?></td>
                                            <td>
                                                <?php echo formatDate($leave['start_date'], 'd M, Y'); ?> - 
                                                <?php echo formatDate($leave['end_date'], 'd M, Y'); ?>
                                            </td>
                                            <td><?php echo $leave['no_of_days']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($leave['status']) {
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($leave['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($leave['status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-info" onclick="updateStatus(<?php echo $leave['id']; ?>, '<?php echo htmlspecialchars($leave['full_name']); ?>')">
                                                        Process
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($leave)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
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

    <!-- Add Leave Modal -->
    <div class="modal fade" id="addLeaveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Guard</label>
                            <select class="form-select" name="guard_id" required>
                                <option value="">-- Choose Guard --</option>
                                <?php foreach ($guards as $g): ?>
                                    <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['full_name']); ?> (<?php echo $g['employee_id']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Leave Type</label>
                            <select class="form-select" name="leave_type" required>
                                <option value="casual">Casual Leave</option>
                                <option value="sick">Sick Leave</option>
                                <option value="earned">Earned Leave</option>
                                <option value="unpaid">Unpaid Leave</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required id="start_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" required id="end_date">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Number of Days</label>
                            <input type="number" class="form-control" name="no_of_days" required id="no_of_days" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" name="reason" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="leave_id" id="status_leave_id">
                    <div class="modal-body">
                        <p>Processing leave for: <strong id="status_guard_name"></strong></p>
                        <div class="mb-3">
                            <label class="form-label">Decision</label>
                            <select class="form-select" name="status" required>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="leave_details_content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calculate days between dates
        function calculateDays() {
            const start = document.getElementById('start_date').value;
            const end = document.getElementById('end_date').value;
            if (start && end) {
                const startDate = new Date(start);
                const endDate = new Date(end);
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                document.getElementById('no_of_days').value = diffDays > 0 ? diffDays : 0;
            }
        }

        document.getElementById('start_date').addEventListener('change', calculateDays);
        document.getElementById('end_date').addEventListener('change', calculateDays);

        function updateStatus(id, name) {
            document.getElementById('status_leave_id').value = id;
            document.getElementById('status_guard_name').innerText = name;
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        function viewDetails(leave) {
            let content = `
                <table class="table table-sm">
                    <tr><th>Guard</th><td>${leave.full_name} (${leave.employee_id})</td></tr>
                    <tr><th>Type</th><td>${leave.leave_type.charAt(0).toUpperCase() + leave.leave_type.slice(1)}</td></tr>
                    <tr><th>Duration</th><td>${leave.start_date} to ${leave.end_date}</td></tr>
                    <tr><th>Total Days</th><td>${leave.no_of_days}</td></tr>
                    <tr><th>Reason</th><td>${leave.reason || 'N/A'}</td></tr>
                    <tr><th>Status</th><td>${leave.status.charAt(0).toUpperCase() + leave.status.slice(1)}</td></tr>
                    <tr><th>Created At</th><td>${leave.created_at}</td></tr>
                    <tr><th>Remarks</th><td>${leave.remarks || 'None'}</td></tr>
                </table>
            `;
            document.getElementById('leave_details_content').innerHTML = content;
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        }
    </script>
</body>
</html>

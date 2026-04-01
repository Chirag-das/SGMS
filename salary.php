okay<?php
require_once 'core/init.php';

// Check login
$auth->requireLogin();

$user = $auth->getUser();

// Initialize Models
$salaryModel = new Salary($conn);
$guardModel = new Guard($conn);

// Get all guards for the selection dropdown
$guards = $guardModel->getAll();

$message = '';
$messageType = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'generate') {
            $guard_id = (int)$_POST['guard_id'];
            $year_month = sanitize($_POST['year_month']);

            // Check if salary already exists for this guard and month
            if ($salaryModel->getGuardSalaryByMonth($guard_id, $year_month)) {
                $message = "Salary already generated for this guard and month.";
                $messageType = "warning";
            } else {
                // Get guard data to calculate salary
                $guard_data = $guardModel->getById($guard_id);
                if ($guard_data) {
                    $calc = $salaryModel->calculateSalary($guard_id, $year_month, $guard_data);
                    
                    $salaryData = [
                        'guard_id' => $guard_id,
                        'year_month' => $year_month,
                        'total_working_days' => 26, // Default
                        'present_days' => $calc['present_days'],
                        'absent_days' => $calc['absent_days'],
                        'leave_days' => $calc['leave_days'],
                        'half_day_count' => $calc['half_day_count'],
                        'overtime_hours' => $calc['overtime_hours'],
                        'basic_salary' => $calc['basic_salary'],
                        'overtime_allowance' => $calc['overtime_allowance'],
                        'bonus' => 0,
                        'deductions' => $calc['deductions'],
                        'net_salary' => $calc['net_salary'],
                        'payment_status' => 'pending',
                        'remarks' => 'Generated automatically'
                    ];

                    if ($salaryModel->create($salaryData)) {
                        $message = "Salary generated successfully for " . htmlspecialchars($guard_data['full_name']);
                        $messageType = "success";
                    } else {
                        $message = "Error generating salary.";
                        $messageType = "danger";
                    }
                }
            }
        }

        if ($action === 'edit') {
            $salary_id = (int)$_POST['salary_id'];
            $data = [
                'bonus' => (float)$_POST['bonus'],
                'deductions' => (float)$_POST['deductions'],
                'payment_status' => sanitize($_POST['payment_status']),
                'payment_date' => !empty($_POST['payment_date']) ? sanitize($_POST['payment_date']) : null,
                'payment_method' => sanitize($_POST['payment_method']),
                'remarks' => sanitize($_POST['remarks'])
            ];
            
            // Recalculate net salary based on basic + OT + bonus - deductions
            $salary = $salaryModel->getById($salary_id);
            if ($salary) {
                $data['net_salary'] = (float)$salary['basic_salary'] + (float)$salary['overtime_allowance'] + (float)$data['bonus'] - (float)$data['deductions'];
                
                if ($salaryModel->update($salary_id, $data)) {
                    $message = "Salary record updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error updating salary record.";
                    $messageType = "danger";
                }
            }
        }

        if ($action === 'batch_generate') {
            $year_month = sanitize($_POST['year_month']);
            $results = $salaryModel->batchProcessMonthlySalary($year_month);
            $message = "Batch processing complete. Total processed: " . $results['processed'] . ", Errors: " . $results['errors'];
            $messageType = $results['errors'] > 0 ? "warning" : "success";
        }

        if ($action === 'delete') {
            $salary_id = (int)$_POST['salary_id'];
            $sql = "DELETE FROM salaries WHERE id = $salary_id";
            if ($conn->query($sql)) {
                $message = "Salary record deleted successfully!";
                $messageType = "success";
            } else {
                $message = "Error deleting salary record.";
                $messageType = "danger";
            }
        }
    }
    // Set session message and redirect
    if ($message) {
        $_SESSION['msg'] = $message;
        $_SESSION['msg_type'] = $messageType;
        header("Location: salary.php");
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
$total = $salaryModel->count();
$pagination = getPagination($total);
$salaries = $salaryModel->getAll($pagination['per_page'], $pagination['offset']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Management - Security Guard Management System</title>
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
                    <div class="page-header">
                        <div>
                            <h1 class="page-title">Salary Management</h1>
                            <p class="page-subtitle">Track and manage guard payments</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#batchGenerateModal">
                                <i class="fas fa-layer-group"></i> Batch Generate
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateSalaryModal">
                                <i class="fas fa-calculator"></i> Generate Salary
                            </button>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Total Salary Records: <strong><?php echo $total; ?></strong></h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Guard Name</th>
                                            <th>Year-Month</th>
                                            <th>D/P/A/L/H</th>
                                            <th>OT Hrs</th>
                                            <th>Basic</th>
                                            <th>OT Allow.</th>
                                            <th>Bonus</th>
                                            <th>Deduct.</th>
                                            <th>Net Salary</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($salaries)): ?>
                                            <tr>
                                                <td colspan="12" class="text-center">No salary records found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($salaries as $s): ?>
                                                <tr>
                                                    <td><?php echo $s['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($s['full_name']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($s['employee_id']); ?></small>
                                                    </td>
                                                    <td><?php echo date('F Y', strtotime($s['year_month'] . '-01')); ?></td>
                                                    <td>
                                                        <span title="Total Days"><?php echo $s['total_working_days']; ?></span>/
                                                        <span class="text-success" title="Present"><?php echo $s['present_days']; ?></span>/
                                                        <span class="text-danger" title="Absent"><?php echo $s['absent_days']; ?></span>/
                                                        <span class="text-info" title="Leave"><?php echo $s['leave_days']; ?></span>/
                                                        <span class="text-warning" title="Half Days"><?php echo $s['half_day_count']; ?></span>
                                                    </td>
                                                    <td><?php echo $s['overtime_hours']; ?></td>
                                                    <td><?php echo formatCurrency($s['basic_salary']); ?></td>
                                                    <td><?php echo formatCurrency($s['overtime_allowance']); ?></td>
                                                    <td><?php echo formatCurrency($s['bonus']); ?></td>
                                                    <td><?php echo formatCurrency($s['deductions']); ?></td>
                                                    <td><strong class="text-primary"><?php echo formatCurrency($s['net_salary']); ?></strong></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $s['payment_status'] === 'paid' ? 'success' : 
                                                                ($s['payment_status'] === 'pending' ? 'warning' : 'danger'); 
                                                        ?>">
                                                            <?php echo ucfirst($s['payment_status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="payslip.php?id=<?php echo $s['id']; ?>" class="btn btn-outline-primary" title="Download PDF">
                                                                <i class="fas fa-file-pdf"></i>
                                                            </a>
                                                            <button class="btn btn-info" onclick="editSalary(<?php echo htmlspecialchars(json_encode($s)); ?>)"
                                                                    data-bs-toggle="modal" data-bs-target="#editSalaryModal">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-danger" onclick="deleteSalary(<?php echo $s['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($pagination['total_pages'] > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Salary Modal -->
    <div class="modal fade" id="generateSalaryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Salary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="generate">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Guard *</label>
                            <select class="form-select" name="guard_id" required>
                                <option value="">-- Choose Guard --</option>
                                <?php foreach ($guards as $g): ?>
                                    <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['full_name']); ?> (<?php echo $g['employee_id']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Month *</label>
                            <input type="month" class="form-control" name="year_month" value="<?php echo date('Y-m'); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Salary Modal -->
    <div class="modal fade" id="editSalaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Salary Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="salary_id" id="edit_salary_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guard Name</label>
                                <input type="text" class="form-control" id="edit_guard_name" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Year-Month</label>
                                <input type="text" class="form-control" id="edit_year_month" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Basic Salary</label>
                                <input type="text" class="form-control" id="edit_basic" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">OT Allowance</label>
                                <input type="text" class="form-control" id="edit_ot_allowance" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Net Salary (Current)</label>
                                <input type="text" class="form-control text-primary fw-bold" id="edit_current_net" readonly>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bonus</label>
                                <input type="number" class="form-control" name="bonus" id="edit_bonus" step="0.01" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Additional Deductions</label>
                                <input type="number" class="form-control" name="deductions" id="edit_deductions" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Status</label>
                                <select class="form-select" name="payment_status" id="edit_payment_status">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="hold">Hold</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Date</label>
                                <input type="date" class="form-control" name="payment_date" id="edit_payment_date">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method" id="edit_payment_method">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" name="remarks" id="edit_remarks" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteSalaryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="salary_id" id="delete_salary_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete this salary record?</p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
    <script>
        function editSalary(data) {
            document.getElementById('edit_salary_id').value = data.id;
            document.getElementById('edit_guard_name').value = data.full_name;
            document.getElementById('edit_year_month').value = data.year_month;
            document.getElementById('edit_basic').value = data.basic_salary;
            document.getElementById('edit_ot_allowance').value = data.overtime_allowance;
            document.getElementById('edit_current_net').value = data.net_salary;
            document.getElementById('edit_bonus').value = data.bonus;
            document.getElementById('edit_deductions').value = data.deductions;
            document.getElementById('edit_payment_status').value = data.payment_status;
            document.getElementById('edit_payment_date').value = data.payment_date || '';
            document.getElementById('edit_payment_method').value = data.payment_method;
            document.getElementById('edit_remarks').value = data.remarks || '';
        }

        function deleteSalary(id) {
            document.getElementById('delete_salary_id').value = id;
            new bootstrap.Modal(document.getElementById('deleteSalaryModal')).show();
        }
    </script>
    <!-- Batch Generate Modal -->
    <div class="modal fade" id="batchGenerateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Batch Generate Salaries</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="batch_generate">
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small">
                            <i class="fas fa-info-circle me-1"></i> This will calculate and generate salary records for <strong>all active guards</strong> who don't already have a record for the selected month.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Month *</label>
                            <input type="month" class="form-control" name="year_month" value="<?php echo date('Y-m'); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Process Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>

<?php
require_once 'core/init.php';


$auth->requireLogin();

$user = $auth->getUser();


$guardModel = new Guard($conn);
$siteModel = new Site($conn);


$sites = $siteModel->getAll(null, 0);


$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            
            $photo_filename = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                $photo_filename = uploadFile($_FILES['photo'], GUARDS_UPLOADS_PATH);
            }

            $employee_id = isset($_POST['employee_id']) ? sanitize($_POST['employee_id']) : $guardModel->generateEmployeeId();

            $data = [
                'employee_id' => $employee_id,
                'full_name' => sanitize($_POST['full_name']),
                'phone' => sanitize($_POST['phone']),
                'email' => sanitize($_POST['email']),
                'address' => sanitize($_POST['address']),
                'city' => sanitize($_POST['city']),
                'state' => sanitize($_POST['state']),
                'pincode' => sanitize($_POST['pincode']),
                'aadhaar_number' => sanitize($_POST['aadhaar_number']),
                'date_of_birth' => sanitize($_POST['date_of_birth']),
                'gender' => sanitize($_POST['gender']),
                'photo' => $photo_filename,
                'assigned_site_id' => isset($_POST['assigned_site_id']) ? sanitize($_POST['assigned_site_id']) : null,
                'joining_date' => sanitize($_POST['joining_date']),
                'salary_fixed' => (float)$_POST['salary_fixed'],
                'salary_per_day' => (float)$_POST['salary_per_day'],
                'username' => sanitize($_POST['username']),
                'password' => $_POST['password'], // Password hashing handled in model
                'status' => sanitize($_POST['status'])
            ];

            if ($guardModel->create($data)) {
                $message = 'Guard added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error adding guard. Please try again.';
                $messageType = 'danger';
            }
        }

        if ($action === 'edit') {
            $guard_id = (int)$_POST['guard_id'];
            $photo_filename = $_POST['photo_current'];

            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                
                if ($photo_filename) {
                    deleteFile(GUARDS_UPLOADS_PATH . $photo_filename);
                }
                $photo_filename = uploadFile($_FILES['photo'], GUARDS_UPLOADS_PATH);
            }

            $data = [
                'full_name' => sanitize($_POST['full_name']),
                'phone' => sanitize($_POST['phone']),
                'email' => sanitize($_POST['email']),
                'address' => sanitize($_POST['address']),
                'city' => sanitize($_POST['city']),
                'state' => sanitize($_POST['state']),
                'pincode' => sanitize($_POST['pincode']),
                'aadhaar_number' => sanitize($_POST['aadhaar_number']),
                'date_of_birth' => sanitize($_POST['date_of_birth']),
                'gender' => sanitize($_POST['gender']),
                'photo' => $photo_filename,
                'assigned_site_id' => isset($_POST['assigned_site_id']) ? sanitize($_POST['assigned_site_id']) : '',
                'salary_fixed' => isset($_POST['salary_fixed']) && $_POST['salary_fixed'] !== '' ? (float)$_POST['salary_fixed'] : '',
                'salary_per_day' => isset($_POST['salary_per_day']) && $_POST['salary_per_day'] !== '' ? (float)$_POST['salary_per_day'] : '',
                'username' => sanitize($_POST['username']),
                'password' => $_POST['password'],
                'status' => sanitize($_POST['status'])
            ];

            if ($guardModel->update($guard_id, $data)) {
                $message = 'Guard updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating guard. Please try again.';
                $messageType = 'danger';
            }
        }

        if ($action === 'delete') {
            $guard_id = (int)$_POST['guard_id'];
            $guard = $guardModel->getById($guard_id);

            if ($guard && $guardModel->delete($guard_id)) {
                
                if ($guard['photo']) {
                    deleteFile(GUARDS_UPLOADS_PATH . $guard['photo']);
                }
                $message = 'Guard deleted successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error deleting guard. Please try again.';
                $messageType = 'danger';
            }
        }
    }
}


$total = $guardModel->count();
$pagination = getPagination($total);


$guards = $guardModel->getAll($pagination['per_page'], $pagination['offset']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guards Management - Security Guard Management System</title>
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
                    <!-- Page Header -->
                    <div class="page-header">
                        <div>
                            <h1 class="page-title">Guards Management</h1>
                            <p class="page-subtitle">Manage all security guards</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGuardModal">
                            <i class="fas fa-plus"></i> Add New Guard
                        </button>
                    </div>

                    <!-- Messages -->
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Guard Card List or Table View -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Total Guards: <strong><?php echo $total; ?></strong></h6>
                                </div>
                                <div>
                                    <input type="text" class="form-control form-control-sm" id="searchGuard" 
                                           placeholder="Search by name, ID, or phone..."
                                           style="width: 250px;">
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="guardsTable">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Full Name</th>
                                            <th>Employee ID</th>
                                            <th>Phone</th>
                                            <th>Aadhaar</th>
                                            <th>Site</th>
                                            <th>Salary</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($guards as $guard): ?>
                                        <tr>
                                            <td>
                                                <?php if ($guard['photo']): ?>
                                                    <img src="<?php echo ASSETS_URL; ?>../uploads/guards/<?php echo $guard['photo']; ?>" 
                                                         alt="Photo" class="rounded" style="width: 32px; height: 32px; object-fit: cover;">
                                                <?php else: ?>
                                                    <img src="https://via.placeholder.com/32" alt="Photo" class="rounded">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="guard_dashboard.php?id=<?php echo $guard['id']; ?>" class="text-decoration-none text-dark fw-bold">
                                                    <?php echo htmlspecialchars($guard['full_name']); ?>
                                                </a>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($guard['email'] ?? ''); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($guard['employee_id']); ?></td>
                                            <td><?php echo htmlspecialchars($guard['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($guard['aadhaar_number'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($guard['site_name'] ?? 'Not Assigned'); ?></td>
                                            <td><?php echo formatCurrency($guard['salary_fixed'] ?? 0); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $guard['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($guard['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="guard_dashboard.php?id=<?php echo $guard['id']; ?>" class="btn btn-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-info" onclick="editGuard(<?php echo $guard['id']; ?>)"
                                                            data-bs-toggle="modal" data-bs-target="#editGuardModal"
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger" onclick="deleteGuard(<?php echo $guard['id']; ?>, '<?php echo htmlspecialchars($guard['full_name']); ?>')"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($pagination['total_pages'] > 1): ?>
                            <nav>
                                <ul class="pagination">
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

    <!-- Add Guard Modal -->
    <div class="modal fade" id="addGuardModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Guard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="addGuardForm">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="full_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-control" name="gender">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone *</label>
                                    <input type="tel" class="form-control" name="phone" pattern="[0-9]{10}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Aadhaar Number</label>
                                    <input type="text" class="form-control" name="aadhaar_number" pattern="[0-9]{12}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" name="state">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Pincode</label>
                                    <input type="text" class="form-control" name="pincode" pattern="[0-9]{6}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Joining Date *</label>
                                    <input type="date" class="form-control" name="joining_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Assigned Site</label>
                                    <select class="form-control" name="assigned_site_id">
                                        <option value="">-- Select Site --</option>
                                        <?php foreach ($sites as $site): ?>
                                        <option value="<?php echo $site['site_id']; ?>"><?php echo htmlspecialchars($site['site_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Username (App Login)</label>
                                    <input type="text" class="form-control" name="username" placeholder="e.g. guard_john">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Login Password</label>
                                    <input type="password" class="form-control" name="password" placeholder="Set initial password">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Monthly Salary</label>
                                    <input type="number" class="form-control" name="salary_fixed" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Per Day Salary</label>
                                    <input type="number" class="form-control" name="salary_per_day" step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Photo</label>
                                    <input type="file" class="form-control" name="photo" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Guard</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Guard Modal -->
    <div class="modal fade" id="editGuardModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Guard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editGuardForm">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="guard_id" id="guard_id">
                    <input type="hidden" name="photo_current" id="photo_current">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-control" name="gender" id="edit_gender">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone *</label>
                                    <input type="tel" class="form-control" name="phone" id="edit_phone" pattern="[0-9]{10}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="edit_email">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Aadhaar Number</label>
                                    <input type="text" class="form-control" name="aadhaar_number" id="edit_aadhaar_number" pattern="[0-9]{12}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth" id="edit_date_of_birth">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" id="edit_city">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" name="state" id="edit_state">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Pincode</label>
                                    <input type="text" class="form-control" name="pincode" id="edit_pincode" pattern="[0-9]{6}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Assigned Site</label>
                                    <select class="form-control" name="assigned_site_id" id="edit_assigned_site_id">
                                        <option value="">-- Select Site --</option>
                                        <?php foreach ($sites as $site): ?>
                                        <option value="<?php echo $site['site_id']; ?>"><?php echo htmlspecialchars($site['site_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status" id="edit_status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="on_leave">On Leave</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Username (App Login)</label>
                                    <input type="text" class="form-control" name="username" id="edit_username">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Monthly Salary</label>
                                    <input type="number" class="form-control" name="salary_fixed" id="edit_salary_fixed" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Per Day Salary</label>
                                    <input type="number" class="form-control" name="salary_per_day" id="edit_salary_per_day" step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                            <small class="text-muted">Leave blank to keep current photo</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Guard</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteGuardModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Guard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="deleteGuardForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="guard_id" id="delete_guard_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong id="delete_guard_name"></strong>?</p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Guard</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
    <script>
        // Setup table search
        setupTableSearch('searchGuard', 'guardsTable');

        // Guard data from PHP
        const guardsData = <?php echo json_encode($guards); ?>;

        // Edit guard function
        function editGuard(guardId) {
            const guard = guardsData.find(g => g.id == guardId);
            if (!guard) return;

            document.getElementById('guard_id').value = guard.id;
            document.getElementById('edit_full_name').value = guard.full_name || '';
            document.getElementById('edit_gender').value = guard.gender || 'male';
            document.getElementById('edit_phone').value = guard.phone || '';
            document.getElementById('edit_email').value = guard.email || '';
            document.getElementById('edit_aadhaar_number').value = guard.aadhaar_number || '';
            document.getElementById('edit_date_of_birth').value = guard.date_of_birth || '';
            document.getElementById('edit_address').value = guard.address || '';
            document.getElementById('edit_city').value = guard.city || '';
            document.getElementById('edit_state').value = guard.state || '';
            document.getElementById('edit_pincode').value = guard.pincode || '';
            document.getElementById('edit_status').value = guard.status || 'active';
            document.getElementById('edit_username').value = guard.username || '';
            
            const siteSelect = document.getElementById('edit_assigned_site_id');
            if(siteSelect) siteSelect.value = guard.assigned_site_id || '';
            
            document.getElementById('edit_salary_fixed').value = guard.salary_fixed || '';
            document.getElementById('edit_salary_per_day').value = guard.salary_per_day || '';
            document.getElementById('photo_current').value = guard.photo || '';
        }

        // Delete guard function
        function deleteGuard(guardId, guardName) {
            document.getElementById('delete_guard_id').value = guardId;
            document.getElementById('delete_guard_name').textContent = guardName;
            new bootstrap.Modal(document.getElementById('deleteGuardModal')).show();
        }
    </script>
</body>
</html>

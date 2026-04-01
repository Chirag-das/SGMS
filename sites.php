<?php
require_once 'core/init.php';

$auth->requireLogin();
$user = $auth->getUser();

$siteModel = new Site($conn);
$clientModel = new Client($conn);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $data = [
                'client_id' => (int)$_POST['client_id'],
                'site_name' => sanitize($_POST['site_name']),
                'site_code' => sanitize($_POST['site_code']),
                'site_type' => sanitize($_POST['site_type']),
                'site_address_line1' => sanitize($_POST['site_address_line1']),
                'site_address_line2' => sanitize($_POST['site_address_line2']),
                'city' => sanitize($_POST['city']),
                'state' => sanitize($_POST['state']),
                'pincode' => sanitize($_POST['pincode']),
                'site_supervisor' => sanitize($_POST['site_supervisor']),
                'supervisor_contact' => sanitize($_POST['supervisor_contact']),
                'total_guards_required' => (int)$_POST['total_guards_required'],
                'shift_type' => sanitize($_POST['shift_type']),
                'shift_hours' => (int)$_POST['shift_hours'],
                'start_time' => sanitize($_POST['start_time']),
                'end_time' => sanitize($_POST['end_time']),
                'site_status' => sanitize($_POST['site_status'])
            ];

            if ($siteModel->create($data)) {
                $message = 'Site added successfully!';
                $messageType = 'success';
            }
            else {
                $message = 'Error adding site.';
                $messageType = 'danger';
            }
        }

        if ($action === 'edit') {
            $site_id = (int)$_POST['site_id'];
            $data = [
                'client_id' => (int)$_POST['client_id'],
                'site_name' => sanitize($_POST['site_name']),
                'site_code' => sanitize($_POST['site_code']),
                'site_type' => sanitize($_POST['site_type']),
                'site_address_line1' => sanitize($_POST['site_address_line1']),
                'site_address_line2' => sanitize($_POST['site_address_line2']),
                'city' => sanitize($_POST['city']),
                'state' => sanitize($_POST['state']),
                'pincode' => sanitize($_POST['pincode']),
                'site_supervisor' => sanitize($_POST['site_supervisor']),
                'supervisor_contact' => sanitize($_POST['supervisor_contact']),
                'total_guards_required' => (int)$_POST['total_guards_required'],
                'shift_type' => sanitize($_POST['shift_type']),
                'shift_hours' => (int)$_POST['shift_hours'],
                'start_time' => sanitize($_POST['start_time']),
                'end_time' => sanitize($_POST['end_time']),
                'site_status' => sanitize($_POST['site_status'])
            ];

            if ($siteModel->update($site_id, $data)) {
                $message = 'Site updated successfully!';
                $messageType = 'success';
            }
            else {
                $message = 'Error updating site.';
                $messageType = 'danger';
            }
        }

        if ($action === 'delete') {
            $site_id = (int)$_POST['site_id'];
            if ($siteModel->delete($site_id)) {
                $message = 'Site deleted successfully!';
                $messageType = 'success';
            }
            else {
                $message = 'Error deleting site.';
                $messageType = 'danger';
            }
        }
    }
}

$total = $siteModel->count();
$pagination = getPagination($total);
$sites = $siteModel->getAll($pagination['per_page'], $pagination['offset']);
$clients = $clientModel->getAll(null, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sites Management - SGMS</title>
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
                            <h1 class="page-title">Sites Management</h1>
                            <p class="page-subtitle">Manage guard assignment sites</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSiteModal">
                            <i class="fas fa-plus"></i> Add Site
                        </button>
                    </div>

                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                    <?php
endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Total Sites: <strong><?php echo $total; ?></strong></h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Site Name</th>
                                            <th>Site Code</th>
                                            <th>Client</th>
                                            <th>City</th>
                                            <th>Guards Req</th>
                                            <th>Shift Type</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sites as $site): ?>
                                        <tr>
                                             <td>
                                                 <a href="site_dashboard.php?id=<?php echo $site['site_id']; ?>" class="text-decoration-none text-dark fw-bold">
                                                     <?php echo htmlspecialchars($site['site_name']); ?>
                                                 </a>
                                             </td>
                                            <td><?php echo htmlspecialchars($site['site_code'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($site['client_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($site['city'] ?? 'N/A'); ?></td>
                                            <td><?php echo $site['total_guards_required']; ?></td>
                                            <td><?php echo htmlspecialchars($site['shift_type'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo strtolower($site['site_status']) === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst(strtolower($site['site_status'])); ?>
                                                </span>
                                            </td>
                                             <td>
                                                 <div class="btn-group btn-group-sm">
                                                     <a href="site_dashboard.php?id=<?php echo $site['site_id']; ?>" class="btn btn-primary" title="View Details">
                                                         <i class="fas fa-eye"></i>
                                                     </a>
                                                     <button class="btn btn-sm btn-info" onclick='loadSiteData(<?php echo json_encode($site); ?>)' data-bs-toggle="modal" data-bs-target="#editSiteModal">
                                                         <i class="fas fa-edit"></i>
                                                     </button>
                                                     <button class="btn btn-sm btn-danger" onclick="deleteSite(<?php echo $site['site_id']; ?>)" data-bs-toggle="modal" data-bs-target="#deleteSiteModal">
                                                         <i class="fas fa-trash"></i>
                                                     </button>
                                                 </div>
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
    </div>

    <!-- Add Site Modal -->
    <div class="modal fade" id="addSiteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Client *</label>
                            <select class="form-control" name="client_id" required>
                                <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['company_name']); ?></option>
                                <?php
endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Name *</label>
                                <input type="text" class="form-control" name="site_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Code</label>
                                <input type="text" class="form-control" name="site_code">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site Type *</label>
                            <input type="text" class="form-control" name="site_type" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 1 *</label>
                            <textarea class="form-control" name="site_address_line1" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 2</label>
                            <textarea class="form-control" name="site_address_line2" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City *</label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">State *</label>
                                <input type="text" class="form-control" name="state" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pincode *</label>
                                <input type="text" class="form-control" name="pincode" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Supervisor</label>
                                <input type="text" class="form-control" name="site_supervisor">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Supervisor Contact</label>
                                <input type="tel" class="form-control" name="supervisor_contact">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Guards Required</label>
                                <input type="number" class="form-control" name="total_guards_required" min="0" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shift Type *</label>
                                <input type="text" class="form-control" name="shift_type" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Shift Hours *</label>
                                <input type="number" class="form-control" name="shift_hours" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" class="form-control" name="start_time">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-control" name="end_time">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="site_status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Site Modal -->
    <div class="modal fade" id="editSiteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="site_id" id="edit_site_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Client *</label>
                            <select class="form-control" name="client_id" id="edit_client_id" required>
                                <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['company_name']); ?></option>
                                <?php
endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Name *</label>
                                <input type="text" class="form-control" name="site_name" id="edit_site_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Code</label>
                                <input type="text" class="form-control" name="site_code" id="edit_site_code">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site Type *</label>
                            <input type="text" class="form-control" name="site_type" id="edit_site_type" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 1 *</label>
                            <textarea class="form-control" name="site_address_line1" id="edit_site_address_line1" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 2</label>
                            <textarea class="form-control" name="site_address_line2" id="edit_site_address_line2" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City *</label>
                                <input type="text" class="form-control" name="city" id="edit_city" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">State *</label>
                                <input type="text" class="form-control" name="state" id="edit_state" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pincode *</label>
                                <input type="text" class="form-control" name="pincode" id="edit_pincode" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Supervisor</label>
                                <input type="text" class="form-control" name="site_supervisor" id="edit_site_supervisor">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Supervisor Contact</label>
                                <input type="tel" class="form-control" name="supervisor_contact" id="edit_supervisor_contact">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Guards Required</label>
                                <input type="number" class="form-control" name="total_guards_required" id="edit_total_guards_required" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shift Type *</label>
                                <input type="text" class="form-control" name="shift_type" id="edit_shift_type" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Shift Hours *</label>
                                <input type="number" class="form-control" name="shift_hours" id="edit_shift_hours" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" class="form-control" name="start_time" id="edit_start_time">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-control" name="end_time" id="edit_end_time">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="site_status" id="edit_site_status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Conf modal -->
    <div class="modal fade" id="deleteSiteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="site_id" id="delete_site_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete this site?</p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
    <script>
        function loadSiteData(site) {
            document.getElementById('edit_site_id').value = site.site_id;
            document.getElementById('edit_client_id').value = site.client_id;
            document.getElementById('edit_site_name').value = site.site_name;
            document.getElementById('edit_site_code').value = site.site_code || '';
            document.getElementById('edit_site_type').value = site.site_type;
            document.getElementById('edit_site_address_line1').value = site.site_address_line1;
            document.getElementById('edit_site_address_line2').value = site.site_address_line2 || '';
            document.getElementById('edit_city').value = site.city;
            document.getElementById('edit_state').value = site.state;
            document.getElementById('edit_pincode').value = site.pincode;
            document.getElementById('edit_site_supervisor').value = site.site_supervisor || '';
            document.getElementById('edit_supervisor_contact').value = site.supervisor_contact || '';
            document.getElementById('edit_total_guards_required').value = site.total_guards_required;
            document.getElementById('edit_shift_type').value = site.shift_type;
            document.getElementById('edit_shift_hours').value = site.shift_hours;
            document.getElementById('edit_start_time').value = site.start_time || '';
            document.getElementById('edit_end_time').value = site.end_time || '';
            document.getElementById('edit_site_status').value = site.site_status;
        }

        function deleteSite(siteId) {
            document.getElementById('delete_site_id').value = siteId;
        }
    </script>
</body>
</html>

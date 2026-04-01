<?php
require_once 'core/init.php';

$auth->requireLogin();
$user = $auth->getUser();

$clientModel = new Client($conn);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $data = [
                'company_name' => sanitize($_POST['company_name']),
                'contact_person' => sanitize($_POST['contact_person']),
                'contact_number' => sanitize($_POST['contact_number']),
                'alternate_number' => sanitize($_POST['alternate_number']),
                'email' => sanitize($_POST['email']),
                'address_line1' => sanitize($_POST['address_line1']),
                'address_line2' => sanitize($_POST['address_line2']),
                'city' => sanitize($_POST['city']),
                'state' => sanitize($_POST['state']),
                'pincode' => sanitize($_POST['pincode']),
                'gst_number' => sanitize($_POST['gst_number']),
                'contract_start_date' => sanitize($_POST['contract_start_date']),
                'contract_end_date' => sanitize($_POST['contract_end_date']),
                'number_of_guards' => (int)$_POST['number_of_guards'],
                'service_type' => sanitize($_POST['service_type']),
                'billing_cycle' => sanitize($_POST['billing_cycle']),
                'status' => sanitize($_POST['status'])
            ];

            if ($clientModel->create($data)) {
                $message = 'Client added successfully!';
                $messageType = 'success';
            }
            else {
                $message = 'Error adding client. Please try again.';
                $messageType = 'danger';
            }
        }

        if ($action === 'edit') {
            $client_id = (int)$_POST['client_id'];

            $data = [
                'company_name' => sanitize($_POST['company_name']),
                'contact_person' => sanitize($_POST['contact_person']),
                'contact_number' => sanitize($_POST['contact_number']),
                'alternate_number' => sanitize($_POST['alternate_number']),
                'email' => sanitize($_POST['email']),
                'address_line1' => sanitize($_POST['address_line1']),
                'address_line2' => sanitize($_POST['address_line2']),
                'city' => sanitize($_POST['city']),
                'state' => sanitize($_POST['state']),
                'pincode' => sanitize($_POST['pincode']),
                'gst_number' => sanitize($_POST['gst_number']),
                'contract_start_date' => sanitize($_POST['contract_start_date']),
                'contract_end_date' => sanitize($_POST['contract_end_date']),
                'number_of_guards' => (int)$_POST['number_of_guards'],
                'service_type' => sanitize($_POST['service_type']),
                'billing_cycle' => sanitize($_POST['billing_cycle']),
                'status' => sanitize($_POST['status'])
            ];

            if ($clientModel->update($client_id, $data)) {
                $message = 'Client updated successfully!';
                $messageType = 'success';
            }
            else {
                $message = 'Error updating client. Please try again.';
                $messageType = 'danger';
            }
        }

        if ($action === 'delete') {
            $client_id = (int)$_POST['client_id'];
            if ($clientModel->delete($client_id)) {
                $message = 'Client deleted successfully!';
                $messageType = 'success';
            }
            else {
                $message = 'Error deleting client. Please try again.';
                $messageType = 'danger';
            }
        }
    }
}

$total = $clientModel->count();
$pagination = getPagination($total);

$clients = $clientModel->getAll($pagination['per_page'], $pagination['offset']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients Management - Security Guard Management System</title>
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
                            <h1 class="page-title">Clients Management</h1>
                            <p class="page-subtitle">Manage client companies</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                            <i class="fas fa-plus"></i> Add New Client
                        </button>
                    </div>

                    <!-- Messages -->
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php
endif; ?>

                    <!-- Clients Table -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">Total Clients: <strong><?php echo $total; ?></strong></h6>
                                <input type="text" class="form-control form-control-sm" id="searchClient" 
                                       placeholder="Search by company name..." style="width: 250px;">
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="clientsTable">
                                    <thead>
                                        <tr>
                                            <th>Company Name</th>
                                            <th>Contact Person</th>
                                            <th>Contact Number</th>
                                            <th>Email</th>
                                            <th>City</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clients as $client): ?>
                                        <tr>
                                             <td>
                                                 <a href="client_dashboard.php?id=<?php echo $client['client_id']; ?>" class="text-decoration-none text-dark fw-bold">
                                                     <?php echo htmlspecialchars($client['company_name']); ?>
                                                 </a>
                                             </td>
                                            <td><?php echo htmlspecialchars($client['contact_person'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($client['contact_number'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($client['email'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($client['city'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo strtolower($client['status']) === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst(strtolower($client['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="client_dashboard.php?id=<?php echo $client['client_id']; ?>" class="btn btn-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-info" onclick='loadClientData(<?php echo json_encode($client); ?>)'
                                                            data-bs-toggle="modal" data-bs-target="#editClientModal" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger" onclick="deleteClient(<?php echo $client['client_id']; ?>, '<?php echo htmlspecialchars(addslashes($client['company_name'])); ?>')"
                                                            data-bs-toggle="modal" data-bs-target="#deleteClientModal">
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

                            <!-- Pagination -->
                            <?php if ($pagination['total_pages'] > 1): ?>
                            <nav>
                                <ul class="pagination">
                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                    <?php
    endfor; ?>
                                </ul>
                            </nav>
                            <?php
endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Client Modal -->
    <div class="modal fade" id="addClientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name *</label>
                                <input type="text" class="form-control" name="company_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person *</label>
                                <input type="text" class="form-control" name="contact_person" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Number *</label>
                                <input type="tel" class="form-control" name="contact_number" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alternate Number</label>
                                <input type="tel" class="form-control" name="alternate_number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GST Number</label>
                                <input type="text" class="form-control" name="gst_number">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 1 *</label>
                            <input type="text" class="form-control" name="address_line1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" name="address_line2">
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
                                <label class="form-label">Contract Start Date *</label>
                                <input type="date" class="form-control" name="contract_start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contract End Date *</label>
                                <input type="date" class="form-control" name="contract_end_date" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Number of Guards</label>
                                <input type="number" class="form-control" name="number_of_guards" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Service Type *</label>
                                <input type="text" class="form-control" name="service_type" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Billing Cycle *</label>
                                <select class="form-control" name="billing_cycle" required>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Client Modal -->
    <div class="modal fade" id="editClientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="client_id" id="edit_client_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name *</label>
                                <input type="text" class="form-control" name="company_name" id="edit_company_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person *</label>
                                <input type="text" class="form-control" name="contact_person" id="edit_contact_person" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Number *</label>
                                <input type="tel" class="form-control" name="contact_number" id="edit_contact_number" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alternate Number</label>
                                <input type="tel" class="form-control" name="alternate_number" id="edit_alternate_number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="edit_email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GST Number</label>
                                <input type="text" class="form-control" name="gst_number" id="edit_gst_number">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 1 *</label>
                            <input type="text" class="form-control" name="address_line1" id="edit_address_line1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" name="address_line2" id="edit_address_line2">
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
                                <label class="form-label">Contract Start Date *</label>
                                <input type="date" class="form-control" name="contract_start_date" id="edit_contract_start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contract End Date *</label>
                                <input type="date" class="form-control" name="contract_end_date" id="edit_contract_end_date" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Number of Guards</label>
                                <input type="number" class="form-control" name="number_of_guards" id="edit_number_of_guards">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Service Type *</label>
                                <input type="text" class="form-control" name="service_type" id="edit_service_type" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Billing Cycle *</label>
                                <select class="form-control" name="billing_cycle" id="edit_billing_cycle" required>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="edit_status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Client Modal -->
    <div class="modal fade" id="deleteClientModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="client_id" id="delete_client_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong id="delete_client_name"></strong>?</p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
    <script>
        setupTableSearch('searchClient', 'clientsTable');

        function loadClientData(client) {
            document.getElementById('edit_client_id').value = client.client_id;
            document.getElementById('edit_company_name').value = client.company_name;
            document.getElementById('edit_contact_person').value = client.contact_person;
            document.getElementById('edit_contact_number').value = client.contact_number;
            document.getElementById('edit_alternate_number').value = client.alternate_number;
            document.getElementById('edit_email').value = client.email;
            document.getElementById('edit_address_line1').value = client.address_line1;
            document.getElementById('edit_address_line2').value = client.address_line2;
            document.getElementById('edit_city').value = client.city;
            document.getElementById('edit_state').value = client.state;
            document.getElementById('edit_pincode').value = client.pincode;
            document.getElementById('edit_gst_number').value = client.gst_number;
            document.getElementById('edit_contract_start_date').value = client.contract_start_date;
            document.getElementById('edit_contract_end_date').value = client.contract_end_date;
            document.getElementById('edit_number_of_guards').value = client.number_of_guards;
            document.getElementById('edit_service_type').value = client.service_type;
            document.getElementById('edit_billing_cycle').value = client.billing_cycle;
            document.getElementById('edit_status').value = client.status;
        }

        function deleteClient(clientId, clientName) {
            document.getElementById('delete_client_id').value = clientId;
            document.getElementById('delete_client_name').textContent = clientName;
        }
    </script>
</body>
</html>

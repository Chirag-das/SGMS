<?php
require_once 'core/init.php';

// Require user to be logged in
$auth->requireLogin();

$user = $auth->getUser();

// Check if client ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'clients.php');
    exit;
}

$client_id = (int)$_GET['id'];

// Initialize models
$clientModel = new Client($conn);
$guardModel = new Guard($conn);
$siteModel = new Site($conn);

// Get client details
$client = $clientModel->getById($client_id);

if (!$client) {
    // Client not found
    header('Location: ' . BASE_URL . 'clients.php');
    exit;
}

// Custom queries to get client-specific stats
// Get all sites for this client
$sites_query = "SELECT * FROM sites WHERE client_id = {$client_id}";
$sites_result = $conn->query($sites_query);
$sites = $sites_result ? $sites_result->fetch_all(MYSQLI_ASSOC) : [];

// Count active sites and total deployed guards
$active_sites = 0;
$total_deployed_guards = 0;
$site_ids = [];

foreach ($sites as $site) {
    if ($site['site_status'] === 'active') {
        $active_sites++;
    }
    $total_deployed_guards += (int)$site['total_guards_required'];
    $site_ids[] = $site['site_id'];
}

// Get recent guards assigned to this client's sites
$recent_guards = [];
if (!empty($site_ids)) {
    $site_ids_str = implode(',', $site_ids);
    $guards_query = "SELECT g.*, s.site_name 
                     FROM guards g 
                     JOIN sites s ON g.assigned_site_id = s.site_id 
                     WHERE g.assigned_site_id IN ({$site_ids_str}) 
                     ORDER BY g.created_at DESC LIMIT 5";
    $guards_result = $conn->query($guards_query);
    if ($guards_result) {
        $recent_guards = $guards_result->fetch_all(MYSQLI_ASSOC);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Security Guard Management System</title>
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
                            <h1 class="page-title">Client Dashboard</h1>
                            <p class="page-subtitle">Overview & Details for <?php echo htmlspecialchars($client['company_name']); ?></p>
                        </div>
                        <a href="clients.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Clients
                        </a>
                    </div>

                    <div class="row g-4">
                        <!-- Company Info Card -->
                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fas fa-building text-primary me-2"></i> Company Details</h6>
                                </div>
                                <div class="card-body text-center mt-3">
                                    <div class="rounded-circle bg-light d-inline-flex justify-content-center align-items-center mb-3 border shadow-sm" style="width: 120px; height: 120px;">
                                        <i class="fas fa-building fa-4x text-secondary"></i>
                                    </div>
                                    
                                    <h4 class="mb-1"><?php echo htmlspecialchars($client['company_name']); ?></h4>
                                    <p class="text-muted mb-2">GST: <?php echo htmlspecialchars($client['gst_number'] ?? 'N/A'); ?></p>
                                    <span class="badge bg-<?php echo strtolower($client['status']) === 'active' ? 'success' : 'secondary'; ?> mb-4 px-3 py-2 border">
                                        <?php echo ucfirst(strtolower($client['status'])); ?>
                                    </span>
                                    
                                    <ul class="list-group list-group-flush text-start">
                                        <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                                            <span><i class="fas fa-user-tie text-muted me-2 mt-1"></i> Contact:</span>
                                            <div class="text-end">
                                                <strong><?php echo htmlspecialchars($client['contact_person']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($client['contact_number']); ?></small><br>
                                                <?php if($client['email']): ?><small class="text-muted"><?php echo htmlspecialchars($client['email']); ?></small><?php endif; ?>
                                            </div>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="fas fa-file-contract text-muted me-2"></i> Contract Period:</span>
                                            <div class="text-end">
                                                <strong><?php echo date('M Y', strtotime($client['contract_start_date'])); ?> - <?php echo date('M Y', strtotime($client['contract_end_date'])); ?></strong>
                                            </div>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="fas fa-sync-alt text-muted me-2"></i> Billing Cycle:</span>
                                            <strong><?php echo htmlspecialchars($client['billing_cycle']); ?></strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics & Data -->
                        <div class="col-lg-8">
                            
                            <!-- Key Metrics -->
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6 col-md-4">
                                    <div class="card bg-primary text-white h-100">
                                        <div class="card-body py-4 text-center">
                                            <h6 class="card-title fw-normal mb-2 opacity-75">Contracted Guards</h6>
                                            <h2 class="mb-0 fw-bold"><?php echo htmlspecialchars($client['number_of_guards']); ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="card bg-info text-white h-100">
                                        <div class="card-body py-4 text-center">
                                            <h6 class="card-title fw-normal mb-2 opacity-75">Guards Appointed</h6>
                                            <h2 class="mb-0 fw-bold"><?php echo $total_deployed_guards; ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="card bg-success text-white h-100">
                                        <div class="card-body py-4 text-center">
                                            <h6 class="card-title fw-normal mb-2 opacity-75">Active Sites</h6>
                                            <h2 class="mb-0 fw-bold"><?php echo count($sites); ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <!-- Sites List -->
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0">Sites for this Client</h6>
                                            <a href="sites.php" class="btn-link text-decoration-none">Manage Sites</a>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Site Name</th>
                                                            <th>Location</th>
                                                            <th>Guards Req.</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($sites)): ?>
                                                            <?php foreach ($sites as $site): ?>
                                                            <tr onclick="window.location='site_dashboard.php?id=<?php echo $site['site_id']; ?>'" style="cursor:pointer;" title="Click to view site details">
                                                                <td><strong><?php echo htmlspecialchars($site['site_name']); ?></strong></td>
                                                                <td><?php echo htmlspecialchars($site['city']); ?>, <?php echo htmlspecialchars($site['state']); ?></td>
                                                                <td><?php echo htmlspecialchars($site['total_guards_required']); ?></td>
                                                                <td>
                                                                    <span class="badge bg-<?php echo strtolower($site['site_status']) === 'active' ? 'success' : 'secondary'; ?>">
                                                                        <?php echo ucfirst(strtolower($site['site_status'] ?? '')); ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="4" class="text-center py-4 text-muted">No sites assigned to this client.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recently Assigned Guards -->
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0">Recently Deployed Guards</h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Guard Name</th>
                                                            <th>Employee ID</th>
                                                            <th>Assigned Site</th>
                                                            <th>Contact</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($recent_guards)): ?>
                                                            <?php foreach ($recent_guards as $g): ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <?php if ($g['photo']): ?>
                                                                            <img src="<?php echo ASSETS_URL; ?>../uploads/guards/<?php echo htmlspecialchars($g['photo']); ?>" class="rounded-circle me-2" style="width:32px; height:32px; object-fit:cover;">
                                                                        <?php else: ?>
                                                                            <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2 text-primary fw-bold" style="width:32px; height:32px; font-size:12px;">
                                                                                <?php echo substr($g['full_name'], 0, 1); ?>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        <strong><?php echo htmlspecialchars($g['full_name']); ?></strong>
                                                                    </div>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($g['employee_id']); ?></td>
                                                                <td><?php echo htmlspecialchars($g['site_name']); ?></td>
                                                                <td><?php echo htmlspecialchars($g['phone']); ?></td>
                                                                <td>
                                                                    <a href="guard_dashboard.php?id=<?php echo $g['id']; ?>" class="btn btn-sm btn-light border" title="View Guard">
                                                                        <i class="fas fa-external-link-alt text-primary"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="5" class="text-center py-4 text-muted">No guards currently deployed at this client's sites.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address Details -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fas fa-map-marker-alt text-danger me-2"></i> Client Address</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <small class="text-muted d-block">Street Address</small>
                                            <span class="fw-medium font-monospace"><?php echo htmlspecialchars($client['address_line1']); ?> <?php echo htmlspecialchars($client['address_line2']); ?></span>
                                        </div>
                                        <div class="col-md-4 mb-2 mt-2">
                                            <small class="text-muted d-block">City</small>
                                            <span class="fw-medium"><?php echo htmlspecialchars($client['city']); ?></span>
                                        </div>
                                        <div class="col-md-4 mb-2 mt-2">
                                            <small class="text-muted d-block">State</small>
                                            <span class="fw-medium"><?php echo htmlspecialchars($client['state']); ?></span>
                                        </div>
                                        <div class="col-md-4 mb-2 mt-2">
                                            <small class="text-muted d-block">Pincode</small>
                                            <span class="fw-medium"><?php echo htmlspecialchars($client['pincode']); ?></span>
                                        </div>
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

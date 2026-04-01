<?php
require_once 'core/init.php';

$auth->requireLogin();
$user = $auth->getUser();

$guardModel = new Guard($conn);
$clientModel = new Client($conn);

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

$guards = [];
$clients = [];

if ($query !== '') {
    $guards = $guardModel->search($query, 50);
    $clients = $clientModel->search($query, 50);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Security Guard Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
    <style>
        .search-term { font-weight: bold; color: var(--primary-color); }
    </style>
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
                            <h1 class="page-title">Search Results</h1>
                            <?php if ($query !== ''): ?>
                                <p class="page-subtitle">Showing results for: <span class="search-term">"<?php echo htmlspecialchars($query); ?>"</span></p>
                            <?php else: ?>
                                <p class="page-subtitle">Enter a search term above to find guards and clients.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($query !== ''): ?>
                        <div class="row g-4">
                            <!-- Guards Results -->
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">Guards (<?php echo count($guards); ?>)</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (count($guards) > 0): ?>
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($guards as $guard): ?>
                                                    <a href="guard_dashboard.php?id=<?php echo $guard['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                                                        <div class="d-flex align-items-center">
                                                            <?php if ($guard['photo']): ?>
                                                                <img src="<?php echo ASSETS_URL; ?>../uploads/guards/<?php echo htmlspecialchars($guard['photo']); ?>" alt="Photo" class="rounded-circle me-3" style="width: 48px; height: 48px; object-fit: cover;">
                                                            <?php else: ?>
                                                                <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                                                    <?php echo strtoupper(substr($guard['full_name'], 0, 1)); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div>
                                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($guard['full_name']); ?></h6>
                                                                <small class="text-muted"><i class="fas fa-id-badge me-1"></i><?php echo htmlspecialchars($guard['employee_id']); ?> | <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($guard['phone']); ?></small>
                                                            </div>
                                                        </div>
                                                        <span class="badge bg-<?php echo $guard['status'] === 'active' ? 'success' : 'secondary'; ?> rounded-pill">
                                                            <?php echo ucfirst($guard['status']); ?>
                                                        </span>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="p-4 text-center text-muted">
                                                <i class="fas fa-search fs-3 mb-2 opacity-50"></i>
                                                <p class="mb-0">No guards found matching "<?php echo htmlspecialchars($query); ?>"</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Clients Results -->
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">Clients (<?php echo count($clients); ?>)</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (count($clients) > 0): ?>
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($clients as $client): ?>
                                                    <a href="client_dashboard.php?id=<?php echo $client['client_id']; ?>" class="list-group-item list-group-item-action p-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <h6 class="mb-0 fw-bold"><i class="fas fa-building text-primary me-2"></i><?php echo htmlspecialchars($client['company_name']); ?></h6>
                                                            <span class="badge bg-<?php echo strtolower($client['status']) === 'active' ? 'success' : 'secondary'; ?> rounded-pill">
                                                                <?php echo ucfirst(strtolower($client['status'])); ?>
                                                            </span>
                                                        </div>
                                                        <div class="ps-4 ms-2 text-muted small">
                                                            <div class="mb-1"><i class="fas fa-user-tie me-1"></i> <?php echo htmlspecialchars($client['contact_person']); ?></div>
                                                            <div><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($client['contact_number']); ?></div>
                                                        </div>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="p-4 text-center text-muted">
                                                <i class="fas fa-search fs-3 mb-2 opacity-50"></i>
                                                <p class="mb-0">No clients found matching "<?php echo htmlspecialchars($query); ?>"</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
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

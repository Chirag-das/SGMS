<?php
require_once 'core/init.php';

$auth->requireLogin();
$auth->requireAdmin();

$user = $auth->getUser();

// Fetch current logo
$logoQuery = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'company_logo'");
$logoResult = $logoQuery ? $logoQuery->fetch_assoc() : null;
$companyLogo = ($logoResult && !empty($logoResult['setting_value'])) ? $logoResult['setting_value'] : '';
if ($logoQuery) $logoQuery->free();

// Handle logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_logo'])) {
    $uploadDir = ROOT_PATH . '/uploads/system/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $logo = uploadFile($_FILES['company_logo_file'], $uploadDir);
    if ($logo) {
        $logoPath = 'uploads/system/' . $logo;
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'company_logo'");
        $stmt->bind_param("s", $logoPath);
        $stmt->execute();
        $_SESSION['success_msg'] = "Company logo updated successfully!";
        redirect(BASE_URL . 'settings.php');
    } else {
        $_SESSION['error_msg'] = "Failed to upload logo. Please check file type and size.";
        redirect(BASE_URL . 'settings.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - SGMS</title>
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
                    <?php if (isset($_SESSION['success_msg'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error_msg'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <div class="page-header">
                        <div>
                            <h1 class="page-title">System Settings</h1>
                            <p class="page-subtitle">Configure system preferences</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="list-group">
                                <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                                    <i class="fas fa-cog"></i> General Settings
                                </a>
                                <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                    <i class="fas fa-lock"></i> Security
                                </a>

                                <a href="#backup" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                    <i class="fas fa-database"></i> Backup & Data
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-9">
                            <div id="general" class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">General Settings</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Company Logo Upload -->
                                    <div class="mb-4 pb-4" style="border-bottom: 1px solid #e2e8f0;">
                                        <h6 class="mb-3"><i class="fas fa-image me-2 text-primary"></i>Company Logo</h6>
                                        <div class="d-flex align-items-center gap-4">
                                            <div class="text-center" style="min-width: 100px;">
                                                <?php if ($companyLogo): ?>
                                                    <img src="<?php echo BASE_URL . $companyLogo; ?>" alt="Company Logo" 
                                                         style="max-width: 100px; max-height: 100px; border-radius: 12px; border: 2px solid #e2e8f0; padding: 4px; background: #f8fafc;">
                                                <?php else: ?>
                                                    <div style="width: 100px; height: 100px; border-radius: 12px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; background: #f8fafc;">
                                                        <i class="fas fa-shield-alt fa-2x text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <small class="text-muted d-block mt-1">Current Logo</small>
                                            </div>
                                            <form method="POST" enctype="multipart/form-data" class="flex-grow-1">
                                                <label class="form-label">Upload New Logo</label>
                                                <input type="file" name="company_logo_file" class="form-control mb-2" accept="image/*" required>
                                                <small class="text-muted d-block mb-2">Accepted: JPG, PNG, GIF. Max size: 5MB</small>
                                                <button type="submit" name="upload_logo" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-upload me-1"></i>Upload Logo
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Other General Settings -->
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label">System Name</label>
                                            <input type="text" class="form-control" value="Security Guard Management System">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">System Email</label>
                                            <input type="email" class="form-control" value="admin@sgms.local">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" class="form-control" value="Your Company Name">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Company Address</label>
                                            <textarea class="form-control" rows="3"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>

                            <div id="security" class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Security Settings</h6>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label">Session Timeout (minutes)</label>
                                            <input type="number" class="form-control" value="60">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Failed Login Attempts</label>
                                            <input type="number" class="form-control" value="5">
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="forceSSL" checked>
                                            <label class="form-check-label" for="forceSSL">
                                                Force HTTPS Connection
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>



                            <div id="backup" class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Backup & Database</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6>Database Information</h6>
                                        <dl class="row">
                                            <dt class="col-sm-4">Database Name:</dt>
                                            <dd class="col-sm-8"><?php echo DB_NAME; ?></dd>
                                            <dt class="col-sm-4">Database Host:</dt>
                                            <dd class="col-sm-8"><?php echo DB_HOST; ?></dd>
                                        </dl>
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-warning">
                                            <i class="fas fa-download"></i> Backup Database
                                        </button>
                                    </div>
                                    <div class="alert alert-warning">
                                        <strong>Note:</strong> Regular backups are recommended for data safety.
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

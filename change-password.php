<?php
require_once 'core/init.php';

// Check login
$auth->requireLogin();

$user = $auth->getUser();
$message = '';
$messageType = '';

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $messageType = "danger";
    }
    elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
        $messageType = "danger";
    }
    elseif (strlen($new_password) < 6) {
        $message = "New password must be at least 6 characters long.";
        $messageType = "danger";
    }
    else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $userData = $result->fetch_assoc();
            if (password_verify($current_password, $userData['password'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $hashed_password, $user['id']);

                if ($updateStmt->execute()) {
                    $message = "Password successfully changed.";
                    $messageType = "success";
                }
                else {
                    $message = "Failed to update password. Please try again.";
                    $messageType = "danger";
                }
            }
            else {
                $message = "Incorrect current password.";
                $messageType = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Security Guard Management System</title>
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
                            <h1 class="page-title">Change Password</h1>
                            <p class="page-subtitle">Update your account security</p>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php
endif; ?>

                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="card mt-4">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="fas fa-lock text-primary me-2"></i> Password Settings
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label class="form-label">Current Password *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                <input type="password" name="current_password" class="form-control" required>
                                            </div>
                                        </div>
                                        <hr class="my-4">
                                        <div class="mb-3">
                                            <label class="form-label">New Password *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" name="new_password" class="form-control" required>
                                            </div>
                                            <div class="form-text">Must be at least 6 characters long.</div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Confirm New Password *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" name="confirm_password" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" name="change_password" class="btn btn-primary">
                                                <i class="fas fa-check-circle me-2"></i> Update Password
                                            </button>
                                        </div>
                                    </form>
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

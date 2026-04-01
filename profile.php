<?php
require_once 'core/init.php';

// Check login
$auth->requireLogin();

$user = $auth->getUser();
$message = '';
$messageType = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $photo_filename = $user['photo'];

    // Handle Photo Upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploaded_file = uploadFile($_FILES['photo'], ADMINS_UPLOADS_PATH);
        if ($uploaded_file) {
            // Delete old photo if exists
            if ($photo_filename && file_exists(ADMINS_UPLOADS_PATH . $photo_filename)) {
                unlink(ADMINS_UPLOADS_PATH . $photo_filename);
            }
            $photo_filename = $uploaded_file;
        }
    }

    // Basic validation
    if (empty($full_name) || empty($email)) {
        $message = "Full Name and Email are required.";
        $messageType = "danger";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "danger";
    }
    else {
        // Check if email exists for other admins
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "This email is already registered to another account.";
            $messageType = "danger";
        }
        else {
            // Update admin profile
            $updateStmt = $conn->prepare("UPDATE admins SET full_name = ?, email = ?, phone = ?, photo = ? WHERE id = ?");
            $updateStmt->bind_param("ssssi", $full_name, $email, $phone, $photo_filename, $user['id']);

            if ($updateStmt->execute()) {
                // Update session variables
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $_SESSION['photo'] = $photo_filename;

                // Get fresh user data
                $refreshStmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
                $refreshStmt->bind_param("i", $user['id']);
                $refreshStmt->execute();
                $user = $refreshStmt->get_result()->fetch_assoc();

                $message = "Profile updated successfully.";
                $messageType = "success";
            }
            else {
                $message = "Failed to update profile. Please try again.";
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
    <title>My Profile - Security Guard Management System</title>
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
                            <h1 class="page-title">My Profile</h1>
                            <p class="page-subtitle">Manage your personal information</p>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php
endif; ?>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card text-center p-4">
                                <div class="mb-3">
                                    <?php if ($user['photo']): ?>
                                        <img src="<?php echo ASSETS_URL; ?>../uploads/admins/<?php echo $user['photo']; ?>" alt="Profile" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/150" alt="Profile" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                                <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                <p class="text-muted mb-1"><?php echo htmlspecialchars($user['role']); ?></p>
                                <p class="text-muted"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?></p>
                                <hr>
                                <div class="d-flex justify-content-between text-muted mt-3">
                                    <small>Member since:</small>
                                    <small><strong><?php echo date('M Y', strtotime($user['created_at'])); ?></strong></small>
                                </div>
                                <div class="d-flex justify-content-between text-muted mt-2">
                                    <small>Last Login:</small>
                                    <small><strong><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></strong></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Edit Profile Details</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Username</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly disabled>
                                                <div class="form-text">Username cannot be changed.</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Role</label>
                                                <input type="text" class="form-control mb-3" value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" readonly disabled>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="form-label">Full Name *</label>
                                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label">Email Address *</label>
                                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Phone Number</label>
                                                <input type="text" name="phone" class="form-control" value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <label class="form-label">Profile Photo</label>
                                                <input type="file" name="photo" class="form-control" accept="image/*">
                                                <div class="form-text">Leave blank to keep the current photo.</div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" name="update_profile" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i> Save Changes
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

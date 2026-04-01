<?php
require_once 'core/init.php';

if ($auth->isGuardLoggedIn()) {
    header('Location: ' . BASE_URL . 'guard_portal.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if ($auth->guardLogin($username, $password)) {
        header('Location: ' . BASE_URL . 'guard_portal.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

// Fetch system settings for logo/name
$nameQuery = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'company_name'");
$companyName = $nameQuery ? $nameQuery->fetch_assoc()['setting_value'] : 'SGMS';

$logoQuery = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'company_logo'");
$companyLogo = $logoQuery ? $logoQuery->fetch_assoc()['setting_value'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guard Login - <?php echo htmlspecialchars($companyName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
    <style>
        body { background: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-card { width: 100%; max-width: 400px; padding: 2rem; border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); border-radius: 1rem; background: white; }
        .login-header { text-align: center; margin-bottom: 2rem; }
        .login-header i { font-size: 3rem; color: #3b82f6; }
        .login-header img { max-width: 120px; height: auto; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <?php if ($companyLogo): ?>
                <img src="<?php echo ASSETS_URL; ?>../uploads/system/<?php echo $companyLogo; ?>" alt="Logo">
            <?php else: ?>
                <i class="fas fa-user-shield"></i>
            <?php endif; ?>
            <h3 class="mt-3">Guard Portal</h3>
            <p class="text-muted">Sign in to manage your leaves</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control" required placeholder="Enter username">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" required placeholder="Enter password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 mt-2 shadow-sm">
                Login as Guard
            </button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="login.php" class="text-decoration-none small text-muted">Admin Login</a>
        </div>
    </div>
</body>
</html>

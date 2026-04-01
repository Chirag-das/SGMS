<?php
require_once 'core/init.php';


if ($auth->isLoggedIn()) {
    redirect(BASE_URL . 'dashboard.php');
}

$error = '';

// Fetch system settings
$logoQuery = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'company_logo'");
$logoResult = $logoQuery ? $logoQuery->fetch_assoc() : null;
$companyLogo = ($logoResult && !empty($logoResult['setting_value'])) ? $logoResult['setting_value'] : '';
if ($logoQuery) $logoQuery->free();

$nameQuery = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'company_name'");
$nameResult = $nameQuery ? $nameQuery->fetch_assoc() : null;
$companyName = ($nameResult && !empty($nameResult['setting_value'])) ? $nameResult['setting_value'] : 'SGMS';
if ($nameQuery) $nameQuery->free();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $username = sanitize($_POST['username']);
        $password = sanitize($_POST['password']);

        if (empty($username) || empty($password)) {
            $error = 'Please enter username and password';
        } else {
            if ($auth->login($username, $password)) {
                redirect(BASE_URL . 'dashboard.php');
            } else {
                $error = MSG_INVALID_LOGIN;
            }
        }
    } elseif (isset($_POST['guard_login'])) {
        $username = sanitize($_POST['username']);
        $password = sanitize($_POST['password']);

        if (empty($username) || empty($password)) {
            $error = 'Please enter username and password';
        } else {
            if ($auth->guardLogin($username, $password)) {
                redirect(BASE_URL . 'guard_portal.php');
            } else {
                $error = 'Invalid guard username or password.';
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
    <title>Login - <?php echo $companyName; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            position: relative;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Animated gradient background */
        body::before {
            content: '';
            position: fixed;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(ellipse at 20% 50%, rgba(99,102,241,.15) 0%, transparent 50%),
                        radial-gradient(ellipse at 80% 50%, rgba(20,184,166,.1) 0%, transparent 50%),
                        radial-gradient(ellipse at 50% 0%, rgba(139,92,246,.1) 0%, transparent 50%);
            z-index: 0;
            animation: bgFloat 15s ease-in-out infinite alternate;
        }

        @keyframes bgFloat {
            0%   { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-30px, -20px) rotate(2deg); }
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: url('assets/images/security-guard-bg.jpg.png') center/cover no-repeat;
            opacity: .06;
            z-index: 0;
            filter: grayscale(1);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: rgba(255,255,255,.97);
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,.5), 0 0 0 1px rgba(255,255,255,.08);
            overflow: hidden;
            animation: slideUp .5s cubic-bezier(.4,0,.2,1);
            position: relative;
            z-index: 10;
            backdrop-filter: blur(20px);
        }

        .nav-tabs {
            border-bottom: none;
            background: #f8fafc;
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2.5px solid transparent;
            color: #94a3b8;
            font-weight: 600;
            font-size: 13px;
            padding: 14px;
            border-radius: 0;
            transition: all .25s ease;
            text-align: center;
            width: 50%;
            letter-spacing: .3px;
        }

        .nav-tabs .nav-link:hover {
            background: #f1f5f9;
            color: #6366f1;
        }

        .nav-tabs .nav-link.active {
            background: white;
            color: #6366f1;
            border-bottom-color: #6366f1;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .login-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 36px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Subtle decorative accent */
        .login-header::before {
            content: '';
            position: absolute;
            top: -40%; right: -20%;
            width: 200px; height: 200px;
            background: rgba(99,102,241,.15);
            border-radius: 50%;
            filter: blur(40px);
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: -.5px;
            position: relative;
        }

        .login-header p {
            font-size: 13px;
            opacity: .7;
            margin: 0;
            font-weight: 400;
            position: relative;
        }

        .login-header i, .login-header img {
            font-size: 40px;
            margin-bottom: 14px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            max-width: 80px;
            height: auto;
            position: relative;
        }

        .login-header i {
            color: #6366f1;
            background: rgba(99,102,241,.15);
            width: 60px; height: 60px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 16px;
            margin-left: auto; margin-right: auto;
            font-size: 26px;
        }



        .login-body { padding: 32px 28px; }

        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            margin-bottom: 6px;
            color: #334155;
            font-weight: 600;
            font-size: 13px;
        }

        .form-control {
            padding: 11px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 13.5px;
            transition: all .2s ease;
            background: #f8fafc;
            color: #334155;
        }

        .form-control:focus {
            background: white;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,.1);
            outline: none;
        }

        .form-control::placeholder { color: #cbd5e1; }

        .input-group-text {
            background: transparent;
            border: 1.5px solid #e2e8f0;
            border-right: none;
            color: #94a3b8;
            font-size: 14px;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .input-icon { cursor: pointer; transition: color .2s; }
        .input-icon:hover { color: #6366f1; }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all .25s cubic-bezier(.4,0,.2,1);
            margin-top: 8px;
            letter-spacing: .2px;
        }

        .btn-login:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99,102,241,.4);
            color: white;
        }

        .btn-login:active { transform: translateY(0); }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 11px 14px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 500;
        }

        .alert-danger  { background: #ffe4e6; color: #9f1239; }
        .alert-success { background: #d1fae5; color: #065f46; }

        .login-footer {
            text-align: center;
            padding: 16px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }

        .login-footer p { color: #94a3b8; font-size: 12px; margin: 0; font-weight: 500; }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
        }

        .remember-me input[type="checkbox"] {
            margin-right: 8px;
            cursor: pointer;
            width: 16px; height: 16px;
            accent-color: #6366f1;
        }

        .remember-me label {
            margin: 0;
            cursor: pointer;
            font-size: 13px;
            color: #64748b;
            user-select: none;
        }

        .loading-spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2.5px solid rgba(255,255,255,.3);
            border-top: 2.5px solid white;
            border-radius: 50%;
            animation: spin .8s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn-login.loading { opacity: .7; cursor: not-allowed; }
        .btn-login.loading .loading-spinner { display: inline-block; }

        @media (max-width: 576px) {
            .login-container { margin: 16px; border-radius: 16px; }
            .login-header { padding: 28px 20px; }
            .login-header h1 { font-size: 20px; }
            .login-body { padding: 24px 20px; }
        }
    </style>
</head>
<body>
    <div class="login-container">

        <div class="login-header">
            <?php if ($companyLogo): ?>
                <img src="<?php echo BASE_URL . $companyLogo; ?>" alt="Company Logo">
            <?php else: ?>
                <i class="fas fa-shield-alt"></i>
            <?php endif; ?>
            <h1><?php echo $companyName; ?></h1>
            

        </div>

        <ul class="nav nav-tabs" id="loginTabs" role="tablist">
            <li class="nav-item" role="presentation" style="width: 50%;">
                <button class="nav-link active" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-panel" type="button" role="tab">
                    <i class="fas fa-user-shield me-2"></i>Admin
                </button>
            </li>
            <li class="nav-item" role="presentation" style="width: 50%;">
                <button class="nav-link" id="guard-tab" data-bs-toggle="tab" data-bs-target="#guard-panel" type="button" role="tab">
                    <i class="fas fa-shield-alt me-2"></i>Guard
                </button>
            </li>
        </ul>

        <div class="tab-content login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                </div>
            <?php endif; ?>

            <div class="tab-pane fade show active" id="admin-panel" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="login" value="1">
                    <div class="form-group">
                        <label class="form-label">Admin Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="username" placeholder="Username or Email" required autocomplete="username">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control admin-pass" name="password" placeholder="Password" required autocomplete="current-password">
                            <span class="input-group-text">
                                <i class="fas fa-eye input-icon" onclick="togglePassword('.admin-pass')"></i>
                            </span>
                        </div>
                    </div>

                    <div class="remember-me">
                        <input type="checkbox" id="remember-admin" name="remember">
                        <label for="remember-admin">Remember me</label>
                    </div>

                    <button type="submit" class="btn-login" id="loginBtn">
                        <span class="loading-spinner"></span>
                        <span>Login as Admin</span>
                    </button>
                </form>
            </div>

            <div class="tab-pane fade" id="guard-panel" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="guard_login" value="1">
                    <div class="form-group">
                        <label class="form-label">Guard Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                            <input type="text" class="form-control" name="username" placeholder="Employee Username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control guard-pass" name="password" placeholder="Password" required>
                            <span class="input-group-text">
                                <i class="fas fa-eye input-icon" onclick="togglePassword('.guard-pass')"></i>
                            </span>
                        </div>
                    </div>

                    <div class="remember-me">
                        <input type="checkbox" id="remember-guard" name="remember">
                        <label for="remember-guard">Remember me</label>
                    </div>

                    <button type="submit" class="btn-login" id="guardBtn">
                        <span class="loading-spinner"></span>
                        <span>Login as Guard</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="login-footer">
            <p>Developed By Chirag Das</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(selector) {
            const input = document.querySelector(selector);
            const icon = event.target;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }



        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const btn = form.querySelector('.btn-login');
                if (btn) {
                    btn.classList.add('loading');
                    btn.disabled = true;
                }
            });
        });

        // Retain active tab on reload if there's an error
        <?php if (isset($_POST['guard_login'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const guardTab = new bootstrap.Tab(document.getElementById('guard-tab'));
                guardTab.show();
            });
        <?php endif; ?>
    </script>
</body>
</html>

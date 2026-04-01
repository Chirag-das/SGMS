<?php



$checks = [];
$error_count = 0;
$warning_count = 0;


$php_version = phpversion();
$checks[] = [
    'name' => 'PHP Version',
    'status' => version_compare(PHP_VERSION, '8.0.0', '>=') ? 'pass' : 'fail',
    'value' => $php_version . (version_compare(PHP_VERSION, '8.0.0', '>=') ? ' (Recommended: 8.0+)' : ' (Needs upgrade)'),
    'description' => 'System requires PHP 8.0 or higher'
];
if (version_compare(PHP_VERSION, '8.0.0', '<'))
    $error_count++;


$checks[] = [
    'name' => 'Database Connection',
    'status' => 'checking'
];
$db_status = 'fail';
$db_message = '';
try {
    $conn = new mysqli('localhost', 'root', '', 'sgms_db');
    if ($conn->connect_error) {
        $db_message = 'Connection failed: ' . htmlspecialchars($conn->connect_error);
    }
    else {
        $db_status = 'pass';
        $db_message = 'MySQL version: ' . $conn->server_info;
        $conn->close();
    }
}
catch (Exception $e) {
    $db_message = 'Error: ' . htmlspecialchars($e->getMessage());
}
$checks[1] = [
    'name' => 'Database Connection',
    'status' => $db_status,
    'value' => $db_message,
    'description' => 'Verify MySQL is running and credentials are correct'
];
if ($db_status === 'fail')
    $error_count++;


$upload_path = __DIR__ . '/uploads/guards/';
$upload_exists = is_dir($upload_path);
$upload_writable = $upload_exists && is_writable($upload_path);
$checks[] = [
    'name' => 'Upload Directory Permission',
    'status' => $upload_writable ? 'pass' : ($upload_exists ? 'warning' : 'fail'),
    'value' => $upload_path . ($upload_writable ? ' (Writable)' : ' (Not writable)'),
    'description' => 'Ensure uploads/guards/ is writable (chmod 755)'
];
if ($upload_exists && !$upload_writable)
    $warning_count++;


$files_to_check = [
    './config/database.php',
    './config/constants.php',
    './core/init.php',
    './core/Database.php',
    './models/Auth.php'
];

foreach ($files_to_check as $file) {
    $file_exists = file_exists($file);
    $checks[] = [
        'name' => 'File: ' . basename($file),
        'status' => $file_exists ? 'pass' : 'fail',
        'value' => $file_exists ? 'Found' : 'Missing',
        'description' => 'Required system file'
    ];
    if (!$file_exists)
        $error_count++;
}


$models = ['Guard', 'Attendance', 'Salary', 'Client', 'Site'];
$models_ok = true;
foreach ($models as $model) {
    $model_file = "./models/{$model}.php";
    if (!file_exists($model_file)) {
        $models_ok = false;
    }
}
$checks[] = [
    'name' => 'Model Files',
    'status' => $models_ok ? 'pass' : 'fail',
    'value' => $models_ok ? 'All 5 models found' : 'One or more models missing',
    'description' => 'All required database models'
];
if (!$models_ok)
    $error_count++;


$session_started = session_status() === PHP_SESSION_ACTIVE || session_start() !== false;
$checks[] = [
    'name' => 'Session Support',
    'status' => $session_started ? 'pass' : 'fail',
    'value' => $session_started ? 'Enabled' : 'Disabled',
    'description' => 'Required for user authentication'
];


$mysqli_loaded = extension_loaded('mysqli');
$checks[] = [
    'name' => 'MySQL Extension (mysqli)',
    'status' => $mysqli_loaded ? 'pass' : 'fail',
    'value' => $mysqli_loaded ? 'Loaded' : 'Not loaded',
    'description' => 'Required for database operations'
];
if (!$mysqli_loaded)
    $error_count++;


$gd_loaded = extension_loaded('gd');
$checks[] = [
    'name' => 'GD Library',
    'status' => $gd_loaded ? 'pass' : 'warning',
    'value' => $gd_loaded ? 'Loaded' : 'Not loaded',
    'description' => 'Used for image processing (optional but recommended)'
];


$config_errors = [];
if (file_exists('./config/constants.php')) {
    ob_start();
    @include './config/constants.php';
    ob_end_clean();

    if (!defined('BASE_URL')) {
        $config_errors[] = 'BASE_URL not defined';
    }
    if (!defined('SESSION_TIMEOUT')) {
        $config_errors[] = 'SESSION_TIMEOUT not defined';
    }
}

$checks[] = [
    'name' => 'Configuration',
    'status' => empty($config_errors) ? 'pass' : 'fail',
    'value' => empty($config_errors) ? 'All constants defined' : implode(', ', $config_errors),
    'description' => 'Application constants must be properly defined'
];


if ($db_status === 'pass') {
    try {
        $conn = new mysqli('localhost', 'root', '', 'sgms_db');
        $tables = ['admins', 'guards', 'clients', 'sites', 'attendance', 'salaries', 'leaves', 'audit_logs'];
        $missing_tables = [];

        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '{$table}'");
            if ($result->num_rows === 0) {
                $missing_tables[] = $table;
            }
        }

        $checks[] = [
            'name' => 'Database Tables',
            'status' => empty($missing_tables) ? 'pass' : 'fail',
            'value' => empty($missing_tables) ? 'All 8 tables found' : 'Missing: ' . implode(', ', $missing_tables),
            'description' => 'Required database tables'
        ];

        $conn->close();
    }
    catch (Exception $e) {
        $checks[] = [
            'name' => 'Database Tables',
            'status' => 'warning',
            'value' => 'Could not verify (Database unavailable)',
            'description' => 'Required database tables'
        ];
    }
}





$components = ['sidebar.php', 'topnav.php'];
$components_ok = true;
foreach ($components as $comp) {
    if (!file_exists("./app/components/{$comp}")) {
        $components_ok = false;
    }
}
$checks[] = [
    'name' => 'Component Files',
    'status' => $components_ok ? 'pass' : 'fail',
    'value' => $components_ok ? 'All components found' : 'One or more missing',
    'description' => 'UI component templates'
];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health Check - SGMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin-top: 30px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            border-bottom: 3px solid #667eea;
        }
        .header h1 {
            margin: 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .header .badge {
            font-size: 12px;
        }
        .check-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .check-name {
            font-weight: 500;
            color: #333;
            flex: 1;
        }
        .check-description {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .check-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 20px;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            min-width: 70px;
            text-align: center;
        }
        .status-pass {
            background-color: #d4edda;
            color: #155724;
        }
        .status-fail {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .check-value {
            color: #666;
            font-size: 13px;
            text-align: right;
            min-width: 200px;
        }
        .summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .summary-item {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-pass {
            background: #d4edda;
            color: #155724;
        }
        .summary-fail {
            background: #f8d7da;
            color: #721c24;
        }
        .summary-warning {
            background: #fff3cd;
            color: #856404;
        }
        .summary-item h3 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .summary-item p {
            margin: 5px 0 0 0;
            font-size: 12px;
        }
        .action-buttons {
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 10px;
        }
        .footer-text {
            color: white;
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>
                    <i class="fas fa-stethoscope"></i>
                    System Health Check
                    <span class="badge bg-info">SGMS</span>
                </h1>
                <p style="margin: 10px 0 0 0; color: #666;">Last checked: <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>

            <div style="background: white; padding: 20px;">
                <div class="summary">
                    <div class="summary-item summary-pass">
                        <h3><?php echo count(array_filter($checks, fn($c) => $c['status'] === 'pass')); ?></h3>
                        <p>Passed</p>
                    </div>
                    <div class="summary-item <?php echo $error_count > 0 ? 'summary-fail' : 'summary-pass'; ?>">
                        <h3><?php echo $error_count; ?></h3>
                        <p>Errors</p>
                    </div>
                    <div class="summary-item <?php echo $warning_count > 0 ? 'summary-warning' : 'summary-pass'; ?>">
                        <h3><?php echo $warning_count; ?></h3>
                        <p>Warnings</p>
                    </div>
                    <div class="summary-item <?php echo $error_count === 0 ? 'summary-pass' : 'summary-fail'; ?>">
                        <h3><?php echo $error_count === 0 && $warning_count === 0 ? 'READY' : ($error_count === 0 ? 'CAUTION' : 'ISSUES'); ?></h3>
                        <p>Status</p>
                    </div>
                </div>

                <hr style="margin: 20px 0;">

                <h5 style="margin-bottom: 15px; color: #333;"><i class="fas fa-list"></i> Detailed Checks</h5>

                <?php foreach ($checks as $check): ?>
                    <div class="check-item">
                        <div style="flex: 1;">
                            <div class="check-name">
                                <?php echo htmlspecialchars($check['name']); ?>
                                <div class="check-description"><?php echo htmlspecialchars($check['description']); ?></div>
                            </div>
                        </div>
                        <div class="check-status">
                            <div class="check-value"><?php echo htmlspecialchars($check['value']); ?></div>
                            <span class="status-badge status-<?php echo $check['status']; ?>">
                                <?php
    $icons = ['pass' => 'fa-check-circle', 'fail' => 'fa-times-circle', 'warning' => 'fa-exclamation-circle', 'checking' => 'fa-circle-notch'];
    echo '<i class="fas ' . ($icons[$check['status']] ?? 'fa-question-circle') . '"></i> ';
    echo ucfirst($check['status']);
?>
                            </span>
                        </div>
                    </div>
                <?php
endforeach; ?>
            </div>

            <div class="action-buttons">
                <h5 style="margin-bottom: 15px; color: #333;"><i class="fas fa-tools"></i> Quick Actions</h5>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                    <a href="index.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-in-alt"></i> Go to Login
                    </a>
                    <button class="btn btn-info btn-sm" onclick="location.reload()">
                        <i class="fas fa-redo"></i> Refresh Check
                    </button>
                    <a href="SETUP.md" class="btn btn-secondary btn-sm" target="_blank">
                        <i class="fas fa-book"></i> View Setup Guide
                    </a>
                    <a href="README.md" class="btn btn-secondary btn-sm" target="_blank">
                        <i class="fas fa-book"></i> View Documentation
                    </a>
                </div>

                <?php if ($error_count > 0): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;">
                        <h6 style="margin-top: 0;"><i class="fas fa-exclamation-triangle"></i> Action Required</h6>
                        <p>Your system has <?php echo $error_count; ?> critical issue(s) that need to be resolved before deployment. Please refer to SETUP.md for troubleshooting.</p>
                    </div>
                <?php
endif; ?>

                <?php if ($error_count === 0 && $warning_count === 0): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;">
                        <h6 style="margin-top: 0;"><i class="fas fa-check-circle"></i> System Ready</h6>
                        <p>All system checks have passed! Your SGMS installation is ready to use. Click "Go to Login" above to start.</p>
                    </div>
                <?php
endif; ?>
            </div>
        </div>

        <div class="footer-text">
            <p><strong>Security Guard Management System (SGMS)</strong> v1.0</p>
            <p>This health check page should be removed or password-protected in production environments.</p>
            <p><strong style="color: #ffeb3b;">Note:</strong> This is a diagnostic tool for development/testing only.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        .error-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .error-content {
            text-align: center;
            color: white;
        }
        .error-code {
            font-size: 120px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
        }
        .error-description {
            font-size: 16px;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        .btn {
            background: white;
            color: #1e3c72;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            opacity: 0.9;
            color: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-code">403</div>
            <div class="error-message">Access Denied</div>
            <div class="error-description">
                You do not have permission to access this resource.
                <br>
                Please contact your system administrator if you believe this is an error.
            </div>
            <a href="dashboard.php" class="btn">
                <i class="fas fa-home"></i> Go to Dashboard
            </a>
        </div>
    </div>
</body>
</html>

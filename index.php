<?php
require_once __DIR__ . '/core/init.php';

if ($auth->isLoggedIn()) {
    redirect(BASE_URL . 'dashboard.php');
} else {
    redirect(BASE_URL . 'login.php');
}
?>

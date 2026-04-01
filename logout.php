<?php
require_once 'core/init.php';


$auth->requireLogin();


$auth->logout();
redirect(BASE_URL . 'login.php?logout=1');
?>

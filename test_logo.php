<?php
require 'core/init.php';
$stmt = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'company_logo'");
print_r($stmt->fetch_assoc());
?>

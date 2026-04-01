<?php
require 'core/init.php';
$stmt = $conn->query("UPDATE settings SET setting_value = 'uploads/system/69b292357b74e.png' WHERE setting_key = 'company_logo'");
echo "Updated!";
?>

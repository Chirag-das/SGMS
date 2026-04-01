<?php
include 'config/database.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $conn->query("SELECT id, full_name, employee_id FROM guards");
while($row = $res->fetch_assoc()) {
    echo $row['id'] . ' | ' . $row['employee_id'] . ' | ' . $row['full_name'] . PHP_EOL;
}
?>

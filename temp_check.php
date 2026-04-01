<?php
include 'config/database.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$res = $conn->query("SELECT COUNT(*) as cnt FROM guards");
echo "Guards: " . $res->fetch_assoc()['cnt'] . "\n";
$res = $conn->query("SELECT COUNT(*) as cnt FROM attendance");
echo "Attendance records: " . $res->fetch_assoc()['cnt'] . "\n";
?>

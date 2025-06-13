<?php
require 'db.php';

$sessionId = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text = $_POST["text"];

// Check if user is admin
$stmt = $pdo->prepare("SELECT * FROM admins WHERE phone_number = ?");
$stmt->execute([$phoneNumber]);
$isAdmin = $stmt->fetch();

if ($isAdmin) {
    // Load Admin Logic
    require_once("admin.php");
} else {
    // Load Student Logic
    require_once("student.php");
}
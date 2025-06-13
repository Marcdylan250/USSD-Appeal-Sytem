<?php
$host = 'localhost';
$db = 'appeal_system';
$user = 'dylan'; // change if needed
$pass = 'oop@2125';     // change if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>

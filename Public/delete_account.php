<?php
require_once __DIR__ . '/../Config/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    session_destroy();
}
header("Location: index.php");
exit();
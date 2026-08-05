<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/CTF/ctf_session.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/auth.php');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: cart.php");
  exit;
}
if (!isset($_SESSION['user_id'])) {
  die("Login required");
}

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
  header("Location: cart.php");
  exit;
}

$counts = array_count_values($_SESSION['cart']);

try {

  foreach ($counts as $product_id => $qty) {

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) continue;

    $price = $product['price'];
    $total = $price * $qty;

    // 🔥 FIXED QUERY (matching your DB)
    $stmt = $pdo->prepare("
      INSERT INTO orders (user_id, product_id, quantity, total, status, created_at)
      VALUES (?, ?, ?, ?, 'completed', NOW())
    ");

    $stmt->execute([
      $user_id,
      $product_id,
      $qty,
      $total
    ]);
  }

  // clear cart
  // clear cart completely
  unset($_SESSION['cart']);

  session_write_close();

  header("Location: account.php?tab=orders&success=1");
  exit;

} catch (Exception $e) {
  die("Checkout error: " . $e->getMessage());
}
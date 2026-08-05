<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/functions.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/CTF/ctf_session.php';

// VULNERABILITY: SQL Injection in search parameter
// FLAG: DH{sql_injection_search}
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Intentionally vulnerable raw SQL query - NO sanitization
$sql = "SELECT * FROM products WHERE hidden = 0";
if (!empty($search_query)) {
  $sql = "SELECT * FROM products WHERE (name LIKE '%" . $search_query . "%' OR description LIKE '%" . $search_query . "%') AND hidden = 0";
}
if (!empty($category_filter)) {
  $sql .= " AND category = '" . $category_filter . "'";
}

// Sort handling (intentionally injectable)
$sort_sql = match ($sort) {
  'price_low' => "ORDER BY price ASC",
  'price_high' => "ORDER BY price DESC",
  'popular' => "ORDER BY views DESC",
  default => "ORDER BY created_at DESC"
};
// VULNERABILITY: Potential SQL injection via sort parameter
$sql .= " " . $sort_sql;

$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();

// VULNERABILITY: IDOR - hidden products accessible via direct ID
// FLAG: DH{hidden_product_access}
if (isset($_GET['product_id'])) {
  $product_id = $_GET['product_id'];
  $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
  $stmt->execute([$product_id]);
  $selected_product = $stmt->fetch();

  if ($selected_product) {
    // Increment views
    $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$product_id]);

    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll();

    // Get related products
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? AND hidden = 0 LIMIT 4");
    $stmt->execute([$selected_product['category'], $product_id]);
    $related_products = $stmt->fetchAll();
  }
}

// VULNERABILITY: No CSRF protection on review submission
// FLAG: DH{xss_review_pwned}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
  $product_id = $_POST['product_id'];
  $rating = $_POST['rating'];
  $comment = $_POST['comment']; // No sanitization - XSS possible
  $username = is_logged_in() ? $_SESSION['username'] : 'Anonymous';
  $user_id = is_logged_in() ? $_SESSION['user_id'] : 0;

  add_review($product_id, $user_id, $username, $rating, $comment);
  header("Location: shop.php?product_id=" . $product_id . "&reviewed=1");
  exit;
}

// Get categories
$stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE hidden = 0");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// VULNERABILITY: Coupon code - weak validation
$coupon_message = '';
if (isset($_POST['apply_coupon'])) {
  $coupon = $_POST['coupon_code'];
  // VULNERABILITY: Weak coupon validation - string comparison only
  if ($coupon === 'HACKER10' || $coupon === 'CTF2024' || $coupon === 'ADMIN999') {
    $coupon_message = '<div class="toast-notification toast-success"><i class="fas fa-check-circle"></i> Coupon applied! 10% discount.</div>';
    $_SESSION['coupon'] = $coupon;
  } else {
    $coupon_message = '<div class="toast-notification toast-error"><i class="fas fa-times-circle"></i> Invalid coupon code.</div>';
  }
}

// Get cart count
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// VULNERABILITY: Open redirect in add to cart
if (isset($_GET['add_to_cart'])) {
  $product_id = $_GET['add_to_cart'];
  if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
  $_SESSION['cart'][] = $product_id;
  $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'shop.php';
  header("Location: " . $redirect);
  exit;
}

// Product badges helper
function getProductBadge($product)
{
  if ($product['stock'] <= 3) return '<span class="badge badge-limited">Limited</span>';
  if (strtotime($product['created_at']) > strtotime('-7 days')) return '<span class="badge badge-new">New</span>';
  if ($product['views'] > 100) return '<span class="badge badge-hot">Hot</span>';
  if (isset($product['sale_price']) && $product['sale_price'] < $product['price']) return '<span class="badge badge-sale">Sale</span>';
  return '';
}

// Rating helper
function getRatingStars($rating)
{
  $stars = '';
  for ($i = 1; $i <= 5; $i++) {
    $stars .= '<i class="fas fa-star ' . ($i <= $rating ? 'star-filled' : 'star-empty') . '"></i>';
  }
  return $stars;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DarkShop | Cybersecurity Marketplace</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <style>
    :root {
      --bg-primary: #0a0a0f;
      --bg-secondary: #111118;
      --bg-card: #16161f;
      --bg-hover: #1e1e2a;
      --bg-elevated: #22222e;
      --accent-cyan: #00d4ff;
      --accent-green: #00e676;
      --accent-red: #ff1744;
      --accent-purple: #b967ff;
      --accent-orange: #ff9100;
      --accent-pink: #ff4081;
      --text-primary: #f0f0f5;
      --text-secondary: #a0a0b0;
      --text-muted: #6c6c7e;
      --border-color: #2a2a3a;
      --border-hover: #3a3a4e;
      --glow-cyan: 0 0 20px rgba(0, 212, 255, 0.15);
      --glow-purple: 0 0 20px rgba(185, 103, 255, 0.15);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: var(--bg-primary);
      color: var(--text-primary);
      font-family: 'Inter', -apple-system, sans-serif;
      line-height: 1.6;
      overflow-x: hidden;
    }

    .bg-grid {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: linear-gradient(rgba(0, 212, 255, 0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 212, 255, 0.02) 1px, transparent 1px);
      background-size: 60px 60px;
      pointer-events: none;
      z-index: 0;
    }

    .bg-glow {
      position: fixed;
      width: 500px;
      height: 500px;
      border-radius: 50%;
      filter: blur(120px);
      opacity: 0.08;
      pointer-events: none;
      z-index: 0;
    }

    .glow-1 {
      top: -100px;
      left: -100px;
      background: var(--accent-cyan);
    }

    .glow-2 {
      bottom: -100px;
      right: -100px;
      background: var(--accent-purple);
    }

    .navbar {
      background: rgba(10, 10, 15, 0.9) !important;
      backdrop-filter: blur(20px) saturate(180%);
      border-bottom: 1px solid var(--border-color);
      padding: 0.75rem 0;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .navbar-brand {
      font-family: 'JetBrains Mono', monospace;
      font-weight: 700;
      font-size: 1.4rem;
      color: var(--accent-cyan) !important;
      text-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .nav-link {
      color: var(--text-secondary) !important;
      font-weight: 500;
      padding: 0.5rem 1rem !important;
      position: relative;
      transition: all 0.3s;
    }

    .nav-link:hover,
    .nav-link.active {
      color: var(--accent-cyan) !important;
    }

    .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 1rem;
      right: 1rem;
      height: 2px;
      background: var(--accent-cyan);
      box-shadow: var(--glow-cyan);
    }

    .cart-badge {
      background: linear-gradient(135deg, var(--accent-red), var(--accent-pink));
      color: white;
      font-size: 0.65rem;
      font-weight: 700;
      padding: 0.2rem 0.5rem;
      border-radius: 50px;
      margin-left: 0.3rem;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        box-shadow: 0 0 0 0 rgba(255, 23, 68, 0.4);
      }

      50% {
        box-shadow: 0 0 0 8px rgba(255, 23, 68, 0);
      }
    }

    .shop-hero {
      position: relative;
      z-index: 1;
      background: linear-gradient(135deg, rgba(0, 212, 255, 0.03), rgba(185, 103, 255, 0.03));
      border-bottom: 1px solid var(--border-color);
      padding: 80px 0 50px;
      overflow: hidden;
    }

    .shop-hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(ellipse at top, rgba(0, 212, 255, 0.05), transparent 70%);
    }

    .hero-content {
      position: relative;
      z-index: 2;
    }

    .shop-hero h1 {
      font-family: 'JetBrains Mono', monospace;
      font-size: 3rem;
      font-weight: 700;
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0.75rem;
    }

    .shop-hero p {
      color: var(--text-secondary);
      font-size: 1.1rem;
      max-width: 600px;
    }

    .hero-stats {
      display: flex;
      gap: 2rem;
      margin-top: 1.5rem;
    }

    .hero-stat {
      text-align: center;
    }

    .hero-stat .number {
      font-family: 'JetBrains Mono', monospace;
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--accent-green);
    }

    .hero-stat .label {
      color: var(--text-muted);
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .search-bar {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      position: relative;
      z-index: 10;
    }

    .search-input-group {
      display: flex;
      gap: 0.75rem;
      margin-bottom: 1rem;
    }

    .search-input {
      flex: 1;
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      color: var(--text-primary);
      padding: 0.875rem 1.25rem;
      border-radius: 12px;
      font-size: 0.95rem;
      transition: all 0.3s;
    }

    .search-input:focus {
      outline: none;
      border-color: var(--accent-cyan);
      box-shadow: var(--glow-cyan);
    }

    .search-input::placeholder {
      color: var(--text-muted);
    }

    .btn-search {
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      border: none;
      color: var(--bg-primary);
      font-weight: 700;
      padding: 0.875rem 1.75rem;
      border-radius: 12px;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-search:hover {
      transform: translateY(-2px);
      box-shadow: var(--glow-cyan);
    }

    .filter-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .category-pills {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
    }

    .category-pill {
      padding: 0.4rem 1rem;
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      border-radius: 50px;
      color: var(--text-secondary);
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 500;
      transition: all 0.3s;
    }

    .category-pill:hover,
    .category-pill.active {
      background: rgba(0, 212, 255, 0.1);
      border-color: var(--accent-cyan);
      color: var(--accent-cyan);
      box-shadow: var(--glow-cyan);
    }

    .sort-select {
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      color: var(--text-primary);
      padding: 0.4rem 1rem;
      border-radius: 8px;
      font-size: 0.85rem;
      cursor: pointer;
    }

    .sort-select:focus {
      outline: none;
      border-color: var(--accent-cyan);
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
    }

    .product-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      display: flex;
      flex-direction: column;
    }

    .product-card:hover {
      border-color: var(--border-hover);
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), var(--glow-cyan);
    }

    .product-card .badge-container {
      position: absolute;
      top: 1rem;
      left: 1rem;
      z-index: 5;
      display: flex;
      gap: 0.5rem;
    }

    .badge {
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .badge-new {
      background: rgba(0, 230, 118, 0.15);
      color: var(--accent-green);
      border: 1px solid rgba(0, 230, 118, 0.3);
    }

    .badge-hot {
      background: rgba(255, 145, 0, 0.15);
      color: var(--accent-orange);
      border: 1px solid rgba(255, 145, 0, 0.3);
    }

    .badge-limited {
      background: rgba(255, 23, 68, 0.15);
      color: var(--accent-red);
      border: 1px solid rgba(255, 23, 68, 0.3);
    }

    .badge-sale {
      background: rgba(185, 103, 255, 0.15);
      color: var(--accent-purple);
      border: 1px solid rgba(185, 103, 255, 0.3);
    }

    .product-image-wrapper {
      position: relative;
      height: 200px;
      overflow: hidden;
      background: linear-gradient(135deg, var(--bg-secondary), var(--bg-hover));
    }

    .product-image-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .product-card:hover .product-image-wrapper img {
      transform: scale(1.1);
    }

    .product-image-overlay {
      position: absolute;
      inset: 0;
      background: rgba(10, 10, 15, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s;
    }

    .product-card:hover .product-image-overlay {
      opacity: 1;
    }

    .quick-actions {
      display: flex;
      gap: 0.75rem;
    }

    .quick-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      color: var(--text-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s;
    }

    .quick-btn:hover {
      background: var(--accent-cyan);
      color: var(--bg-primary);
      border-color: var(--accent-cyan);
    }

    .product-body {
      padding: 1.25rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .product-category {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--accent-cyan);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 0.5rem;
    }

    .product-title {
      font-family: 'JetBrains Mono', monospace;
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .product-description {
      color: var(--text-secondary);
      font-size: 0.85rem;
      line-height: 1.5;
      margin-bottom: 1rem;
      flex: 1;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .product-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .product-price {
      font-family: 'JetBrains Mono', monospace;
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--accent-green);
    }

    .product-price .original {
      font-size: 0.85rem;
      color: var(--text-muted);
      text-decoration: line-through;
      margin-left: 0.5rem;
    }

    .product-stock {
      font-size: 0.8rem;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }

    .stock-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      display: inline-block;
    }

    .stock-high {
      background: var(--accent-green);
      box-shadow: 0 0 6px var(--accent-green);
    }

    .stock-low {
      background: var(--accent-red);
      box-shadow: 0 0 6px var(--accent-red);
    }

    .stock-medium {
      background: var(--accent-orange);
    }

    .product-rating {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    .star-filled {
      color: var(--accent-orange);
      font-size: 0.8rem;
    }

    .star-empty {
      color: var(--text-muted);
      font-size: 0.8rem;
    }

    .rating-text {
      font-size: 0.8rem;
      color: var(--text-muted);
    }

    .product-actions {
      display: flex;
      gap: 0.5rem;
    }

    .btn-add-cart {
      flex: 1;
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      border: none;
      color: var(--bg-primary);
      font-weight: 600;
      font-size: 0.9rem;
      padding: 0.75rem;
      border-radius: 10px;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      text-decoration: none;
    }

    .btn-add-cart:hover {
      transform: translateY(-2px);
      box-shadow: var(--glow-cyan);
      color: var(--bg-primary);
    }

    .btn-wishlist {
      width: 42px;
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      color: var(--text-secondary);
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-wishlist:hover {
      border-color: var(--accent-red);
      color: var(--accent-red);
    }

    .btn-wishlist.active {
      background: rgba(255, 23, 68, 0.1);
      color: var(--accent-red);
      border-color: var(--accent-red);
    }

    .product-gallery {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 20px;
      overflow: hidden;
    }

    .gallery-main {
      height: 400px;
      background: linear-gradient(135deg, var(--bg-secondary), var(--bg-hover));
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .gallery-main img {
      max-width: 80%;
      max-height: 80%;
      object-fit: contain;
    }

    .gallery-thumbs {
      display: flex;
      gap: 0.75rem;
      padding: 1rem;
    }

    .gallery-thumb {
      width: 80px;
      height: 80px;
      border-radius: 12px;
      background: var(--bg-secondary);
      border: 2px solid var(--border-color);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s;
    }

    .gallery-thumb:hover,
    .gallery-thumb.active {
      border-color: var(--accent-cyan);
    }

    .product-info-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 20px;
      padding: 2rem;
    }

    .product-info-card .category-tag {
      display: inline-block;
      padding: 0.35rem 1rem;
      background: rgba(0, 212, 255, 0.1);
      color: var(--accent-cyan);
      border-radius: 50px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .product-info-card h1 {
      font-family: 'JetBrains Mono', monospace;
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.75rem;
    }

    .product-info-card .price-block {
      display: flex;
      align-items: baseline;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .product-info-card .current-price {
      font-family: 'JetBrains Mono', monospace;
      font-size: 2rem;
      font-weight: 700;
      color: var(--accent-green);
    }

    .product-info-card .original-price {
      font-size: 1.25rem;
      color: var(--text-muted);
      text-decoration: line-through;
    }

    .product-info-card .discount-badge {
      background: rgba(255, 23, 68, 0.15);
      color: var(--accent-red);
      padding: 0.25rem 0.75rem;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    .specs-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
      margin: 1.5rem 0;
    }

    .spec-item {
      background: var(--bg-secondary);
      border-radius: 10px;
      padding: 0.875rem;
      border: 1px solid var(--border-color);
    }

    .spec-item .spec-label {
      font-size: 0.75rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.25rem;
    }

    .spec-item .spec-value {
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.9rem;
      color: var(--text-primary);
    }

    .quantity-selector {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin: 1.5rem 0;
    }

    .qty-btn {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      color: var(--text-primary);
      font-size: 1.25rem;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .qty-btn:hover {
      border-color: var(--accent-cyan);
      color: var(--accent-cyan);
    }

    .qty-input {
      width: 60px;
      text-align: center;
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      color: var(--text-primary);
      padding: 0.5rem;
      border-radius: 10px;
      font-family: 'JetBrains Mono', monospace;
    }

    .action-buttons {
      display: flex;
      gap: 0.75rem;
    }

    .btn-primary-action {
      flex: 1;
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      border: none;
      color: var(--bg-primary);
      font-weight: 700;
      font-size: 1rem;
      padding: 1rem;
      border-radius: 12px;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .btn-primary-action:hover {
      transform: translateY(-2px);
      box-shadow: var(--glow-cyan);
    }

    .btn-secondary-action {
      padding: 1rem 1.5rem;
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      color: var(--text-primary);
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-secondary-action:hover {
      border-color: var(--accent-cyan);
      color: var(--accent-cyan);
    }

    .tab-nav {
      display: flex;
      gap: 0.5rem;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 1.5rem;
    }

    .tab-btn {
      padding: 0.875rem 1.5rem;
      background: none;
      border: none;
      color: var(--text-muted);
      font-weight: 500;
      cursor: pointer;
      position: relative;
      transition: all 0.3s;
      font-size: 0.9rem;
    }

    .tab-btn:hover {
      color: var(--text-secondary);
    }

    .tab-btn.active {
      color: var(--accent-cyan);
    }

    .tab-btn.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      right: 0;
      height: 2px;
      background: var(--accent-cyan);
      box-shadow: var(--glow-cyan);
    }

    .tab-panel {
      display: none;
    }

    .tab-panel.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .reviews-section {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 20px;
      padding: 2rem;
    }

    .review-form {
      background: var(--bg-secondary);
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }

    .review-card-modern {
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      border-radius: 14px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      transition: all 0.3s;
    }

    .review-card-modern:hover {
      border-color: var(--border-hover);
    }

    .review-header-modern {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 0.75rem;
    }

    .reviewer-info {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .reviewer-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: var(--bg-primary);
      font-size: 0.9rem;
    }

    .reviewer-name {
      font-weight: 600;
      color: var(--text-primary);
    }

    .review-date {
      font-size: 0.8rem;
      color: var(--text-muted);
    }

    .review-actions {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }

    .review-action-btn {
      background: none;
      border: none;
      color: var(--text-muted);
      font-size: 0.85rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.35rem;
      transition: color 0.3s;
    }

    .review-action-btn:hover {
      color: var(--accent-cyan);
    }

    .toast-container {
      position: fixed;
      top: 100px;
      right: 20px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .toast-notification {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 1rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      animation: slideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      min-width: 300px;
    }

    .toast-notification.toast-success {
      border-left: 3px solid var(--accent-green);
    }

    .toast-notification.toast-error {
      border-left: 3px solid var(--accent-red);
    }

    .toast-notification.toast-info {
      border-left: 3px solid var(--accent-cyan);
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }

      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    @keyframes slideOut {
      from {
        transform: translateX(0);
        opacity: 1;
      }

      to {
        transform: translateX(100%);
        opacity: 0;
      }
    }

    .toast-notification.hiding {
      animation: slideOut 0.3s ease forwards;
    }

    .sidebar-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .sidebar-card h5 {
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.95rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
    }

    .empty-state-icon {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: var(--bg-secondary);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      font-size: 2.5rem;
      color: var(--text-muted);
    }

    .empty-state h3 {
      color: var(--text-secondary);
      margin-bottom: 0.5rem;
    }

    .empty-state p {
      color: var(--text-muted);
    }

    .skeleton {
      background: linear-gradient(90deg, var(--bg-secondary) 25%, var(--bg-hover) 50%, var(--bg-secondary) 75%);
      background-size: 200% 100%;
      animation: shimmer 1.5s infinite;
      border-radius: 8px;
    }

    @keyframes shimmer {
      0% {
        background-position: 200% 0;
      }

      100% {
        background-position: -200% 0;
      }
    }

    footer {
      background: var(--bg-secondary);
      border-top: 1px solid var(--border-color);
      padding: 3rem 0 2rem;
      margin-top: 4rem;
      position: relative;
      z-index: 1;
    }

    .footer-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
    }

    .footer-col h6 {
      color: var(--text-primary);
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .footer-col a {
      display: block;
      color: var(--text-muted);
      text-decoration: none;
      margin-bottom: 0.5rem;
      transition: color 0.3s;
    }

    .footer-col a:hover {
      color: var(--accent-cyan);
    }

    .footer-bottom {
      border-top: 1px solid var(--border-color);
      padding-top: 2rem;
      text-align: center;
      color: var(--text-muted);
      font-size: 0.85rem;
    }

    @media (max-width: 768px) {
      .shop-hero h1 {
        font-size: 2rem;
      }

      .product-grid {
        grid-template-columns: 1fr;
      }

      .hero-stats {
        gap: 1rem;
      }

      .filter-row {
        flex-direction: column;
        align-items: flex-start;
      }

      .gallery-main {
        height: 250px;
      }
    }

    ::-webkit-scrollbar {
      width: 6px;
    }

    ::-webkit-scrollbar-track {
      background: var(--bg-primary);
    }

    ::-webkit-scrollbar-thumb {
      background: var(--border-color);
      border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: var(--accent-cyan);
    }
  </style>
</head>

<body>
  <div class="bg-grid"></div>
  <div class="bg-glow glow-1"></div>
  <div class="bg-glow glow-2"></div>

  <!-- Toast Container -->
  <div class="toast-container" id="toastContainer"></div>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.php"><i class="fas fa-bug"></i> DarkHunter</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="shop.php">Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="account.php">Account</a></li>
          <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
          <li class="nav-item"><a class="nav-link" href="submit.php">Submit Flag</a></li>
          <li class="nav-item">
            <a class="nav-link" href="cart.php">
              <i class="fas fa-shopping-cart"></i> Cart
              <?php if ($cart_count > 0): ?>
                <span class="cart-badge" id="cartBadge"><?php echo $cart_count; ?></span>
              <?php endif; ?>
            </a>
          </li>
          <?php if (is_logged_in()): ?>
            <li class="nav-item"><a class="nav-link" href="account.php"><i class="fas fa-user"></i>
                <?php echo $_SESSION['username']; ?></a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="account.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="shop-hero">
    <div class="container hero-content">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <h1><i class="fas fa-store"></i> DarkShop</h1>
          <p>Premium cybersecurity tools, hardware, and services for ethical hackers and security professionals.</p>
          <div class="hero-stats">
            <div class="hero-stat">
              <div class="number"><?php echo count($products); ?></div>
              <div class="label">Products</div>
            </div>
            <div class="hero-stat">
              <div class="number"><?php echo count($categories); ?></div>
              <div class="label">Categories</div>
            </div>
            <div class="hero-stat">
              <div class="number">24/7</div>
              <div class="label">Support</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="container" style="padding: 2rem 0; position: relative; z-index: 1;">
    <?php if (!isset($selected_product)): ?>
      <!-- Product Listing -->
      <div class="row">
        <div class="col-lg-9">
          <!-- Search Bar -->
          <div class="search-bar">
            <form method="GET" action="shop.php" class="search-input-group">
              <input type="text" name="search" class="search-input"
                placeholder="Search tools, hardware, services... Try SQL injection payloads here"
                value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
              <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
            </form>

            <!-- VULNERABILITY: Reflected XSS in search parameter -->
            <?php if (!empty($search_query)): ?>
              <div style="margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
                <i class="fas fa-search"></i> Results for: <span
                  style="color: var(--accent-cyan);"><?php echo $search_query; ?></span>
                <!-- VULNERABILITY: No htmlspecialchars() - XSS possible -->
                <!-- FLAG: Reflected XSS here -->
              </div>
            <?php endif; ?>

            <div class="filter-row">
              <div class="category-pills">
                <a href="shop.php" class="category-pill <?php echo empty($category_filter) ? 'active' : ''; ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                  <a href="shop.php?category=<?php echo urlencode($cat); ?>"
                    class="category-pill <?php echo $category_filter === $cat ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat); ?>
                  </a>
                <?php endforeach; ?>
              </div>
              <select class="sort-select" onchange="window.location.href=this.value">
                <option
                  value="shop.php?sort=newest<?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                  Newest</option>
                <option
                  value="shop.php?sort=price_low<?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                  <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option
                  value="shop.php?sort=price_high<?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                  <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option
                  value="shop.php?sort=popular<?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                  <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
              </select>
            </div>
          </div>

          <!-- Products Grid -->
          <?php if (!empty($products)): ?>
            <div class="product-grid" id="productGrid">
              <?php foreach ($products as $product): ?>
                <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                  <div class="badge-container">
                    <?php echo getProductBadge($product); ?>
                  </div>
                  <div class="product-image-wrapper">
                    <img src="https://picsum.photos/seed/<?php echo $product['id']; ?>/400/300"
                      alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                    <div class="product-image-overlay">
                      <div class="quick-actions">
                        <button class="quick-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)"
                          title="Add to Wishlist"><i class="fas fa-heart"></i></button>
                        <a href="shop.php?product_id=<?php echo $product['id']; ?>" class="quick-btn" title="View Details"><i
                            class="fas fa-eye"></i></a>
                      </div>
                    </div>
                  </div>
                  <div class="product-body">
                    <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                    <div class="product-rating">
                      <?php echo getRatingStars(rand(3, 5)); ?>
                      <span class="rating-text">(<?php echo rand(10, 200); ?> reviews)</span>
                    </div>
                    <div class="product-meta">
                      <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                      <span class="product-stock">
                        <span
                          class="stock-dot <?php echo $product['stock'] > 10 ? 'stock-high' : ($product['stock'] > 3 ? 'stock-medium' : 'stock-low'); ?>"></span>
                        <?php echo $product['stock']; ?> in stock
                      </span>
                    </div>
                    <div class="product-actions">
                      <!-- VULNERABILITY: Open redirect in add_to_cart -->
                      <a href="shop.php?add_to_cart=<?php echo $product['id']; ?>&redirect=shop.php" class="btn-add-cart"
                        onclick="showToast('Added to cart!', 'success')">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                      </a>
                      <button class="btn-wishlist" onclick="addToWishlist(<?php echo $product['id']; ?>)"
                        id="wishlist-<?php echo $product['id']; ?>">
                        <i class="fas fa-heart"></i>
                      </button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <div class="empty-state-icon"><i class="fas fa-search"></i></div>
              <h3>No products found</h3>
              <p>Try adjusting your search or browse all categories.</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
          <!-- Cart Summary -->
          <div class="sidebar-card">
            <h5><i class="fas fa-shopping-cart"></i> Your Cart</h5>
            <?php if ($cart_count > 0): ?>
              <p style="color: var(--text-secondary); margin-bottom: 1rem;"><?php echo $cart_count; ?> item(s)</p>
              <a href="cart.php" class="btn-add-cart w-100 text-center text-decoration-none" style="margin-bottom: 0.5rem;">
                <i class="fas fa-credit-card"></i> Checkout
              </a>
              <a href="cart.php" class="btn-secondary-action w-100 text-center text-decoration-none d-block"
                style="padding: 0.5rem;">
                View Cart
              </a>
            <?php else: ?>
              <div class="empty-state" style="padding: 2rem 0;">
                <i class="fas fa-shopping-basket"
                  style="font-size: 2rem; color: var(--text-muted); margin-bottom: 0.5rem;"></i>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Your cart is empty</p>
              </div>
            <?php endif; ?>
          </div>

          <!-- Coupon -->
          <div class="sidebar-card">
            <h5><i class="fas fa-ticket-alt"></i> Coupon</h5>
            <?php echo $coupon_message; ?>
            <form method="POST" action="shop.php">
              <div class="input-group" style="gap: 0.5rem;">
                <input type="text" name="coupon_code" class="search-input"
                  style="padding: 0.5rem 0.75rem; font-size: 0.85rem;" placeholder="Enter code">
                <button type="submit" name="apply_coupon" class="btn-search"
                  style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                  <i class="fas fa-check"></i>
                </button>
              </div>
            </form>
            <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.5rem; display: block;">
              Try: HACKER10, CTF2024, ADMIN999
            </small>
          </div>

          <!-- Notifications -->
          <div class="sidebar-card">
            <h5><i class="fas fa-bell"></i> Notifications</h5>
            <?php if (is_logged_in()): ?>
              <?php
              $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
              $stmt->execute([$_SESSION['user_id']]);
              $notifications = $stmt->fetchAll();
              ?>
              <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notif): ?>
                  <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color); font-size: 0.85rem;">
                    <p style="margin: 0; color: var(--text-secondary);"><?php echo $notif['message']; ?></p>
                    <small
                      style="color: var(--text-muted);"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></small>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p style="color: var(--text-muted); font-size: 0.85rem;">No new notifications</p>
              <?php endif; ?>
            <?php else: ?>
              <p style="color: var(--text-muted); font-size: 0.85rem;">Login to see notifications</p>
            <?php endif; ?>
          </div>

          <!-- Quick Links -->
          <div class="sidebar-card">
            <h5><i class="fas fa-link"></i> Quick Links</h5>
            <a href="shop.php?product_id=11" class="d-block mb-2"
              style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem;">
              <i class="fas fa-eye-slash"></i> Hidden Products
            </a>
            <a href="admin.php" class="d-block mb-2"
              style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem;">
              <i class="fas fa-user-shield"></i> Admin Panel
            </a>
            <a href="submit.php" class="d-block"
              style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem;">
              <i class="fas fa-flag"></i> Submit Flag
            </a>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- Product Detail Page -->
      <div class="row g-4">
        <div class="col-lg-7">
          <div class="product-gallery">
            <div class="gallery-main">
              <img src="https://picsum.photos/seed/<?php echo $selected_product['id']; ?>/600/400"
                alt="<?php echo htmlspecialchars($selected_product['name']); ?>">
            </div>
            <div class="gallery-thumbs">
              <div class="gallery-thumb active"><i class="fas fa-image" style="color: var(--accent-cyan);"></i></div>
              <div class="gallery-thumb"><i class="fas fa-cube" style="color: var(--accent-purple);"></i></div>
              <div class="gallery-thumb"><i class="fas fa-layer-group" style="color: var(--accent-green);"></i></div>
              <div class="gallery-thumb"><i class="fas fa-microchip" style="color: var(--accent-orange);"></i></div>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="product-info-card">
            <span class="category-tag"><?php echo htmlspecialchars($selected_product['category']); ?></span>
            <h1><?php echo htmlspecialchars($selected_product['name']); ?></h1>
            <div class="product-rating" style="margin-bottom: 1rem;">
              <?php echo getRatingStars(4); ?>
              <span class="rating-text">(<?php echo count($reviews); ?> reviews)</span>
            </div>
            <div class="price-block">
              <span class="current-price">$<?php echo number_format($selected_product['price'], 2); ?></span>
              <?php if (isset($selected_product['original_price'])): ?>
                <span class="original-price">$<?php echo number_format($selected_product['original_price'], 2); ?></span>
                <span
                  class="discount-badge">-<?php echo round((1 - $selected_product['price'] / $selected_product['original_price']) * 100); ?>%</span>
              <?php endif; ?>
            </div>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
              <?php echo htmlspecialchars($selected_product['description']); ?>
            </p>

            <div class="specs-grid">
              <div class="spec-item">
                <div class="spec-label">Stock</div>
                <div class="spec-value"><?php echo $selected_product['stock']; ?> units</div>
              </div>
              <div class="spec-item">
                <div class="spec-label">SKU</div>
                <div class="spec-value">DH-<?php echo str_pad($selected_product['id'], 4, '0', STR_PAD_LEFT); ?></div>
              </div>
              <div class="spec-item">
                <div class="spec-label">Category</div>
                <div class="spec-value"><?php echo htmlspecialchars($selected_product['category']); ?></div>
              </div>
              <div class="spec-item">
                <div class="spec-label">Views</div>
                <div class="spec-value"><?php echo $selected_product['views'] ?? 0; ?></div>
              </div>
            </div>

            <div class="quantity-selector">
              <button class="qty-btn" onclick="updateQty(-1)">-</button>
              <input type="number" class="qty-input" value="1" min="1" id="qtyInput">
              <button class="qty-btn" onclick="updateQty(1)">+</button>
            </div>

            <div class="action-buttons">
              <a href="shop.php?add_to_cart=<?php echo $selected_product['id']; ?>&redirect=shop.php?product_id=<?php echo $selected_product['id']; ?>"
                class="btn-primary-action text-decoration-none" onclick="showToast('Added to cart!', 'success')">
                <i class="fas fa-cart-plus"></i> Add to Cart
              </a>
              <button class="btn-secondary-action" onclick="addToWishlist(<?php echo $selected_product['id']; ?>)">
                <i class="fas fa-heart"></i>
              </button>
            </div>

            <!-- VULNERABILITY: Metadata exposure -->
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
              <h6 style="font-family: 'JetBrains Mono', monospace; color: var(--accent-cyan); margin-bottom: 0.75rem;">
                <i class="fas fa-info-circle"></i> Technical Specs
              </h6>
              <pre
                style="background: var(--bg-secondary); border-radius: 10px; padding: 1rem; color: var(--text-secondary); font-size: 0.8rem; margin: 0; overflow-x: auto;"><?php echo htmlspecialchars($selected_product['metadata'] ?? 'No metadata available'); ?></pre>
            </div>
          </div>
        </div>
      </div>

      <!-- Product Tabs -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="reviews-section">
            <div class="tab-nav">
              <button class="tab-btn active" onclick="switchTab('description')">Description</button>
              <button class="tab-btn" onclick="switchTab('reviews')">Reviews (<?php echo count($reviews); ?>)</button>
              <button class="tab-btn" onclick="switchTab('shipping')">Shipping</button>
              <button class="tab-btn" onclick="switchTab('metadata')">API Metadata</button>
            </div>

            <div id="tab-description" class="tab-panel active">
              <h4 style="margin-bottom: 1rem;">About this product</h4>
              <p style="color: var(--text-secondary); line-height: 1.8;">
                <?php echo htmlspecialchars($selected_product['description']); ?>
              </p>
              <p style="color: var(--text-secondary); line-height: 1.8; margin-top: 1rem;">
                This professional-grade cybersecurity tool is designed for ethical hackers, penetration testers, and
                security researchers.
                All products come with a 30-day warranty and dedicated technical support.
                Perfect for CTF competitions, red team exercises, and security audits.
              </p>
            </div>

            <div id="tab-reviews" class="tab-panel">
              <!-- Review Form -->
              <div class="review-form">
                <h5 style="margin-bottom: 1rem;">Write a Review</h5>
                <form method="POST" action="shop.php">
                  <input type="hidden" name="product_id" value="<?php echo $selected_product['id']; ?>">
                  <div class="mb-3">
                    <label style="color: var(--text-secondary); margin-bottom: 0.5rem; display: block;">Rating</label>
                    <div class="star-rating" style="flex-direction: row-reverse; justify-content: flex-end;">
                      <input type="radio" id="star5" name="rating" value="5"><label for="star5"><i
                          class="fas fa-star"></i></label>
                      <input type="radio" id="star4" name="rating" value="4"><label for="star4"><i
                          class="fas fa-star"></i></label>
                      <input type="radio" id="star3" name="rating" value="3"><label for="star3"><i
                          class="fas fa-star"></i></label>
                      <input type="radio" id="star2" name="rating" value="2"><label for="star2"><i
                          class="fas fa-star"></i></label>
                      <input type="radio" id="star1" name="rating" value="1"><label for="star1"><i
                          class="fas fa-star"></i></label>
                    </div>
                  </div>
                  <div class="mb-3">
                    <textarea name="comment" class="search-input" rows="3"
                      placeholder="Share your experience... XSS payloads welcome for testing"></textarea>
                  </div>
                  <button type="submit" name="submit_review" class="btn-search">
                    <i class="fas fa-paper-plane"></i> Submit Review
                  </button>
                </form>
              </div>

              <!-- Reviews List -->
              <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                  <div class="review-card-modern">
                    <div class="review-header-modern">
                      <div class="reviewer-info">
                        <div class="reviewer-avatar"><?php echo strtoupper(substr($review['username'], 0, 1)); ?></div>
                        <div>
                          <div class="reviewer-name"><?php echo $review['username']; ?></div>
                          <div class="review-date"><?php echo date('F d, Y', strtotime($review['created_at'])); ?></div>
                        </div>
                      </div>
                      <div class="review-rating">
                        <?php echo getRatingStars($review['rating']); ?>
                      </div>
                    </div>
                    <!-- VULNERABILITY: Stored XSS - No output encoding on comment -->
                    <div style="color: var(--text-secondary); line-height: 1.6;">
                      <?php echo $review['comment']; ?>
                      <!-- FLAG: DH{xss_review_pwned} - XSS payload executes here -->
                    </div>
                    <div class="review-actions">
                      <button class="review-action-btn"><i class="fas fa-thumbs-up"></i> Helpful</button>
                      <button class="review-action-btn"><i class="fas fa-flag"></i> Report</button>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="empty-state" style="padding: 2rem 0;">
                  <i class="fas fa-comment-dots"
                    style="font-size: 2rem; color: var(--text-muted); margin-bottom: 0.5rem;"></i>
                  <p style="color: var(--text-muted);">No reviews yet. Be the first to review!</p>
                </div>
              <?php endif; ?>
            </div>

            <div id="tab-shipping" class="tab-panel">
              <h4 style="margin-bottom: 1rem;">Shipping Information</h4>
              <div class="specs-grid" style="max-width: 600px;">
                <div class="spec-item">
                  <div class="spec-label">Delivery Time</div>
                  <div class="spec-value">3-5 Business Days</div>
                </div>
                <div class="spec-item">
                  <div class="spec-label">Shipping Cost</div>
                  <div class="spec-value">Free over $100</div>
                </div>
                <div class="spec-item">
                  <div class="spec-label">Tracking</div>
                  <div class="spec-value">Real-time Updates</div>
                </div>
                <div class="spec-item">
                  <div class="spec-label">Returns</div>
                  <div class="spec-value">30-Day Policy</div>
                </div>
              </div>
            </div>

            <div id="tab-metadata" class="tab-panel">
              <h4 style="margin-bottom: 1rem;">Internal API Metadata</h4>
              <div
                style="background: var(--bg-secondary); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color);">
                <pre
                  style="color: var(--text-secondary); font-size: 0.85rem; margin: 0; font-family: 'JetBrains Mono', monospace;">{
    "product_id": <?php echo $selected_product['id']; ?>,
    "internal_sku": "DH-<?php echo str_pad($selected_product['id'], 4, '0', STR_PAD_LEFT); ?>",
    "warehouse_location": "US-EAST-1",
    "supplier_api": "http://internal-api.darkhunter.local/v1/products",
    "inventory_service": "http://inventory.darkhunter.local:8080",
    "last_sync": "<?php echo date('c'); ?>",
    "debug_mode": true,
    "api_version": "2.1.0-beta"
}</pre>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <?php if (!empty($related_products)): ?>
        <div class="row mt-4">
          <div class="col-12">
            <h3 style="font-family: 'JetBrains Mono', monospace; margin-bottom: 1.5rem;">
              <i class="fas fa-thumbs-up"></i> Related Products
            </h3>
            <div class="product-grid">
              <?php foreach ($related_products as $product): ?>
                <div class="product-card">
                  <div class="product-image-wrapper" style="height: 160px;">
                    <img src="https://picsum.photos/seed/<?php echo $product['id']; ?>/300/200"
                      alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                  </div>
                  <div class="product-body" style="padding: 1rem;">
                    <div class="product-category" style="font-size: 0.7rem;">
                      <?php echo htmlspecialchars($product['category']); ?></div>
                    <h3 class="product-title" style="font-size: 0.9rem;"><?php echo htmlspecialchars($product['name']); ?>
                    </h3>
                    <div class="product-meta" style="margin-bottom: 0.5rem;">
                      <span class="product-price"
                        style="font-size: 1rem;">$<?php echo number_format($product['price'], 2); ?></span>
                    </div>
                    <a href="shop.php?product_id=<?php echo $product['id']; ?>"
                      class="btn-add-cart w-100 text-center text-decoration-none"
                      style="padding: 0.5rem; font-size: 0.85rem;">
                      View Details
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <h6><i class="fas fa-bug"></i> DarkHunter</h6>
          <p style="color: var(--text-muted); font-size: 0.85rem;">Advanced cybersecurity training platform for ethical
            hackers and security professionals.</p>
        </div>
        <div class="footer-col">
          <h6>Shop</h6>
          <a href="shop.php">All Products</a>
          <a href="shop.php?category=Hardware">Hardware</a>
          <a href="shop.php?category=Software">Software</a>
          <a href="shop.php?category=Services">Services</a>
        </div>
        <div class="footer-col">
          <h6>Support</h6>
          <a href="account.php">Account</a>
          <a href="cart.php">Cart</a>
          <a href="submit.php">Submit Flag</a>
        </div>
        <div class="footer-col">
          <h6>Legal</h6>
          <a href="#">Terms of Service</a>
          <a href="#">Privacy Policy</a>
          <a href="#">Responsible Disclosure</a>
        </div>
      </div>
      <div class="footer-bottom">
        <p>DarkHunter CTF Platform - Educational Use Only - <?php echo date('Y'); ?></p>
      </div>
    </div>
  </footer>

  <!-- VULNERABILITY: Exposed JavaScript config with sensitive data -->
  <!-- Realistic staging config leak - developers often forget these in production -->
  <script>
    // DarkShop Client SDK Configuration
    // Environment: staging (accidentally deployed to prod)
    window.DARKSHOP_CONFIG = {
      env: 'staging',
      version: '2.1.0-beta',
      apiBaseUrl: '/api/v2',
      internalEndpoints: {
        inventory: 'http://inventory.darkhunter.local:8080',
        analytics: 'http://analytics.darkhunter.local:9090',
        webhook: 'http://webhook.darkhunter.local/hooks/shop',
        imageProxy: 'http://proxy.darkhunter.local:3000'
      },
      services: {
        paymentGateway: 'https://pay-staging.darkhunter.local/v1',
        shipping: 'https://ship-staging.darkhunter.local/api',
        taxCalculator: 'https://tax-staging.darkhunter.local/calc'
      },
      // VULNERABILITY: API key exposed in client-side JS
      apiKey: 'YOUR_BREVO_API_KEY',
      // VULNERABILITY: JWT secret hint
      jwtSecretHint: 'darkhunter_secret_key_123',
      // VULNERABILITY: Debug mode enabled
      debug: true,
      features: {
        newCheckout: true,
        betaRecommendations: true,
        experimentalSearch: true
      }
    };

    // VULNERABILITY: Internal API client exposed
    class InternalAPIClient {
      constructor() {
        this.baseURL = window.DARKSHOP_CONFIG.internalEndpoints.inventory;
        this.apiKey = window.DARKSHOP_CONFIG.apiKey;
      }

      async fetchInventory(productId) {
        // SSRF vulnerability - no URL validation
        return fetch(`${this.baseURL}/products/${productId}`, {
          headers: {
            'X-API-Key': this.apiKey
          }
        });
      }

      async proxyImage(url) {
        // SSRF through image proxy
        return fetch(`${window.DARKSHOP_CONFIG.internalEndpoints.imageProxy}/fetch?url=${encodeURIComponent(url)}`);
      }

      async webhookTest(url) {
        // SSRF through webhook testing
        return fetch(`${window.DARKSHOP_CONFIG.internalEndpoints.webhook}/test`, {
          method: 'POST',
          body: JSON.stringify({
            url: url
          })
        });
      }
    }

    // Initialize client
    window.internalAPI = new InternalAPIClient();

    // VULNERABILITY: Debug logging in production
    console.group('%c DarkShop Debug Console ',
      'background: linear-gradient(135deg, #ff1744, #b967ff); color: white; font-size: 14px; font-weight: bold; padding: 4px 8px; border-radius: 4px;'
    );
    console.log('%c Environment:', 'color: #00d4ff; font-weight: bold;', window.DARKSHOP_CONFIG.env);
    console.log('%c API Key:', 'color: #00d4ff; font-weight: bold;', window.DARKSHOP_CONFIG.apiKey);
    console.log('%c Internal Endpoints:', 'color: #00d4ff; font-weight: bold;', window.DARKSHOP_CONFIG.internalEndpoints);
    console.log('%c JWT Hint:', 'color: #ff1744; font-weight: bold;', window.DARKSHOP_CONFIG.jwtSecretHint);
    console.groupEnd();

    // Hidden flag in JS source
    const _shopFlag = "DH{api_key_exposure}";

    // VULNERABILITY: CORS misconfiguration test
    console.log('%c CORS Test Endpoint:', 'color: #ff9100;', 'https://api.darkhunter.local/cors-test');

    // Toast Notification System
    function showToast(message, type = 'info', duration = 3000) {
      const container = document.getElementById('toastContainer');
      const toast = document.createElement('div');
      toast.className = `toast-notification toast-${type}`;

      const icons = {
        success: '<i class="fas fa-check-circle" style="color: var(--accent-green);"></i>',
        error: '<i class="fas fa-times-circle" style="color: var(--accent-red);"></i>',
        info: '<i class="fas fa-info-circle" style="color: var(--accent-cyan);"></i>'
      };

      toast.innerHTML = `${icons[type]} <span>${message}</span>`;
      container.appendChild(toast);

      setTimeout(() => {
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
      }, duration);
    }

    // Tab Switching
    function switchTab(tabName) {
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));

      event.target.classList.add('active');
      document.getElementById('tab-' + tabName).classList.add('active');
    }

    // Quantity Selector
    function updateQty(change) {
      const input = document.getElementById('qtyInput');
      let val = parseInt(input.value) + change;
      if (val < 1) val = 1;
      // VULNERABILITY: No upper limit validation - potential integer abuse
      input.value = val;
    }

    // Wishlist System with intentional DOM XSS vulnerability
    function addToWishlist(productId) {
      let wishlist = JSON.parse(localStorage.getItem('darkshop_wishlist') || '[]');

      if (!wishlist.includes(productId)) {
        wishlist.push(productId);
        localStorage.setItem('darkshop_wishlist', JSON.stringify(wishlist));

        // Update button state
        const btn = document.getElementById('wishlist-' + productId);
        if (btn) {
          btn.classList.add('active');
          btn.innerHTML = '<i class="fas fa-heart"></i>';
        }

        showToast('Added to wishlist!', 'success');

        // VULNERABILITY: DOM XSS through wishlist rendering
        // The wishlist is rendered unsafely in some contexts
        updateWishlistCounter();
      } else {
        showToast('Already in wishlist', 'info');
      }
    }

    function updateWishlistCounter() {
      const wishlist = JSON.parse(localStorage.getItem('darkshop_wishlist') || '[]');
      // VULNERABILITY: Potential XSS if wishlist data is manipulated
      console.log('Wishlist items:', wishlist);
    }

    // Cart functionality
    function updateCartBadge() {
      // In real implementation, this would fetch from server
      console.log('Cart updated');
    }

    // VULNERABILITY: Exposed admin endpoints in JS
    const ADMIN_ENDPOINTS = {
      users: '/api/admin/users',
      orders: '/api/admin/orders',
      products: '/api/admin/products',
      config: '/api/admin/config',
      logs: '/api/admin/logs'
    };

    // VULNERABILITY: Source map reference (leaks file structure)
    //# sourceMappingURL=/static/js/darkshop.min.js.map
  </script>

  <!-- VULNERABILITY: Hidden staging config in comment -->
  <!-- 
        Staging Configuration
        =====================
        Database: mysql-staging.darkhunter.local:3306
        Redis: redis-staging.darkhunter.local:6379
        Elasticsearch: es-staging.darkhunter.local:9200
        Kibana: http://kibana.darkhunter.local:5601
        Jenkins: http://jenkins.darkhunter.local:8080
        GitLab: http://gitlab.darkhunter.local

        Internal Services:
        - Payment: pay-staging.darkhunter.local
        - Shipping: ship-staging.darkhunter.local  
        - Tax: tax-staging.darkhunter.local
        - Inventory: inventory.darkhunter.local:8080

        FLAG: DH{staging_config_leak}
    -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
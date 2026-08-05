<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/functions.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/CTF/ctf_session.php';

// VULNERABILITY: No CSRF protection on cart operations
// VULNERABILITY: No input validation on quantity (negative values possible)
if (isset($_POST['update_qty'])) {

  $product_id = $_POST['product_id'];
  $qty = $_POST['quantity']; // No validation - can be negative, zero, or huge

  if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
  }

  // VULNERABILITY: Integer overflow / negative quantity abuse
  if ($qty <= 0) {

    // Remove from cart
    $_SESSION['cart'] = array_diff($_SESSION['cart'], [$product_id]);
  } else {

    // VULNERABILITY: No max quantity check
    $_SESSION['cart'] = array_merge(
      array_diff($_SESSION['cart'], [$product_id]),
      array_fill(0, $qty, $product_id)
    );
  }

  header("Location: cart.php");
  exit;
}

if (isset($_GET['remove'])) {

  $product_id = $_GET['remove'];

  if (isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array_diff($_SESSION['cart'], [$product_id]);
  }

  header("Location: cart.php");
  exit;
}

// VULNERABILITY: Coupon stacking - no check for multiple coupons
$coupon_discount = 0;
$coupon_code = '';

if (isset($_SESSION['coupon'])) {

  $coupon_code = $_SESSION['coupon'];

  // VULNERABILITY: Weak coupon validation
  if (
    $coupon_code === 'HACKER10' ||
    $coupon_code === 'CTF2024' ||
    $coupon_code === 'ADMIN999'
  ) {
    $coupon_discount = 0.10;
  }

  // VULNERABILITY: ADMIN999 gives bigger discount
  if ($coupon_code === 'ADMIN999') {
    $coupon_discount = 0.50;
  }
}

// VULNERABILITY: Race condition - price calculated client-side could differ
$cart_items = [];
$subtotal = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {

  $counts = array_count_values($_SESSION['cart']);

  foreach ($counts as $product_id => $qty) {

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);

    $product = $stmt->fetch();

    if ($product) {

      $item_total = $product['price'] * $qty;

      $subtotal += $item_total;

      $cart_items[] = [
        'product' => $product,
        'qty' => $qty,
        'total' => $item_total
      ];
    }
  }
}

$discount_amount = $subtotal * $coupon_discount;
$shipping = $subtotal > 100 ? 0 : 15.99;
$tax = ($subtotal - $discount_amount) * 0.08;
$total = $subtotal - $discount_amount + $shipping + $tax;

$cart_count = isset($_SESSION['cart'])
  ? count($_SESSION['cart'])
  : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart | DarkShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/CTF/css/cart.css">
</head>

<body>
  <div class="bg-grid"></div>
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
          <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="account.php">Account</a></li>
          <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
          <li class="nav-item"><a class="nav-link" href="submit.php">Submit Flag</a></li>
          <li class="nav-item">
            <a class="nav-link active" href="cart.php">
              <i class="fas fa-shopping-cart"></i> Cart
              <?php if ($cart_count > 0): ?>
              <span class="cart-badge"><?php echo $cart_count; ?></span>
              <?php endif; ?>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container" style="position: relative; z-index: 1;">
    <div class="page-header">
      <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
      <p style="color: var(--text-secondary);">Review your items and proceed to checkout</p>
    </div>

    <?php if (empty($cart_items)): ?>
    <div class="empty-cart">
      <div class="empty-cart-icon"><i class="fas fa-shopping-basket"></i></div>
      <h3 style="color: var(--text-secondary); margin-bottom: 0.5rem;">Your cart is empty</h3>
      <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Looks like you haven't added any items yet.</p>
      <a href="shop.php" class="btn-checkout text-decoration-none d-inline-block"
        style="width: auto; padding: 0.75rem 2rem;">
        <i class="fas fa-arrow-left"></i> Continue Shopping
      </a>
    </div>
    <?php else: ?>
    <div class="row">
      <div class="col-lg-8">
        <!-- Cart Items -->
        <div class="cart-card">
          <h5 style="font-family: 'JetBrains Mono', monospace; margin-bottom: 1rem;">
            <i class="fas fa-box"></i> Cart Items (<?php echo count($cart_items); ?>)
          </h5>

          <?php foreach ($cart_items as $item): ?>
          <div class="cart-item">
            <div class="cart-item-img">
              <i class="fas fa-shield-alt"></i>
            </div>
            <div class="cart-item-details">
              <div class="cart-item-name"><?php echo htmlspecialchars($item['product']['name']); ?></div>
              <div class="cart-item-meta"><?php echo htmlspecialchars($item['product']['category']); ?> | SKU:
                DH-<?php echo str_pad($item['product']['id'], 4, '0', STR_PAD_LEFT); ?></div>
              <div class="cart-item-price">$<?php echo number_format($item['product']['price'], 2); ?> each</div>
            </div>
            <div class="qty-control">
              <form method="POST" action="cart.php" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                <button type="submit" name="update_qty" value="1" class="qty-btn-sm"
                  onclick="this.form.quantity.value=Math.max(1, parseInt(this.form.quantity.value)-1); return true;">-</button>
                <!-- VULNERABILITY: No validation on quantity input -->
                <input type="number" name="quantity" class="qty-input-sm" value="<?php echo $item['qty']; ?>" min="0">
                <button type="submit" name="update_qty" value="1" class="qty-btn-sm"
                  onclick="this.form.quantity.value=parseInt(this.form.quantity.value)+1; return true;">+</button>
              </form>
            </div>
            <div style="text-align: right; min-width: 100px;">
              <div class="cart-item-price">$<?php echo number_format($item['total'], 2); ?></div>
              <a href="cart.php?remove=<?php echo $item['product']['id']; ?>" class="remove-btn">
                <i class="fas fa-trash"></i> Remove
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Shipping Info -->
        <div class="cart-card">
          <h5 style="font-family: 'JetBrains Mono', monospace; margin-bottom: 1rem;">
            <i class="fas fa-truck"></i> Shipping Information
          </h5>
          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-input" placeholder="John Doe">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-input" placeholder="john@example.com">
            </div>
            <div class="col-12" style="margin-top: 0.75rem;">
              <label class="form-label">Address</label>
              <input type="text" class="form-input" placeholder="123 Cyber Street, Tech City">
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <!-- Order Summary -->
        <div class="summary-card">
          <h5 style="font-family: 'JetBrains Mono', monospace; margin-bottom: 1rem;">
            <i class="fas fa-receipt"></i> Order Summary
          </h5>

          <div class="summary-row">
            <span class="summary-label">Subtotal</span>
            <span class="summary-value">$<?php echo number_format($subtotal, 2); ?></span>
          </div>

          <?php if ($coupon_discount > 0): ?>
          <div class="summary-row">
            <span class="summary-label">Discount (<?php echo $coupon_code; ?>)</span>
            <span class="summary-value"
              style="color: var(--accent-green);">-$<?php echo number_format($discount_amount, 2); ?></span>
          </div>
          <?php endif; ?>

          <div class="summary-row">
            <span class="summary-label">Shipping</span>
            <span
              class="summary-value"><?php echo $shipping === 0 ? 'FREE' : '$' . number_format($shipping, 2); ?></span>
          </div>

          <div class="summary-row">
            <span class="summary-label">Tax (8%)</span>
            <span class="summary-value">$<?php echo number_format($tax, 2); ?></span>
          </div>

          <div class="summary-total">
            <span class="label">Total</span>
            <span class="value">$<?php echo number_format($total, 2); ?></span>
          </div>

          <!-- Payment Methods -->
          <div class="payment-methods">
            <div class="payment-method active">
              <i class="fas fa-credit-card"></i>
              <span>Card</span>
            </div>
            <div class="payment-method">
              <i class="fab fa-bitcoin"></i>
              <span>Crypto</span>
            </div>
            <div class="payment-method">
              <i class="fas fa-university"></i>
              <span>Wire</span>
            </div>
          </div>

          <form action="/DarkHunter/CTF/checkout.php" method="POST">
            <button class="btn-checkout" type="submit">
              <i class="fas fa-lock"></i> Complete Purchase
            </button>
          </form>

          <p style="color: var(--text-muted); font-size: 0.75rem; text-align: center; margin-top: 0.75rem;">
            <i class="fas fa-shield-alt"></i> Secure checkout. This is a training environment.
          </p>
        </div>

        <!-- Coupon -->
        <div class="summary-card" style="margin-top: 1rem;">
          <h5 style="font-family: 'JetBrains Mono', monospace; margin-bottom: 1rem;">
            <i class="fas fa-ticket-alt"></i> Coupon Code
          </h5>
          <form method="POST" action="shop.php">
            <input type="text" name="coupon_code" class="coupon-input" placeholder="Enter code"
              value="<?php echo htmlspecialchars($coupon_code); ?>">
            <button type="submit" name="apply_coupon" class="btn-apply">
              <i class="fas fa-check"></i> Apply Coupon
            </button>
          </form>
          <!-- VULNERABILITY: Coupon hints exposed -->
          <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.5rem; display: block;">
            Try: HACKER10 (10% off), CTF2024 (10% off), ADMIN999 (50% off)
          </small>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <footer>
    <div class="container">
      <p>DarkHunter CTF Platform - Educational Use Only</p>
    </div>
  </footer>

  <script>
  // VULNERABILITY: Client-side price calculation (can be manipulated)
  window.CART_CONFIG = {
    currency: 'USD',
    taxRate: 0.08,
    freeShippingThreshold: 100,
    shippingCost: 15.99,
    // VULNERABILITY: API endpoint for price updates (IDOR possible)
    priceUpdateEndpoint: '/api/cart/update-price',
    // VULNERABILITY: Admin override key exposed
    adminOverrideKey: 'dh_admin_override_9999'
  };

  function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML =
      `<i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}" style="color: var(--accent-${type === 'success' ? 'green' : 'cyan'});"></i> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.animation = 'slideIn 0.3s reverse';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  function fakeCheckout() {
    // VULNERABILITY: Fake checkout with no server validation
    showToast('Order placed successfully! (Training environment)', 'success');
    console.log('%c Checkout Data:', 'color: #00d4ff;', {
      cart: JSON.parse(localStorage.getItem('darkshop_cart') || '[]'),
      coupon: '<?php echo $coupon_code; ?>',
      total: <?php echo $total; ?>
    });
  }

  // VULNERABILITY: Exposed internal API routes
  const API_ROUTES = {
    cart: '/api/v2/cart',
    checkout: '/api/v2/checkout',
    orders: '/api/v2/orders',
    inventory: '/api/v2/inventory',
    pricing: '/api/v2/pricing'
  };

  console.log('%c DarkShop Cart Debug', 'background: #b967ff; color: white; font-size: 14px; padding: 4px 8px;');
  console.log('Cart Items:', <?php echo json_encode($cart_items); ?>);
  console.log('API Routes:', API_ROUTES);
  console.log('Config:', window.CART_CONFIG);
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- VULNERABILITY: Open redirect in continue shopping -->
  <!-- VULNERABILITY: Exposed order processing API -->
  <script>
  // Order Processing API Client
  window.OrderAPI = {
    baseURL: '/api/v2/orders',
    internalURL: 'http://orders.darkhunter.local:8080',
    apiKey: 'dh_order_sk_7a8b9c0d1e2f3g4h',

    // VULNERABILITY: No authentication on order creation
    async createOrder(data) {
      return fetch(this.baseURL + '/create', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-API-Key': this.apiKey
        },
        body: JSON.stringify(data)
      });
    },

    // VULNERABILITY: IDOR - can fetch any order by ID
    async getOrder(orderId) {
      return fetch(this.baseURL + '/' + orderId);
    },

    // VULNERABILITY: SSRF through webhook
    async testWebhook(url) {
      return fetch(this.internalURL + '/webhook/test', {
        method: 'POST',
        body: JSON.stringify({
          url: url
        })
      });
    }
  };

  // VULNERABILITY: Price manipulation helper exposed
  function updateItemPrice(itemId, newPrice) {
    // This would normally be server-side only
    console.log('Price update requested:', itemId, newPrice);
    return fetch('/api/cart/update-price', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        itemId: itemId,
        price: newPrice
      })
    });
  }

  // Hidden flag
  const _cartFlag = "DH{price_manipulation}";

  console.log('%c OrderAPI Debug', 'background: #ff1744; color: white; font-size: 12px; padding: 4px 8px;');
  console.log('API Key:', window.OrderAPI.apiKey);
  console.log('Internal URL:', window.OrderAPI.internalURL);
  </script>
</body>

</html>
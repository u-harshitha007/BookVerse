<?php
/**
 * BookVerse - Checkout Page
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$cartItems = getCartItems($_SESSION['user_id']);
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$subtotal  = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
$shipping  = 50;
$total     = $subtotal + $shipping;
$user      = getCurrentUser();
$cartCount = getCartCount($_SESSION['user_id']);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['customer_name'] ?? '');
    $email   = sanitize($_POST['customer_email'] ?? '');
    $phone   = sanitize($_POST['customer_phone'] ?? '');
    $address = sanitize($_POST['shipping_address'] ?? '');
    $payment = sanitize($_POST['payment_method'] ?? '');

    if (empty($name) || empty($email) || empty($address) || empty($payment)) {
        $error = 'Please fill in all required fields.';
    } else {
        global $conn;

        // Generate Order ID
        $orderId = generateOrderId();

        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (order_id, user_id, customer_name, customer_email, customer_phone, shipping_address, total_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssds", $orderId, $_SESSION['user_id'], $name, $email, $phone, $address, $total, $payment);
        $stmt->execute();
        $dbOrderId = $stmt->insert_id;
        $stmt->close();

        // Insert order items
        foreach ($cartItems as $item) {
            $subtotalItem = $item['price'] * $item['quantity'];
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, book_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidd", $dbOrderId, $item['id'], $item['quantity'], $item['price'], $subtotalItem);
            $stmt->execute();
            $stmt->close();
        }

        // Insert payment record
        $stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, amount, payment_method, status, paid_at) VALUES (?, ?, ?, ?, 'completed', NOW())");
        $stmt->bind_param("iids", $dbOrderId, $_SESSION['user_id'], $total, $payment);
        $stmt->execute();
        $stmt->close();

        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        header('Location: orders.php?success=1&order_id=' . urlencode($orderId));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <title>Checkout – BookVerse</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar" id="navbar">
  <div class="nav-container">
    <a href="index.php" class="nav-logo">
      <div class="logo-icon"><i class="fas fa-book-open"></i></div>
      <div><span class="logo-text">BookVerse</span><span class="logo-tag">Discover. Read. Collect.</span></div>
    </a>
    <div class="nav-actions">
      <a href="cart.php" class="btn btn-outline btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Cart
      </a>
    </div>
  </div>
</nav>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1>Checkout</h1>
    <div class="breadcrumb">
      <a href="index.php">Home</a><span class="sep">/</span>
      <a href="cart.php">Cart</a><span class="sep">/</span><span>Checkout</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">

    <!-- Progress Steps -->
    <div style="display:flex;justify-content:center;gap:2rem;margin-bottom:3rem;flex-wrap:wrap;">
      <?php $steps = ['Cart','Shipping','Payment','Confirmation']; ?>
      <?php foreach ($steps as $i => $step): ?>
        <div style="display:flex;align-items:center;gap:0.5rem;">
          <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;
               background:<?= $i < 3 ? 'var(--gold)' : 'var(--dark-4)' ?>;
               color:<?= $i < 3 ? 'var(--dark)' : 'var(--gray)' ?>;">
            <?= $i < 2 ? '<i class="fas fa-check"></i>' : ($i + 1) ?>
          </div>
          <span style="font-size:0.85rem;font-weight:<?= $i === 2 ? '700' : '400' ?>;color:<?= $i === 2 ? 'var(--gold)' : 'var(--gray)' ?>;"><?= $step ?></span>
          <?php if ($i < count($steps)-1): ?>
            <div style="width:40px;height:1px;background:rgba(255,255,255,0.1);margin-left:0.5rem;"></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" id="checkoutForm">
      <div style="display:grid;grid-template-columns:1fr 380px;gap:2rem;align-items:start;">

        <!-- Shipping & Payment -->
        <div>
          <!-- Shipping Info -->
          <div style="background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);padding:1.75rem;margin-bottom:1.5rem;">
            <h3 style="font-family:var(--font-heading);margin-bottom:1.5rem;display:flex;align-items:center;gap:0.6rem;">
              <i class="fas fa-shipping-fast" style="color:var(--gold);"></i> Shipping Information
            </h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
              <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Full Name <span style="color:var(--danger)">*</span></label>
                <input type="text" name="customer_name" class="form-control"
                       value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email <span style="color:var(--danger)">*</span></label>
                <input type="email" name="customer_email" class="form-control"
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" name="customer_phone" class="form-control"
                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
              </div>
              <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Shipping Address <span style="color:var(--danger)">*</span></label>
                <textarea name="shipping_address" class="form-control" rows="3"
                          placeholder="Street, City, State, PIN Code" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
              </div>
            </div>
          </div>

          <!-- Payment -->
          <div style="background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);padding:1.75rem;">
            <h3 style="font-family:var(--font-heading);margin-bottom:1.5rem;display:flex;align-items:center;gap:0.6rem;">
              <i class="fas fa-credit-card" style="color:var(--gold);"></i> Payment Method
            </h3>
            <?php
            $paymentMethods = [
              ['id' => 'credit_card',  'icon' => 'fa-credit-card',   'label' => 'Credit Card',     'desc' => 'Visa, Mastercard, RuPay'],
              ['id' => 'debit_card',   'icon' => 'fa-credit-card',   'label' => 'Debit Card',      'desc' => 'All major banks'],
              ['id' => 'upi',          'icon' => 'fa-mobile-alt',    'label' => 'UPI',             'desc' => 'Google Pay, PhonePe, Paytm'],
              ['id' => 'net_banking',  'icon' => 'fa-university',    'label' => 'Net Banking',     'desc' => 'All major banks'],
              ['id' => 'cod',          'icon' => 'fa-money-bill-alt','label' => 'Cash on Delivery','desc' => 'Pay when you receive'],
            ];
            foreach ($paymentMethods as $pm): ?>
            <label style="display:flex;align-items:center;gap:1rem;padding:1rem;border:1.5px solid rgba(255,255,255,0.06);border-radius:var(--radius-sm);margin-bottom:0.75rem;cursor:pointer;transition:var(--transition);"
                   onmouseover="this.style.borderColor='rgba(212,175,55,0.3)'"
                   onmouseout="this.style.borderColor='rgba(255,255,255,0.06)'">
              <input type="radio" name="payment_method" value="<?= $pm['id'] ?>" required style="accent-color:var(--gold);">
              <i class="fas <?= $pm['icon'] ?>" style="color:var(--gold);font-size:1.2rem;width:24px;text-align:center;"></i>
              <div>
                <div style="font-weight:600;font-size:0.9rem;"><?= $pm['label'] ?></div>
                <div style="font-size:0.78rem;color:var(--gray);"><?= $pm['desc'] ?></div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
          <h3 class="summary-title">Order Summary</h3>

          <div style="max-height:300px;overflow-y:auto;margin-bottom:1rem;">
            <?php foreach ($cartItems as $item): ?>
            <div style="display:flex;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
              <img src="assets/images/books/<?= htmlspecialchars($item['cover_image']) ?>"
                   style="width:48px;height:64px;border-radius:4px;object-fit:cover;"
                   onerror="this.src='assets/images/default_book.jpg'">
              <div style="flex:1;">
                <div style="font-size:0.85rem;font-weight:600;color:var(--off-white);"><?= htmlspecialchars($item['title']) ?></div>
                <div style="font-size:0.75rem;color:var(--gray);">x<?= $item['quantity'] ?></div>
              </div>
              <div style="font-size:0.9rem;font-weight:600;color:var(--gold);">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="summary-row"><span>Subtotal</span><span>₹<?= number_format($subtotal, 2) ?></span></div>
          <div class="summary-row"><span>Shipping</span><span>₹<?= number_format($shipping, 2) ?></span></div>
          <div class="summary-row total"><span>Total</span><span>₹<?= number_format($total, 2) ?></span></div>

          <button type="submit" class="btn btn-gold btn-block btn-lg mt-3">
            <i class="fas fa-lock"></i> Place Order
          </button>

          <div style="display:flex;align-items:center;justify-content:center;gap:0.5rem;margin-top:1rem;font-size:0.78rem;color:var(--gray);">
            <i class="fas fa-shield-alt" style="color:var(--success);"></i>
            Your information is secure
          </div>
        </div>

      </div>
    </form>
  </div>
</section>

<footer class="footer">
  <div class="container">
    <div class="footer-bottom">
      <p class="footer-copy">&copy; <?= date('Y') ?> BookVerse. All rights reserved.</p>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>

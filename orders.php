<?php
/**
 * BookVerse - Orders Page
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$orders    = getOrders($_SESSION['user_id']);
$cartCount = getCartCount($_SESSION['user_id']);
$successOrderId = sanitize($_GET['order_id'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <title>My Orders – BookVerse</title>
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
    <ul class="nav-menu" id="navMenu">
      <li><a href="index.php" class="nav-link">Home</a></li>
      <li><a href="books.php" class="nav-link">Books</a></li>
      <li><a href="orders.php" class="nav-link active">Orders</a></li>
    </ul>
    <div class="nav-actions">
      <a href="cart.php" class="btn btn-outline btn-sm cart-badge">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cartCount > 0): ?><span class="badge"><?= $cartCount ?></span><?php endif; ?>
      </a>
      <a href="profile.php" class="nav-link"><img src="uploads/<?= htmlspecialchars($_SESSION['user_picture'] ?? 'default.png') ?>" class="nav-avatar" onerror="this.src='assets/images/default_avatar.png'"></a>
      <a href="logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i></a>
      <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
    </div>
  </div>
</nav>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1>My Orders</h1>
    <div class="breadcrumb">
      <a href="index.php">Home</a><span class="sep">/</span><span>Orders</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success mb-3">
        <i class="fas fa-check-circle"></i>
        <div>
          <strong>Order Placed Successfully!</strong><br>
          <?php if ($successOrderId): ?>
            Your Order ID is <strong style="color:var(--gold);"><?= htmlspecialchars($successOrderId) ?></strong>.
            We'll send confirmation to your email.
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <h3>No orders yet</h3>
        <p>Start shopping to see your orders here</p>
        <a href="books.php" class="btn btn-gold mt-2">Browse Books</a>
      </div>
    <?php else: ?>

      <div style="display:grid;gap:1.5rem;">
        <?php foreach ($orders as $order):
          $items = getOrderItems($order['id']); ?>

        <div style="background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);overflow:hidden;transition:var(--transition);">

          <!-- Order Header -->
          <div style="display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid rgba(255,255,255,0.06);flex-wrap:wrap;gap:1rem;">
            <div style="display:flex;gap:2rem;flex-wrap:wrap;">
              <div>
                <div style="font-size:0.72rem;color:var(--gray);text-transform:uppercase;letter-spacing:1px;">Order ID</div>
                <div style="font-weight:700;color:var(--gold);"><?= htmlspecialchars($order['order_id']) ?></div>
              </div>
              <div>
                <div style="font-size:0.72px;color:var(--gray);text-transform:uppercase;letter-spacing:1px;font-size:0.72rem;">Date</div>
                <div style="font-weight:500;"><?= date('d M Y', strtotime($order['created_at'])) ?></div>
              </div>
              <div>
                <div style="font-size:0.72rem;color:var(--gray);text-transform:uppercase;letter-spacing:1px;">Total</div>
                <div style="font-weight:700;color:var(--off-white);">₹<?= number_format($order['total_amount'], 2) ?></div>
              </div>
              <div>
                <div style="font-size:0.72rem;color:var(--gray);text-transform:uppercase;letter-spacing:1px;">Payment</div>
                <div style="font-weight:500;text-transform:capitalize;"><?= str_replace('_', ' ', $order['payment_method']) ?></div>
              </div>
            </div>
            <span class="status-badge status-<?= $order['status'] ?>">
              <?= ucfirst($order['status']) ?>
            </span>
          </div>

          <!-- Order Items -->
          <div style="padding:1.25rem 1.5rem;">
            <div style="display:flex;flex-wrap:wrap;gap:1rem;">
              <?php foreach ($items as $item): ?>
              <div style="display:flex;gap:0.75rem;align-items:flex-start;flex:1;min-width:200px;">
                <img src="assets/images/books/<?= htmlspecialchars($item['cover_image'] ?? 'default_book.jpg') ?>"
                     style="width:52px;height:70px;border-radius:6px;object-fit:cover;"
                     onerror="this.src='assets/images/default_book.jpg'">
                <div>
                  <div style="font-size:0.9rem;font-weight:600;color:var(--off-white);"><?= htmlspecialchars($item['title'] ?? 'Book') ?></div>
                  <div style="font-size:0.8rem;color:var(--gray-light);">by <?= htmlspecialchars($item['author'] ?? '') ?></div>
                  <div style="font-size:0.78rem;color:var(--gray);margin-top:0.3rem;">
                    Qty: <?= $item['quantity'] ?> × ₹<?= number_format($item['unit_price'], 2) ?>
                    = <strong style="color:var(--gold);">₹<?= number_format($item['subtotal'], 2) ?></strong>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

            <?php if ($order['shipping_address']): ?>
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,0.05);display:flex;gap:2rem;flex-wrap:wrap;">
              <div style="font-size:0.82rem;">
                <span style="color:var(--gray);">Ship to: </span>
                <span style="color:var(--gray-light);"><?= htmlspecialchars($order['shipping_address']) ?></span>
              </div>
            </div>
            <?php endif; ?>
          </div>

        </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>
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

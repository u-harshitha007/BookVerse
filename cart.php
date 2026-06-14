<?php
/**
 * BookVerse - Shopping Cart Page
 * Handles both page rendering and AJAX requests
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// ── AJAX Handlers ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Please login first.']);
        exit;
    }

    $action  = $_POST['action'];
    $userId  = $_SESSION['user_id'];

    if ($action === 'add') {
        $bookId  = intval($_POST['book_id'] ?? 0);
        $qty     = intval($_POST['quantity'] ?? 1);
        addToCart($userId, $bookId, $qty);
        $count = getCartCount($userId);
        echo json_encode(['success' => true, 'cart_count' => $count]);
        exit;
    }

    if ($action === 'remove') {
        $cartId = intval($_POST['cart_id'] ?? 0);
        removeFromCart($cartId, $userId);
        $count = getCartCount($userId);
        echo json_encode(['success' => true, 'cart_count' => $count]);
        exit;
    }

    if ($action === 'update') {
        $cartId = intval($_POST['cart_id'] ?? 0);
        $qty    = intval($_POST['quantity'] ?? 1);
        updateCartQty($cartId, $userId, $qty);

        // Calculate new item total
        global $conn;
        $stmt = $conn->prepare("SELECT c.quantity, b.price FROM cart c JOIN books b ON c.book_id = b.id WHERE c.id = ? AND c.user_id = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $itemTotal = $row ? '₹' . number_format($row['quantity'] * $row['price'], 2) : '';
        echo json_encode(['success' => true, 'item_total' => $itemTotal]);
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
}

// Summary endpoint
if (isset($_GET['action']) && $_GET['action'] === 'summary' && isLoggedIn()) {
    header('Content-Type: application/json');
    $items = getCartItems($_SESSION['user_id']);
    $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
    $shipping  = $subtotal > 0 ? 50 : 0;
    $total     = $subtotal + $shipping;
    echo json_encode([
        'count'    => array_sum(array_column($items, 'quantity')),
        'subtotal' => '₹' . number_format($subtotal, 2),
        'total'    => '₹' . number_format($total, 2),
    ]);
    exit;
}

// ── Page View ──
requireLogin();
$cartItems = getCartItems($_SESSION['user_id']);
$subtotal  = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
$shipping  = $subtotal > 0 ? 50 : 0;
$total     = $subtotal + $shipping;
$cartCount = getCartCount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <title>Shopping Cart – BookVerse</title>
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
      <?php if (isAdmin()): ?><li><a href="admin.php" class="nav-link text-gold">Admin</a></li><?php endif; ?>
    </ul>
    <div class="nav-actions">
      <a href="cart.php" class="btn btn-gold btn-sm cart-badge">
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
    <h1>Shopping Cart</h1>
    <div class="breadcrumb">
      <a href="index.php">Home</a><span class="sep">/</span><span>Cart</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">

    <?php if (empty($cartItems)): ?>
      <div class="empty-state">
        <i class="fas fa-shopping-cart"></i>
        <h3>Your cart is empty</h3>
        <p>Add some books to get started</p>
        <a href="books.php" class="btn btn-gold mt-2">Browse Books</a>
      </div>
    <?php else: ?>
      <div class="cart-layout">

        <!-- Cart Items -->
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <h2 style="font-family:var(--font-heading);font-size:1.3rem;">
              <span id="summaryCount"><?= array_sum(array_column($cartItems, 'quantity')) ?> item(s)</span>
            </h2>
            <a href="books.php" style="color:var(--gold);font-size:0.85rem;">
              <i class="fas fa-plus"></i> Continue Shopping
            </a>
          </div>

          <?php foreach ($cartItems as $item): ?>
          <div class="cart-item" data-cart-id="<?= $item['cart_id'] ?>">
            <div class="cart-item-cover">
              <img src="assets/images/books/<?= htmlspecialchars($item['cover_image']) ?>"
                   alt="<?= htmlspecialchars($item['title']) ?>"
                   onerror="this.src='assets/images/default_book.jpg'">
            </div>
            <div class="cart-item-info">
              <div class="cart-item-title"><?= htmlspecialchars($item['title']) ?></div>
              <div class="cart-item-author">by <?= htmlspecialchars($item['author']) ?></div>
              <div class="book-category" style="margin-bottom:0.75rem;"><?= htmlspecialchars($item['category']) ?></div>
              <div class="qty-control">
                <button type="button" class="qty-btn minus">−</button>
                <input type="text" class="qty-val" value="<?= $item['quantity'] ?>" readonly>
                <button type="button" class="qty-btn plus">+</button>
              </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.75rem;">
              <div class="cart-item-price">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
              <div style="font-size:0.78rem;color:var(--gray);">₹<?= number_format($item['price'], 2) ?> each</div>
              <button class="cart-item-remove" title="Remove"><i class="fas fa-trash-alt"></i></button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
          <h3 class="summary-title">Order Summary</h3>

          <div class="summary-row">
            <span>Subtotal</span>
            <span id="summarySubtotal">₹<?= number_format($subtotal, 2) ?></span>
          </div>
          <div class="summary-row">
            <span>Shipping</span>
            <span>₹<?= number_format($shipping, 2) ?></span>
          </div>
          <div class="summary-row">
            <span>Tax (0%)</span>
            <span>₹0.00</span>
          </div>
          <div class="summary-row total">
            <span>Total</span>
            <span id="summaryTotal">₹<?= number_format($total, 2) ?></span>
          </div>

          <a href="checkout.php" class="btn btn-gold btn-block btn-lg mt-3">
            <i class="fas fa-lock"></i> Proceed to Checkout
          </a>

          <div style="display:flex;align-items:center;justify-content:center;gap:0.5rem;margin-top:1rem;font-size:0.78rem;color:var(--gray);">
            <i class="fas fa-shield-alt" style="color:var(--success);"></i>
            Secure & Encrypted Checkout
          </div>

          <div style="border-top:1px solid rgba(255,255,255,0.06);margin-top:1.25rem;padding-top:1rem;">
            <p style="font-size:0.8rem;color:var(--gray);margin-bottom:0.6rem;">We accept:</p>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
              <?php foreach (['Credit Card','Debit Card','UPI','Net Banking','COD'] as $pm): ?>
                <span style="background:var(--dark-4);padding:0.25rem 0.6rem;border-radius:4px;font-size:0.72rem;color:var(--gray-light);"><?= $pm ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

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

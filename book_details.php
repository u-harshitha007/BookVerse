<?php
/**
 * BookVerse - Book Details Page
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$id   = intval($_GET['id'] ?? 0);
$book = $id ? getBookById($id) : null;

if (!$book) {
    header('Location: books.php');
    exit;
}

$cartCount   = isLoggedIn() ? getCartCount($_SESSION['user_id']) : 0;
$discount    = ($book['original_price'] > $book['price'])
    ? round((($book['original_price'] - $book['price']) / $book['original_price']) * 100) : 0;

// Related books
$related = getBooks(['category' => $book['category']], 4);
$related = array_filter($related, fn($b) => $b['id'] != $book['id']);

// Handle add to cart
$cartMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    addToCart($_SESSION['user_id'], $book['id'], intval($_POST['quantity'] ?? 1));
    $cartMessage = 'Added to cart successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <title><?= htmlspecialchars($book['title']) ?> – BookVerse</title>
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
      <li><a href="books.php" class="nav-link active">Books</a></li>
      <?php if (isAdmin()): ?><li><a href="admin.php" class="nav-link text-gold">Admin</a></li><?php endif; ?>
    </ul>
    <div class="nav-actions">
      <a href="cart.php" class="btn btn-outline btn-sm cart-badge">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cartCount > 0): ?><span class="badge"><?= $cartCount ?></span><?php endif; ?>
      </a>
      <?php if (isLoggedIn()): ?>
        <a href="profile.php" class="nav-link"><img src="uploads/<?= htmlspecialchars($_SESSION['user_picture'] ?? 'default.png') ?>" class="nav-avatar" onerror="this.src='assets/images/default_avatar.png'"></a>
        <a href="logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i></a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline btn-sm">Login</a>
        <a href="register.php" class="btn btn-gold btn-sm">Sign Up</a>
      <?php endif; ?>
      <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
    </div>
  </div>
</nav>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a><span class="sep">/</span>
      <a href="books.php">Books</a><span class="sep">/</span>
      <a href="books.php?category=<?= urlencode($book['category']) ?>"><?= htmlspecialchars($book['category']) ?></a><span class="sep">/</span>
      <span><?= htmlspecialchars($book['title']) ?></span>
    </div>
  </div>
</div>

<!-- Book Details -->
<section class="section">
  <div class="container">

    <?php if ($cartMessage): ?>
      <div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> <?= $cartMessage ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:320px 1fr;gap:3rem;align-items:start;">

      <!-- Cover -->
      <div>
        <div style="border-radius:var(--radius-md);overflow:hidden;box-shadow:var(--shadow-lg);position:relative;">
          <img src="assets/images/books/<?= htmlspecialchars($book['cover_image']) ?>"
               alt="<?= htmlspecialchars($book['title']) ?>"
               style="width:100%;aspect-ratio:2/3;object-fit:cover;"
               onerror="this.src='assets/images/default_book.jpg'">
          <?php if ($discount > 0): ?>
            <div class="book-discount" style="font-size:1rem;padding:0.35rem 0.75rem;">-<?= $discount ?>%</div>
          <?php endif; ?>
        </div>

        <!-- Book meta info -->
        <div style="background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);padding:1.25rem;margin-top:1.5rem;">
          <?php
          $meta = [
            ['icon'=>'fa-barcode',         'label'=>'ISBN',      'val'=>$book['isbn'] ?? 'N/A'],
            ['icon'=>'fa-building',        'label'=>'Publisher', 'val'=>$book['publisher'] ?? 'N/A'],
            ['icon'=>'fa-calendar-alt',    'label'=>'Year',      'val'=>$book['publish_year'] ?? 'N/A'],
            ['icon'=>'fa-file-alt',        'label'=>'Pages',     'val'=>$book['pages'] ?? 'N/A'],
            ['icon'=>'fa-globe',           'label'=>'Language',  'val'=>$book['language'] ?? 'English'],
            ['icon'=>'fa-boxes',           'label'=>'In Stock',  'val'=>$book['stock'] > 0 ? $book['stock'] . ' copies' : 'Out of stock'],
          ];
          foreach ($meta as $m): ?>
          <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.05);font-size:0.85rem;">
            <span style="color:var(--gray);display:flex;align-items:center;gap:0.5rem;">
              <i class="fas <?= $m['icon'] ?>" style="color:var(--gold);width:16px;"></i>
              <?= $m['label'] ?>
            </span>
            <span style="color:var(--off-white);font-weight:500;"><?= htmlspecialchars($m['val']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Details -->
      <div>
        <!-- Badges -->
        <div style="display:flex;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap;">
          <?php if ($book['is_featured']):   ?><span class="badge-tag badge-featured">Featured</span><?php endif; ?>
          <?php if ($book['is_bestseller']): ?><span class="badge-tag badge-bestseller">Bestseller</span><?php endif; ?>
          <?php if ($book['is_new_arrival']): ?><span class="badge-tag badge-new">New Arrival</span><?php endif; ?>
        </div>

        <span style="font-size:0.8rem;color:var(--gold);text-transform:uppercase;letter-spacing:2px;font-weight:600;">
          <?= htmlspecialchars($book['category']) ?>
        </span>

        <h1 style="font-family:var(--font-heading);font-size:clamp(1.6rem,3vw,2.5rem);margin:0.5rem 0;">
          <?= htmlspecialchars($book['title']) ?>
        </h1>

        <p style="color:var(--gray-light);font-size:1rem;margin-bottom:1rem;">
          by <strong style="color:var(--blue-bright);"><?= htmlspecialchars($book['author']) ?></strong>
        </p>

        <!-- Rating -->
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
          <div class="stars" style="font-size:1rem;">
            <?php $r = floatval($book['rating']); for ($i=1;$i<=5;$i++): ?>
              <?php if ($i<=floor($r)): ?><i class="fas fa-star" style="color:var(--gold)"></i>
              <?php elseif ($i-0.5<=$r): ?><i class="fas fa-star-half-alt" style="color:var(--gold)"></i>
              <?php else: ?><i class="far fa-star" style="color:var(--gold)"></i>
              <?php endif; ?>
            <?php endfor; ?>
          </div>
          <span style="font-size:1rem;font-weight:700;color:var(--gold);"><?= $book['rating'] ?></span>
          <span style="color:var(--gray);font-size:0.85rem;">(<?= number_format($book['total_reviews']) ?> reviews)</span>
        </div>

        <!-- Price -->
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem;">
          <span style="font-family:var(--font-heading);font-size:2.2rem;font-weight:700;color:var(--gold);">
            ₹<?= number_format($book['price'], 2) ?>
          </span>
          <?php if ($book['original_price'] > $book['price']): ?>
            <span style="font-size:1.1rem;color:var(--gray);text-decoration:line-through;">
              ₹<?= number_format($book['original_price'], 2) ?>
            </span>
            <span style="background:var(--danger);color:var(--white);padding:0.3rem 0.75rem;border-radius:6px;font-size:0.85rem;font-weight:700;">
              Save <?= $discount ?>%
            </span>
          <?php endif; ?>
        </div>

        <!-- Description -->
        <div style="background:var(--dark-3);border-left:3px solid var(--gold);padding:1.25rem;border-radius:0 var(--radius-sm) var(--radius-sm) 0;margin-bottom:2rem;">
          <h3 style="font-size:1rem;margin-bottom:0.5rem;color:var(--gold);">Description</h3>
          <p style="color:var(--gray-light);line-height:1.8;font-size:0.95rem;">
            <?= nl2br(htmlspecialchars($book['description'])) ?>
          </p>
        </div>

        <!-- Add to Cart Form -->
        <?php if ($book['stock'] > 0): ?>
          <form method="POST" style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:0.5rem;">
              <label style="font-size:0.85rem;color:var(--gray-light);">Quantity:</label>
              <div class="qty-control">
                <button type="button" class="qty-btn minus" onclick="changeQty(-1)">−</button>
                <input type="number" name="quantity" id="qtyInput" class="qty-val" value="1" min="1" max="<?= $book['stock'] ?>">
                <button type="button" class="qty-btn plus" onclick="changeQty(1)">+</button>
              </div>
            </div>
            <button type="submit" name="add_to_cart" class="btn btn-gold btn-lg">
              <i class="fas fa-shopping-cart"></i> Add to Cart
            </button>
            <a href="cart.php" class="btn btn-outline btn-lg">
              <i class="fas fa-bolt"></i> Buy Now
            </a>
          </form>
        <?php else: ?>
          <div class="alert alert-warning" style="display:inline-flex;">
            <i class="fas fa-exclamation-triangle"></i> Currently out of stock
          </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- Related Books -->
    <?php if (!empty($related)): ?>
    <div style="margin-top:4rem;">
      <div class="section-header">
        <p class="section-subtitle">You May Also Like</p>
        <h2 class="section-title">Related Books</h2>
      </div>
      <div class="books-grid">
        <?php foreach (array_slice($related, 0, 4) as $book): ?>
          <?php include 'includes/book_card.php'; ?>
        <?php endforeach; ?>
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
<script>
function changeQty(delta) {
  const input = document.getElementById('qtyInput');
  const max   = parseInt(input.max);
  let val = parseInt(input.value) + delta;
  if (val < 1)   val = 1;
  if (val > max) val = max;
  input.value = val;
}
</script>
</body>
</html>

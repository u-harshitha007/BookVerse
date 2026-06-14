<?php
/**
 * BookVerse - Book Catalog Page
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$cartCount  = isLoggedIn() ? getCartCount($_SESSION['user_id']) : 0;
$categories = getCategories();

// Build filter from GET params
$filter = [];
if (!empty($_GET['category'])) $filter['category'] = sanitize($_GET['category']);
if (!empty($_GET['search']))   $filter['search']   = sanitize($_GET['search']);

$books = getBooks($filter);
$pageTitle = !empty($filter['category']) ? $filter['category'] . ' Books' : 'All Books';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <title><?= $pageTitle ?> – BookVerse</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar" id="navbar">
  <div class="nav-container">
    <a href="index.php" class="nav-logo">
      <div class="logo-icon"><i class="fas fa-book-open"></i></div>
      <div>
        <span class="logo-text">BookVerse</span>
        <span class="logo-tag">Discover. Read. Collect.</span>
      </div>
    </a>
    <ul class="nav-menu" id="navMenu">
      <li><a href="index.php"    class="nav-link">Home</a></li>
      <li><a href="books.php"    class="nav-link active">Books</a></li>
      <li><a href="index.php#about"   class="nav-link">About</a></li>
      <li><a href="index.php#contact" class="nav-link">Contact</a></li>
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
        <a href="login.php"    class="btn btn-outline btn-sm">Login</a>
        <a href="register.php" class="btn btn-gold btn-sm">Sign Up</a>
      <?php endif; ?>
      <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
    </div>
  </div>
</nav>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1><?= htmlspecialchars($pageTitle) ?></h1>
    <div class="breadcrumb">
      <a href="index.php">Home</a>
      <span class="sep">/</span>
      <span>Books</span>
      <?php if (!empty($filter['category'])): ?>
        <span class="sep">/</span>
        <span><?= htmlspecialchars($filter['category']) ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Books Section -->
<section class="section">
  <div class="container">

    <!-- Filter & Search Bar -->
    <div class="filter-bar">
      <div class="search-input-wrap">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" class="search-input"
               placeholder="Search books, authors, genres..."
               value="<?= htmlspecialchars($filter['search'] ?? '') ?>">
      </div>

      <select id="categoryFilter" class="filter-select">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>"
            <?= (isset($filter['category']) && $filter['category'] === $cat) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select id="sortFilter" class="filter-select">
        <option value="">Sort By</option>
        <option value="title">Title A–Z</option>
        <option value="price-asc">Price: Low to High</option>
        <option value="price-desc">Price: High to Low</option>
        <option value="rating">Top Rated</option>
      </select>

      <span style="color:var(--gray);font-size:0.85rem;white-space:nowrap;">
        <strong style="color:var(--gold)"><?= count($books) ?></strong> books found
      </span>
    </div>

    <!-- Category Pills -->
    <div style="display:flex;gap:0.6rem;flex-wrap:wrap;margin-bottom:2rem;">
      <a href="books.php"
         class="btn btn-sm <?= empty($filter['category']) ? 'btn-gold' : 'btn-outline' ?>">
        All
      </a>
      <?php foreach ($categories as $cat): ?>
        <a href="books.php?category=<?= urlencode($cat) ?>"
           class="btn btn-sm <?= (isset($filter['category']) && $filter['category'] === $cat) ? 'btn-gold' : 'btn-outline' ?>">
          <?= htmlspecialchars($cat) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Books Grid -->
    <div class="books-grid" id="booksGrid">
      <?php if (!empty($books)): ?>
        <?php foreach ($books as $book): ?>
          <?php include 'includes/book_card.php'; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state" style="grid-column:1/-1;">
          <i class="fas fa-book"></i>
          <h3>No books found</h3>
          <p>Try adjusting your search or browse all categories</p>
          <a href="books.php" class="btn btn-gold mt-2">Browse All</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer-bottom">
      <p class="footer-copy">&copy; <?= date('Y') ?> BookVerse. All rights reserved.</p>
      <div class="footer-legal">
        <a href="index.php">Home</a>
        <a href="#">Privacy Policy</a>
      </div>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>

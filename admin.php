<?php
/**
 * BookVerse - Admin Panel
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdmin();

global $conn;

$tab     = sanitize($_GET['tab'] ?? 'dashboard');
$success = '';
$error   = '';

/* ─────────────────────────────────────────────
   Handle POST actions
──────────────────────────────────────────────*/

// Add Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title        = sanitize($_POST['title']        ?? '');
    $author       = sanitize($_POST['author']       ?? '');
    $category     = sanitize($_POST['category']     ?? '');
    $description  = sanitize($_POST['description']  ?? '');
    $price        = floatval($_POST['price']        ?? 0);
    $orig_price   = floatval($_POST['original_price'] ?? 0);
    $stock        = intval($_POST['stock']          ?? 0);
    $rating       = floatval($_POST['rating']       ?? 0);
    $isbn         = sanitize($_POST['isbn']         ?? '');
    $publisher    = sanitize($_POST['publisher']    ?? '');
    $pub_year     = intval($_POST['publish_year']   ?? 0);
    $pages        = intval($_POST['pages']          ?? 0);
    $lang         = sanitize($_POST['language']     ?? 'English');
    $featured     = intval($_POST['is_featured']    ?? 0);
    $bestseller   = intval($_POST['is_bestseller']  ?? 0);
    $new_arr      = intval($_POST['is_new_arrival'] ?? 0);

    if (empty($title) || empty($author) || empty($category) || $price <= 0) {
        $error = 'Title, Author, Category and Price are required.';
    } else {
        // Handle cover image
        $cover = 'default_book.jpg';
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $cover = 'book_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['cover_image']['tmp_name'], __DIR__ . '/assets/images/books/' . $cover);
            }
        }

        $stmt = $conn->prepare("INSERT INTO books (title, author, category, description, price, original_price, stock, cover_image, rating, isbn, publisher, publish_year, pages, language, is_featured, is_bestseller, is_new_arrival) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssddisdssiisiii", $title, $author, $category, $description, $price, $orig_price, $stock, $cover, $rating, $isbn, $publisher, $pub_year, $pages, $lang, $featured, $bestseller, $new_arr);
        $stmt->execute();
        $stmt->close();
        $success = 'Book added successfully!';
        $tab = 'books';
    }
}

// Update Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $bookId       = intval($_POST['book_id']      ?? 0);
    $title        = sanitize($_POST['title']       ?? '');
    $author       = sanitize($_POST['author']      ?? '');
    $category     = sanitize($_POST['category']    ?? '');
    $description  = sanitize($_POST['description'] ?? '');
    $price        = floatval($_POST['price']       ?? 0);
    $orig_price   = floatval($_POST['original_price'] ?? 0);
    $stock        = intval($_POST['stock']         ?? 0);
    $rating       = floatval($_POST['rating']      ?? 0);
    $isbn         = sanitize($_POST['isbn']        ?? '');
    $publisher    = sanitize($_POST['publisher']   ?? '');
    $pub_year     = intval($_POST['publish_year']  ?? 0);
    $pages        = intval($_POST['pages']         ?? 0);
    $lang         = sanitize($_POST['language']    ?? 'English');
    $featured     = intval($_POST['is_featured']   ?? 0);
    $bestseller   = intval($_POST['is_bestseller'] ?? 0);
    $new_arr      = intval($_POST['is_new_arrival'] ?? 0);

    // Handle new cover image
    $coverUpdate = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $cover = 'book_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], __DIR__ . '/assets/images/books/' . $cover);
            $coverUpdate = ", cover_image = '$cover'";
        }
    }

    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, description=?, price=?, original_price=?, stock=?, rating=?, isbn=?, publisher=?, publish_year=?, pages=?, language=?, is_featured=?, is_bestseller=?, is_new_arrival=? $coverUpdate WHERE id=?");
    $stmt->bind_param("ssssddidssiisiiiii", $title, $author, $category, $description, $price, $orig_price, $stock, $rating, $isbn, $publisher, $pub_year, $pages, $lang, $featured, $bestseller, $new_arr, $bookId);
    $stmt->execute();
    $stmt->close();
    $success = 'Book updated successfully!';
    $tab = 'books';
}

// Delete Book
if (isset($_GET['delete_book'])) {
    $bookId = intval($_GET['delete_book']);
    $stmt   = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $stmt->close();
    $success = 'Book deleted.';
    $tab = 'books';
}

// Update Order Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = intval($_POST['order_id']   ?? 0);
    $status  = sanitize($_POST['status']   ?? 'pending');
    $stmt    = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);
    $stmt->execute();
    $stmt->close();
    $success = 'Order status updated!';
    $tab = 'orders';
}

/* ─────────────────────────────────────────────
   Fetch data
──────────────────────────────────────────────*/
$allBooks  = getBooks();
$allOrders = getOrders();

// Stats
$totalBooks    = count($allBooks);
$totalOrders   = count($allOrders);
$totalRevenue  = array_sum(array_column($allOrders, 'total_amount'));
$totalUsers    = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role='customer'")->fetch_assoc()['cnt'];
$pendingOrders = count(array_filter($allOrders, fn($o) => $o['status'] === 'pending'));
$lowStockBooks = array_filter($allBooks, fn($b) => $b['stock'] < 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <title>Admin Panel – BookVerse</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar" id="navbar">
  <div class="nav-container">
    <a href="index.php" class="nav-logo">
      <div class="logo-icon"><i class="fas fa-book-open"></i></div>
      <div><span class="logo-text">BookVerse</span><span class="logo-tag">Admin Panel</span></div>
    </a>
    <div class="nav-actions">
      <a href="index.php" class="btn btn-outline btn-sm"><i class="fas fa-home"></i> View Site</a>
      <a href="logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</nav>

<div style="padding-top:80px;">
<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-title">Navigation</div>
    <a href="?tab=dashboard" class="admin-nav-link <?= $tab==='dashboard'?'active':'' ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="?tab=books" class="admin-nav-link <?= $tab==='books'?'active':'' ?>">
      <i class="fas fa-book"></i> Manage Books
    </a>
    <a href="?tab=add_book" class="admin-nav-link <?= $tab==='add_book'?'active':'' ?>">
      <i class="fas fa-plus-circle"></i> Add Book
    </a>
    <a href="?tab=orders" class="admin-nav-link <?= $tab==='orders'?'active':'' ?>">
      <i class="fas fa-receipt"></i> Orders
      <?php if ($pendingOrders > 0): ?>
        <span style="background:var(--gold);color:var(--dark);font-size:0.7rem;padding:0.1rem 0.5rem;border-radius:50px;margin-left:auto;"><?= $pendingOrders ?></span>
      <?php endif; ?>
    </a>
    <a href="?tab=users" class="admin-nav-link <?= $tab==='users'?'active':'' ?>">
      <i class="fas fa-users"></i> Users
    </a>
    <a href="?tab=inventory" class="admin-nav-link <?= $tab==='inventory'?'active':'' ?>">
      <i class="fas fa-boxes"></i> Inventory
    </a>
    <div class="admin-sidebar-title" style="margin-top:1.5rem;">Quick Links</div>
    <a href="index.php" class="admin-nav-link"><i class="fas fa-globe"></i> View Site</a>
    <a href="logout.php" class="admin-nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </aside>

  <!-- Main Content -->
  <main class="admin-content">

    <?php if ($success): ?>
      <div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <!-- ───── DASHBOARD ───── -->
    <?php if ($tab === 'dashboard'): ?>

      <h2 style="font-family:var(--font-heading);margin-bottom:2rem;">
        Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>
        <span style="color:var(--gold);">👑</span>
      </h2>

      <div class="admin-stats">
        <div class="stat-card">
          <div class="stat-info">
            <span class="stat-val"><?= $totalBooks ?></span>
            <span class="stat-lbl">Total Books</span>
          </div>
          <div class="stat-icon-box gold"><i class="fas fa-book"></i></div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <span class="stat-val"><?= $totalOrders ?></span>
            <span class="stat-lbl">Total Orders</span>
          </div>
          <div class="stat-icon-box blue"><i class="fas fa-receipt"></i></div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <span class="stat-val">₹<?= number_format($totalRevenue, 0) ?></span>
            <span class="stat-lbl">Revenue</span>
          </div>
          <div class="stat-icon-box green"><i class="fas fa-rupee-sign"></i></div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <span class="stat-val"><?= $totalUsers ?></span>
            <span class="stat-lbl">Customers</span>
          </div>
          <div class="stat-icon-box blue"><i class="fas fa-users"></i></div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <span class="stat-val"><?= $pendingOrders ?></span>
            <span class="stat-lbl">Pending Orders</span>
          </div>
          <div class="stat-icon-box red"><i class="fas fa-clock"></i></div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <span class="stat-val"><?= count($lowStockBooks) ?></span>
            <span class="stat-lbl">Low Stock</span>
          </div>
          <div class="stat-icon-box red"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div style="background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);padding:1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
          <h3 style="font-family:var(--font-heading);">Recent Orders</h3>
          <a href="?tab=orders" style="color:var(--gold);font-size:0.85rem;">View All</a>
        </div>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead><tr>
              <th>Order ID</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th>
            </tr></thead>
            <tbody>
              <?php foreach (array_slice($allOrders, 0, 5) as $ord): ?>
              <tr>
                <td style="color:var(--gold);font-weight:600;"><?= htmlspecialchars($ord['order_id']) ?></td>
                <td><?= htmlspecialchars($ord['user_name'] ?? $ord['customer_name']) ?></td>
                <td>₹<?= number_format($ord['total_amount'], 2) ?></td>
                <td><span class="status-badge status-<?= $ord['status'] ?>"><?= ucfirst($ord['status']) ?></span></td>
                <td><?= date('d M Y', strtotime($ord['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    <!-- ───── BOOKS ───── -->
    <?php elseif ($tab === 'books'): ?>

      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h2 style="font-family:var(--font-heading);">Manage Books</h2>
        <a href="?tab=add_book" class="btn btn-gold btn-sm">
          <i class="fas fa-plus"></i> Add Book
        </a>
      </div>

      <div style="overflow-x:auto;background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);">
        <table class="data-table">
          <thead><tr>
            <th>#</th><th>Cover</th><th>Title</th><th>Author</th><th>Category</th>
            <th>Price</th><th>Stock</th><th>Rating</th><th>Actions</th>
          </tr></thead>
          <tbody>
            <?php foreach ($allBooks as $b): ?>
            <tr>
              <td><?= $b['id'] ?></td>
              <td>
                <img src="assets/images/books/<?= htmlspecialchars($b['cover_image']) ?>"
                     style="width:40px;height:55px;object-fit:cover;border-radius:4px;"
                     onerror="this.src='assets/images/default_book.jpg'">
              </td>
              <td style="max-width:180px;">
                <div style="font-weight:600;color:var(--off-white);"><?= htmlspecialchars($b['title']) ?></div>
              </td>
              <td><?= htmlspecialchars($b['author']) ?></td>
              <td><span style="background:rgba(212,175,55,0.1);color:var(--gold);padding:0.2rem 0.6rem;border-radius:4px;font-size:0.75rem;"><?= htmlspecialchars($b['category']) ?></span></td>
              <td style="color:var(--gold);">₹<?= number_format($b['price'], 2) ?></td>
              <td>
                <span style="color:<?= $b['stock'] < 5 ? 'var(--danger)' : 'var(--success)' ?>;">
                  <?= $b['stock'] ?>
                </span>
              </td>
              <td><?= $b['rating'] ?> ⭐</td>
              <td>
                <div style="display:flex;gap:0.5rem;">
                  <button onclick="openEditModal(<?= htmlspecialchars(json_encode($b)) ?>)"
                          class="btn btn-sm btn-blue" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <a href="?delete_book=<?= $b['id'] ?>&tab=books"
                     onclick="return confirm('Delete this book?')"
                     class="btn btn-sm btn-danger" title="Delete">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <!-- ───── ADD BOOK ───── -->
    <?php elseif ($tab === 'add_book'): ?>

      <h2 style="font-family:var(--font-heading);margin-bottom:1.5rem;">Add New Book</h2>

      <div style="background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);padding:1.75rem;">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="add_book" value="1">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
              <label class="form-label">Title *</label>
              <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Author *</label>
              <input type="text" name="author" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Category *</label>
              <select name="category" class="form-control" required>
                <option value="">Select Category</option>
                <?php foreach (['Programming','Data Science','Fiction','Business','Self Help','Cybersecurity'] as $c): ?>
                  <option value="<?= $c ?>"><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Price (₹) *</label>
              <input type="number" name="price" class="form-control" min="0" step="0.01" required>
            </div>
            <div class="form-group">
              <label class="form-label">Original Price (₹)</label>
              <input type="number" name="original_price" class="form-control" min="0" step="0.01">
            </div>
            <div class="form-group">
              <label class="form-label">Stock</label>
              <input type="number" name="stock" class="form-control" min="0" value="0">
            </div>
            <div class="form-group">
              <label class="form-label">Rating (0-5)</label>
              <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1" value="0">
            </div>
            <div class="form-group">
              <label class="form-label">ISBN</label>
              <input type="text" name="isbn" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-label">Publisher</label>
              <input type="text" name="publisher" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-label">Publish Year</label>
              <input type="number" name="publish_year" class="form-control" min="1800" max="2030">
            </div>
            <div class="form-group">
              <label class="form-label">Pages</label>
              <input type="number" name="pages" class="form-control" min="0">
            </div>
            <div class="form-group">
              <label class="form-label">Language</label>
              <input type="text" name="language" class="form-control" value="English">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
              <label class="form-label">Cover Image</label>
              <input type="file" name="cover_image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
              <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                <input type="checkbox" name="is_featured" value="1" style="accent-color:var(--gold);"> Featured Book
              </label>
            </div>
            <div class="form-group">
              <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                <input type="checkbox" name="is_bestseller" value="1" style="accent-color:var(--gold);"> Bestseller
              </label>
            </div>
            <div class="form-group">
              <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                <input type="checkbox" name="is_new_arrival" value="1" style="accent-color:var(--gold);"> New Arrival
              </label>
            </div>
          </div>
          <div style="display:flex;gap:1rem;margin-top:0.5rem;">
            <button type="submit" class="btn btn-gold"><i class="fas fa-plus"></i> Add Book</button>
            <a href="?tab=books" class="btn btn-outline">Cancel</a>
          </div>
        </form>
      </div>

    <!-- ───── ORDERS ───── -->
    <?php elseif ($tab === 'orders'): ?>

      <h2 style="font-family:var(--font-heading);margin-bottom:1.5rem;">All Orders</h2>

      <div style="overflow-x:auto;background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);">
        <table class="data-table">
          <thead><tr>
            <th>Order ID</th><th>Customer</th><th>Email</th>
            <th>Amount</th><th>Payment</th><th>Status</th><th>Date</th><th>Actions</th>
          </tr></thead>
          <tbody>
            <?php foreach ($allOrders as $ord): ?>
            <tr>
              <td style="color:var(--gold);font-weight:600;"><?= htmlspecialchars($ord['order_id']) ?></td>
              <td><?= htmlspecialchars($ord['user_name'] ?? $ord['customer_name']) ?></td>
              <td style="font-size:0.82rem;"><?= htmlspecialchars($ord['customer_email']) ?></td>
              <td>₹<?= number_format($ord['total_amount'], 2) ?></td>
              <td><?= ucwords(str_replace('_', ' ', $ord['payment_method'])) ?></td>
              <td><span class="status-badge status-<?= $ord['status'] ?>"><?= ucfirst($ord['status']) ?></span></td>
              <td><?= date('d M Y', strtotime($ord['created_at'])) ?></td>
              <td>
                <form method="POST" style="display:flex;gap:0.4rem;">
                  <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                  <select name="status" class="filter-select" style="padding:0.35rem 0.6rem;font-size:0.78rem;">
                    <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $st): ?>
                      <option value="<?= $st ?>" <?= $ord['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" name="update_order_status" class="btn btn-sm btn-blue">
                    <i class="fas fa-check"></i>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <!-- ───── USERS ───── -->
    <?php elseif ($tab === 'users'): ?>

      <h2 style="font-family:var(--font-heading);margin-bottom:1.5rem;">All Users</h2>

      <?php $allUsers = $conn->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC"); ?>

      <div style="overflow-x:auto;background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);">
        <table class="data-table">
          <thead><tr>
            <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th>
          </tr></thead>
          <tbody>
            <?php while ($u = $allUsers->fetch_assoc()): ?>
            <tr>
              <td><?= $u['id'] ?></td>
              <td style="font-weight:600;color:var(--off-white);"><?= htmlspecialchars($u['name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
              <td>
                <span style="background:<?= $u['role']==='admin' ? 'rgba(212,175,55,0.15)' : 'rgba(74,144,217,0.15)' ?>;
                             color:<?= $u['role']==='admin' ? 'var(--gold)' : 'var(--blue-bright)' ?>;
                             padding:0.2rem 0.6rem;border-radius:4px;font-size:0.75rem;font-weight:600;">
                  <?= ucfirst($u['role']) ?>
                </span>
              </td>
              <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    <!-- ───── INVENTORY ───── -->
    <?php elseif ($tab === 'inventory'): ?>

      <h2 style="font-family:var(--font-heading);margin-bottom:1.5rem;">Inventory Management</h2>

      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:2rem;">
        <?php
        $invStats = [
          ['label'=>'Total Books',     'val'=>$totalBooks,              'color'=>'gold'],
          ['label'=>'Low Stock (<5)',   'val'=>count($lowStockBooks),    'color'=>'red'],
          ['label'=>'Out of Stock',     'val'=>count(array_filter($allBooks, fn($b) => $b['stock']===0)), 'color'=>'red'],
          ['label'=>'Total Stock',      'val'=>array_sum(array_column($allBooks, 'stock')), 'color'=>'green'],
        ];
        foreach ($invStats as $s): ?>
        <div class="stat-card">
          <div class="stat-info">
            <span class="stat-val" style="color:var(--<?= $s['color'] ?>);"><?= $s['val'] ?></span>
            <span class="stat-lbl"><?= $s['label'] ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if (!empty($lowStockBooks)): ?>
      <div style="background:rgba(220,53,69,0.08);border:1px solid rgba(220,53,69,0.2);border-radius:var(--radius-md);padding:1.25rem;margin-bottom:1.5rem;">
        <h4 style="color:var(--danger);margin-bottom:0.75rem;"><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h4>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead><tr><th>Book</th><th>Category</th><th>Price</th><th>Stock</th></tr></thead>
            <tbody>
              <?php foreach ($lowStockBooks as $b): ?>
              <tr>
                <td><?= htmlspecialchars($b['title']) ?></td>
                <td><?= htmlspecialchars($b['category']) ?></td>
                <td>₹<?= number_format($b['price'], 2) ?></td>
                <td style="color:var(--danger);font-weight:700;"><?= $b['stock'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <div style="overflow-x:auto;background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);">
        <table class="data-table">
          <thead><tr>
            <th>Title</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th>
          </tr></thead>
          <tbody>
            <?php foreach ($allBooks as $b): ?>
            <tr>
              <td style="font-weight:500;"><?= htmlspecialchars($b['title']) ?></td>
              <td><?= htmlspecialchars($b['category']) ?></td>
              <td>₹<?= number_format($b['price'], 2) ?></td>
              <td style="font-weight:600;color:<?= $b['stock'] < 5 ? 'var(--danger)' : 'var(--success)' ?>;"><?= $b['stock'] ?></td>
              <td>
                <?php if ($b['stock'] == 0): ?>
                  <span class="status-badge" style="background:rgba(220,53,69,0.15);color:#f07080;">Out of Stock</span>
                <?php elseif ($b['stock'] < 5): ?>
                  <span class="status-badge" style="background:rgba(255,193,7,0.15);color:#ffd545;">Low Stock</span>
                <?php else: ?>
                  <span class="status-badge" style="background:rgba(40,167,69,0.15);color:#5dd679;">In Stock</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php endif; ?>

  </main>
</div>
</div>

<!-- Edit Book Modal -->
<div id="editBookModal" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Edit Book</h3>
      <button class="modal-close" onclick="closeModal('editBookModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data" id="editBookForm">
        <input type="hidden" name="update_book" value="1">
        <input type="hidden" name="book_id" id="editBookId">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
          <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" id="editTitle" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Author</label>
            <input type="text" name="author" id="editAuthor" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Category</label>
            <select name="category" id="editCategory" class="form-control">
              <?php foreach (['Programming','Data Science','Fiction','Business','Self Help','Cybersecurity'] as $c): ?>
                <option value="<?= $c ?>"><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Price (₹)</label>
            <input type="number" name="price" id="editPrice" class="form-control" step="0.01">
          </div>
          <div class="form-group">
            <label class="form-label">Original Price</label>
            <input type="number" name="original_price" id="editOrigPrice" class="form-control" step="0.01">
          </div>
          <div class="form-group">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" id="editStock" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Rating</label>
            <input type="number" name="rating" id="editRating" class="form-control" step="0.1" min="0" max="5">
          </div>
          <div class="form-group">
            <label class="form-label">ISBN</label>
            <input type="text" name="isbn" id="editIsbn" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Publisher</label>
            <input type="text" name="publisher" id="editPublisher" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Year</label>
            <input type="number" name="publish_year" id="editYear" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Pages</label>
            <input type="number" name="pages" id="editPages" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Language</label>
            <input type="text" name="language" id="editLang" class="form-control">
          </div>
          <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label">Description</label>
            <textarea name="description" id="editDesc" class="form-control" rows="3"></textarea>
          </div>
          <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label">New Cover Image (optional)</label>
            <input type="file" name="cover_image" class="form-control" accept="image/*">
          </div>
          <div class="form-group">
            <label><input type="checkbox" name="is_featured" id="editFeatured" value="1"> Featured</label>
          </div>
          <div class="form-group">
            <label><input type="checkbox" name="is_bestseller" id="editBestseller" value="1"> Bestseller</label>
          </div>
          <div class="form-group">
            <label><input type="checkbox" name="is_new_arrival" id="editNewArrival" value="1"> New Arrival</label>
          </div>
        </div>
        <div style="display:flex;gap:1rem;margin-top:0.75rem;">
          <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Changes</button>
          <button type="button" class="btn btn-outline" onclick="closeModal('editBookModal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/main.js"></script>
<script>
function openEditModal(book) {
  document.getElementById('editBookId').value      = book.id;
  document.getElementById('editTitle').value       = book.title;
  document.getElementById('editAuthor').value      = book.author;
  document.getElementById('editCategory').value    = book.category;
  document.getElementById('editPrice').value       = book.price;
  document.getElementById('editOrigPrice').value   = book.original_price;
  document.getElementById('editStock').value       = book.stock;
  document.getElementById('editRating').value      = book.rating;
  document.getElementById('editIsbn').value        = book.isbn || '';
  document.getElementById('editPublisher').value   = book.publisher || '';
  document.getElementById('editYear').value        = book.publish_year || '';
  document.getElementById('editPages').value       = book.pages || '';
  document.getElementById('editLang').value        = book.language || 'English';
  document.getElementById('editDesc').value        = book.description || '';
  document.getElementById('editFeatured').checked    = book.is_featured == 1;
  document.getElementById('editBestseller').checked  = book.is_bestseller == 1;
  document.getElementById('editNewArrival').checked  = book.is_new_arrival == 1;
  openModal('editBookModal');
}
</script>
</body>
</html>

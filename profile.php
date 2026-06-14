<?php
/**
 * BookVerse - Profile Page
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$user      = getCurrentUser();
$cartCount = getCartCount($_SESSION['user_id']);
$orders    = getOrders($_SESSION['user_id']);

$success = '';
$error   = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    global $conn;

    $name    = sanitize($_POST['name']    ?? '');
    $phone   = sanitize($_POST['phone']   ?? '');
    $address = sanitize($_POST['address'] ?? '');

    if (empty($name)) {
        $error = 'Name cannot be empty.';
    } else {
        // Handle profile picture upload
        $pictureName = $user['profile_picture'];
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $newName = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], UPLOAD_PATH . $newName)) {
                    $pictureName = $newName;
                    $_SESSION['user_picture'] = $newName;
                }
            } else {
                $error = 'Invalid image format. Use JPG, PNG, or GIF.';
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ?, profile_picture = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $phone, $address, $pictureName, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            $_SESSION['user_name'] = $name;
            $user = getCurrentUser(); // Refresh
            $success = 'Profile updated successfully!';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    global $conn;

    $currentPwd  = $_POST['current_password']  ?? '';
    $newPwd      = $_POST['new_password']       ?? '';
    $confirmPwd  = $_POST['confirm_new_password'] ?? '';

    if (empty($currentPwd) || empty($newPwd) || empty($confirmPwd)) {
        $error = 'Please fill in all password fields.';
    } elseif ($newPwd !== $confirmPwd) {
        $error = 'New passwords do not match.';
    } elseif (strlen($newPwd) < 6) {
        $error = 'New password must be at least 6 characters.';
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!password_verify($currentPwd, $row['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            $hashed = password_hash($newPwd, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $success = 'Password changed successfully!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <title>My Profile – BookVerse</title>
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
      <li><a href="orders.php" class="nav-link">Orders</a></li>
      <li><a href="profile.php" class="nav-link active">Profile</a></li>
      <?php if (isAdmin()): ?><li><a href="admin.php" class="nav-link text-gold">Admin</a></li><?php endif; ?>
    </ul>
    <div class="nav-actions">
      <a href="cart.php" class="btn btn-outline btn-sm cart-badge">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cartCount > 0): ?><span class="badge"><?= $cartCount ?></span><?php endif; ?>
      </a>
      <a href="logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
      <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
    </div>
  </div>
</nav>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1>My Profile</h1>
    <div class="breadcrumb">
      <a href="index.php">Home</a><span class="sep">/</span><span>Profile</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="profile-layout">

      <!-- Sidebar -->
      <div class="profile-card">
        <div class="profile-avatar-wrap">
          <img src="uploads/<?= htmlspecialchars($user['profile_picture'] ?? 'default.png') ?>"
               class="profile-avatar" id="profileAvatarPreview"
               onerror="this.src='assets/images/default_avatar.png'"
               alt="Profile Picture">
          <label for="avatarInput" class="avatar-edit" title="Change photo">
            <i class="fas fa-camera"></i>
          </label>
        </div>
        <h3 class="profile-name"><?= htmlspecialchars($user['name']) ?></h3>
        <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
        <span class="profile-role"><?= ucfirst($user['role']) ?></span>

        <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,0.06);">
          <div style="display:flex;justify-content:space-around;">
            <div style="text-align:center;">
              <div style="font-family:var(--font-heading);font-size:1.5rem;color:var(--gold);font-weight:700;"><?= count($orders) ?></div>
              <div style="font-size:0.75rem;color:var(--gray);">Orders</div>
            </div>
            <div style="text-align:center;">
              <div style="font-family:var(--font-heading);font-size:1.5rem;color:var(--gold);font-weight:700;"><?= $cartCount ?></div>
              <div style="font-size:0.75rem;color:var(--gray);">In Cart</div>
            </div>
          </div>
        </div>

        <div style="margin-top:1.5rem;text-align:left;font-size:0.82rem;color:var(--gray);">
          <div style="margin-bottom:0.5rem;"><i class="fas fa-calendar-alt" style="color:var(--gold);margin-right:0.5rem;"></i> Member since <?= date('M Y', strtotime($user['created_at'])) ?></div>
          <?php if ($user['phone']): ?>
          <div><i class="fas fa-phone" style="color:var(--gold);margin-right:0.5rem;"></i> <?= htmlspecialchars($user['phone']) ?></div>
          <?php endif; ?>
        </div>

        <div style="margin-top:1.5rem;display:grid;gap:0.5rem;">
          <a href="orders.php" class="btn btn-outline btn-sm btn-block">
            <i class="fas fa-receipt"></i> My Orders
          </a>
          <a href="books.php" class="btn btn-gold btn-sm btn-block">
            <i class="fas fa-book"></i> Browse Books
          </a>
        </div>
      </div>

      <!-- Main Content -->
      <div>
        <?php if ($success): ?>
          <div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <!-- Edit Profile -->
        <div style="background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);padding:1.75rem;margin-bottom:1.5rem;">
          <h3 style="font-family:var(--font-heading);margin-bottom:1.5rem;display:flex;align-items:center;gap:0.6rem;">
            <i class="fas fa-user-edit" style="color:var(--gold);"></i> Edit Profile
          </h3>
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_profile" value="1">
            <input type="file" id="avatarInput" name="profile_picture" accept="image/*" hidden>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($user['name']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control"
                       value="<?= htmlspecialchars($user['email']) ?>" disabled
                       style="opacity:0.6;cursor:not-allowed;">
                <span style="font-size:0.75rem;color:var(--gray);">Email cannot be changed</span>
              </div>
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control"
                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                       placeholder="+91 98765 43210">
              </div>
              <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="3"
                          placeholder="Your full address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
              </div>
            </div>
            <button type="submit" class="btn btn-gold">
              <i class="fas fa-save"></i> Save Changes
            </button>
          </form>
        </div>

        <!-- Change Password -->
        <div style="background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);padding:1.75rem;margin-bottom:1.5rem;">
          <h3 style="font-family:var(--font-heading);margin-bottom:1.5rem;display:flex;align-items:center;gap:0.6rem;">
            <i class="fas fa-lock" style="color:var(--gold);"></i> Change Password
          </h3>
          <form method="POST">
            <input type="hidden" name="change_password" value="1">
            <div class="form-group">
              <label class="form-label">Current Password</label>
              <div style="position:relative;">
                <input type="password" name="current_password" class="form-control"
                       placeholder="Enter current password" style="padding-right:3rem;">
                <i class="fas fa-eye password-toggle" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--gray);"></i>
              </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
              <div class="form-group">
                <label class="form-label">New Password</label>
                <div style="position:relative;">
                  <input type="password" name="new_password" class="form-control"
                         placeholder="Min 6 characters" style="padding-right:3rem;">
                  <i class="fas fa-eye password-toggle" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--gray);"></i>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <div style="position:relative;">
                  <input type="password" name="confirm_new_password" class="form-control"
                         placeholder="Repeat new password" style="padding-right:3rem;">
                  <i class="fas fa-eye password-toggle" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--gray);"></i>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-blue">
              <i class="fas fa-key"></i> Update Password
            </button>
          </form>
        </div>

        <!-- Recent Orders -->
        <?php if (!empty($orders)): ?>
        <div style="background:var(--dark-3);border:1px solid rgba(255,255,255,0.06);border-radius:var(--radius-md);padding:1.75rem;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
            <h3 style="font-family:var(--font-heading);display:flex;align-items:center;gap:0.6rem;">
              <i class="fas fa-receipt" style="color:var(--gold);"></i> Recent Orders
            </h3>
            <a href="orders.php" style="color:var(--gold);font-size:0.85rem;">View All</a>
          </div>
          <div style="overflow-x:auto;">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Date</th>
                  <th>Amount</th>
                  <th>Payment</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                <tr>
                  <td style="color:var(--gold);font-weight:600;"><?= htmlspecialchars($order['order_id']) ?></td>
                  <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                  <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                  <td><?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></td>
                  <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </div>
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
// Preview profile picture before upload
document.getElementById('avatarInput')?.addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('profileAvatarPreview').src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
});
</script>
</body>
</html>

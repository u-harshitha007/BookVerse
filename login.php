<?php
/**
 * BookVerse - Login Page
 */
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            $redirect = $_GET['redirect'] ?? BASE_URL . '/index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

function sanitize($d) { return htmlspecialchars(strip_tags(trim($d))); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <title>Login – BookVerse</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">

    <div class="auth-logo">
      <a href="index.php">
        <span class="logo-text">BookVerse</span>
        <span class="logo-sub">Discover. Read. Collect.</span>
      </a>
    </div>

    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-subtitle">Sign in to your account to continue</p>

    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Registration successful! Please log in.
      </div>
    <?php endif; ?>

    <form method="POST" id="loginForm" novalidate>
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        <span class="form-error">Please enter a valid email.</span>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position:relative;">
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Enter your password" required style="padding-right:3rem;">
          <i class="fas fa-eye password-toggle" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--gray);"></i>
        </div>
        <span class="form-error">Password is required.</span>
      </div>

      <div style="text-align:right;margin-bottom:1.25rem;">
        <a href="#" style="font-size:0.85rem;color:var(--gold);">Forgot password?</a>
      </div>

      <button type="submit" class="btn btn-gold btn-block btn-lg">
        <i class="fas fa-sign-in-alt"></i> Sign In
      </button>
    </form>

    <div class="auth-divider"><span>or</span></div>

    <!-- Demo login hint -->
    <div style="background:rgba(212,175,55,0.05);border:1px solid rgba(212,175,55,0.15);border-radius:8px;padding:1rem;font-size:0.82rem;color:var(--gray-light);margin-bottom:1rem;">
      <strong style="color:var(--gold);">Demo credentials:</strong><br>
      Admin: <code>admin@bookverse.com</code> / <code>password</code><br>
      <span style="font-size:0.78rem;">(Register a new account for customer access)</span>
    </div>

    <p class="auth-footer">
      Don't have an account? <a href="register.php">Sign up free</a>
    </p>
  </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>

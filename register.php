<?php
/**
 * BookVerse - Registration Page
 */
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($_POST['name']     ?? '');
    $email    = sanitize($_POST['email']    ?? '');
    $phone    = sanitize($_POST['phone']    ?? '');
    $password = $_POST['password']          ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($name, $email, $password, $phone);
        if ($result['success']) {
            header('Location: login.php?registered=1');
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
  <title>Register – BookVerse</title>
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

    <h2 class="auth-title">Create Account</h2>
    <p class="auth-subtitle">Join BookVerse — it's free</p>

    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="registerForm" novalidate onsubmit="return validateForm('registerForm')">

      <div class="form-group">
        <label class="form-label" for="name">Full Name <span style="color:var(--danger)">*</span></label>
        <input type="text" id="name" name="name" class="form-control"
               placeholder="Your full name"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        <span class="form-error">Name is required.</span>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email Address <span style="color:var(--danger)">*</span></label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="your@email.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        <span class="form-error">Please enter a valid email.</span>
      </div>

      <div class="form-group">
        <label class="form-label" for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" class="form-control"
               placeholder="+91 98765 43210"
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password <span style="color:var(--danger)">*</span></label>
        <div style="position:relative;">
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Minimum 6 characters" required style="padding-right:3rem;">
          <i class="fas fa-eye password-toggle" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--gray);"></i>
        </div>
        <span class="form-error">Password is required (min 6 chars).</span>
      </div>

      <div class="form-group">
        <label class="form-label" for="confirm_password">Confirm Password <span style="color:var(--danger)">*</span></label>
        <div style="position:relative;">
          <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                 placeholder="Repeat your password" required style="padding-right:3rem;">
          <i class="fas fa-eye password-toggle" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--gray);"></i>
        </div>
        <span class="form-error">Passwords do not match.</span>
      </div>

      <div style="margin-bottom:1.25rem;font-size:0.82rem;color:var(--gray);">
        By registering, you agree to our
        <a href="#" style="color:var(--gold);">Terms of Service</a> and
        <a href="#" style="color:var(--gold);">Privacy Policy</a>.
      </div>

      <button type="submit" class="btn btn-gold btn-block btn-lg">
        <i class="fas fa-user-plus"></i> Create Account
      </button>
    </form>

    <p class="auth-footer">
      Already have an account? <a href="login.php">Sign in</a>
    </p>
  </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>

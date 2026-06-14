<?php
/**
 * BookVerse - Homepage
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$featuredBooks  = getBooks(['is_featured'    => 1], 4);
$bestsellers    = getBooks(['is_bestseller'   => 1], 4);
$newArrivals    = getBooks(['is_new_arrival'  => 1], 4);
$cartCount      = isLoggedIn() ? getCartCount($_SESSION['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <title>BookVerse – Discover. Read. Collect.</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- ── Navbar ── -->
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
      <li><a href="index.php"    class="nav-link active">Home</a></li>
      <li><a href="books.php"    class="nav-link">Books</a></li>
      <li><a href="#about"       class="nav-link">About</a></li>
      <li><a href="#contact"     class="nav-link">Contact</a></li>
      <?php if (isAdmin()): ?>
        <li><a href="admin.php" class="nav-link text-gold">Admin</a></li>
      <?php endif; ?>
    </ul>

    <div class="nav-actions">
      <a href="cart.php" class="btn btn-outline btn-sm cart-badge">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cartCount > 0): ?>
          <span class="badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
      <?php if (isLoggedIn()): ?>
        <a href="profile.php" class="nav-link d-flex align-center gap-1">
          <img src="uploads/<?= htmlspecialchars($_SESSION['user_picture'] ?? 'default.png') ?>"
               class="nav-avatar" alt="Profile"
               onerror="this.src='assets/images/default_avatar.png'">
        </a>
        <a href="logout.php" class="btn btn-outline btn-sm">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      <?php else: ?>
        <a href="login.php"    class="btn btn-outline btn-sm">Login</a>
        <a href="register.php" class="btn btn-gold btn-sm">Sign Up</a>
      <?php endif; ?>
      <div class="hamburger" id="hamburger">
        <span></span><span></span><span></span>
      </div>
    </div>
  </div>
</nav>

<!-- ── Hero Section ── -->
<section class="hero" id="home">
  <div class="hero-bg"></div>
  <div class="hero-particles"></div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-text">
        <div class="hero-badge">
          <i class="fas fa-star"></i>
          <span>Premium Online Bookstore</span>
        </div>
        <h1 class="hero-title">
          Welcome to <br><span class="brand">BookVerse</span>
        </h1>
        <p class="hero-tagline">Discover. Read. Collect.</p>
        <p class="hero-description">
          Explore thousands of books from every genre — programming, fiction, business, self-help, and more.
          Your next great read is just a click away.
        </p>
        <div class="hero-actions">
          <a href="books.php" class="btn btn-gold btn-lg">
            <i class="fas fa-book-open"></i> Browse Books
          </a>
          <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-outline btn-lg">
              <i class="fas fa-user-plus"></i> Join Free
            </a>
          <?php else: ?>
            <a href="orders.php" class="btn btn-outline btn-lg">
              <i class="fas fa-receipt"></i> My Orders
            </a>
          <?php endif; ?>
        </div>
        <div class="hero-stats">
          <div class="stat-item">
            <span class="stat-number" data-target="5000" data-suffix="+">5000+</span>
            <span class="stat-label">Books</span>
          </div>
          <div class="stat-item">
            <span class="stat-number" data-target="12000" data-suffix="+">12K+</span>
            <span class="stat-label">Readers</span>
          </div>
          <div class="stat-item">
            <span class="stat-number" data-target="6">6</span>
            <span class="stat-label">Genres</span>
          </div>
        </div>
      </div>
      <div class="hero-visual">
        <div class="hero-glow"></div>
        <div class="books-showcase">
          <div class="showcase-card">
            <img src="assets/images/books/atomic_habits.jpg" alt="Book" onerror="this.style.background='#1A3C6E'">
          </div>
          <div class="showcase-card">
            <img src="assets/images/books/project_hail.jpg" alt="Book" onerror="this.style.background='#222'">
          </div>
          <div class="showcase-card">
            <img src="assets/images/books/clean_code.jpg" alt="Book" onerror="this.style.background='#2C2C2C'">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Featured Books ── -->
<?php if (!empty($featuredBooks)): ?>
<section class="section" id="featured">
  <div class="container">
    <div class="section-header">
      <p class="section-subtitle">Editor's Picks</p>
      <h2 class="section-title">Featured Books</h2>
      <p class="section-desc">Hand-picked titles across our most popular categories</p>
    </div>
    <div class="books-grid">
      <?php foreach ($featuredBooks as $book): ?>
        <?php include 'includes/book_card.php'; ?>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="books.php" class="btn btn-outline">View All Books <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Bestsellers ── -->
<?php if (!empty($bestsellers)): ?>
<section class="section" style="background: var(--dark-3);" id="bestsellers">
  <div class="container">
    <div class="section-header">
      <p class="section-subtitle">Most Popular</p>
      <h2 class="section-title">Best Sellers</h2>
      <p class="section-desc">Our readers' all-time favourite books</p>
    </div>
    <div class="books-grid">
      <?php foreach ($bestsellers as $book): ?>
        <?php include 'includes/book_card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── New Arrivals ── -->
<?php if (!empty($newArrivals)): ?>
<section class="section" id="new-arrivals">
  <div class="container">
    <div class="section-header">
      <p class="section-subtitle">Just In</p>
      <h2 class="section-title">New Arrivals</h2>
      <p class="section-desc">Fresh titles added to our collection this season</p>
    </div>
    <div class="books-grid">
      <?php foreach ($newArrivals as $book): ?>
        <?php include 'includes/book_card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Testimonials ── -->
<section class="section" style="background: var(--dark-3);" id="testimonials">
  <div class="container">
    <div class="section-header centered">
      <p class="section-subtitle">Happy Readers</p>
      <h2 class="section-title">What Our Customers Say</h2>
    </div>
    <div class="testimonials-grid">
      <?php
      $testimonials = [
        ['name' => 'Priya Sharma',    'role' => 'Software Engineer', 'init' => 'PS',
         'text' => '"BookVerse completely changed how I discover new books. The curated selections are fantastic, and the dark theme is so easy on the eyes!"'],
        ['name' => 'Arjun Mehta',     'role' => 'Data Scientist',    'init' => 'AM',
         'text' => '"Found all my ML and Data Science books in one place. The search feature is incredibly fast. Highly recommend to every tech enthusiast!"'],
        ['name' => 'Sneha Patel',     'role' => 'Avid Reader',       'init' => 'SP',
         'text' => '"The fiction collection is amazing. Got Project Hail Mary delivered in two days. The checkout process was seamless and the packaging was perfect!"'],
        ['name' => 'Rahul Verma',     'role' => 'Entrepreneur',      'init' => 'RV',
         'text' => '"Running my startup journey alongside BookVerse has been incredible. The business book collection is top-notch with great prices!"'],
        ['name' => 'Ananya Singh',    'role' => 'Student',           'init' => 'AS',
         'text' => '"As a CS student, finding quality programming books at affordable prices is a blessing. BookVerse has become my go-to study resource!"'],
        ['name' => 'Karthik Nair',    'role' => 'Cybersecurity Pro', 'init' => 'KN',
         'text' => '"The cybersecurity section has all the classics. Detailed descriptions help me pick the right book every time. Excellent user experience!"'],
      ];
      foreach ($testimonials as $t): ?>
      <div class="testimonial-card">
        <div class="testimonial-quote"><i class="fas fa-quote-left"></i></div>
        <p class="testimonial-text"><?= $t['text'] ?></p>
        <div class="testimonial-author">
          <div class="author-avatar"><?= $t['init'] ?></div>
          <div>
            <div class="author-name"><?= $t['name'] ?></div>
            <div class="author-role"><?= $t['role'] ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── About ── -->
<section class="section" id="about">
  <div class="container">
    <div class="about-grid">
      <div>
        <p class="section-subtitle">Our Story</p>
        <h2 class="section-title">About BookVerse</h2>
        <p style="color:var(--gray-light);margin:1rem 0;line-height:1.8;">
          BookVerse was founded with a single mission: to make great books accessible to everyone.
          We believe that knowledge and stories have the power to transform lives, and every reader
          deserves a premium experience — without the premium price.
        </p>
        <p style="color:var(--gray-light);line-height:1.8;">
          From programming guides to fiction masterpieces, from business wisdom to cybersecurity expertise —
          our curated collection spans every domain. We work directly with publishers to bring you the
          best titles at competitive prices.
        </p>
        <div class="about-features">
          <?php
          $features = [
            ['icon' => 'fa-shipping-fast',  'title' => 'Fast Delivery',    'desc' => 'Books delivered in 2-3 days'],
            ['icon' => 'fa-shield-alt',     'title' => 'Secure Payments',  'desc' => 'SSL encrypted checkout'],
            ['icon' => 'fa-undo',           'title' => 'Easy Returns',     'desc' => '7-day return policy'],
            ['icon' => 'fa-headset',        'title' => '24/7 Support',     'desc' => 'Expert book recommendations'],
          ];
          foreach ($features as $f): ?>
          <div class="feature-item">
            <div class="feature-icon"><i class="fas <?= $f['icon'] ?>"></i></div>
            <div class="feature-text">
              <h4><?= $f['title'] ?></h4>
              <p><?= $f['desc'] ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="about-visual">
        <i class="fas fa-book-open big-icon"></i>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:1rem;padding:2rem;text-align:center;">
          <div style="font-size:4rem;color:var(--gold);opacity:0.8;"><i class="fas fa-book-open"></i></div>
          <h3 style="font-family:var(--font-heading);font-size:1.5rem;color:var(--gold);">5,000+ Titles</h3>
          <p style="color:var(--gray-light);font-size:0.9rem;">Across 6 major categories, curated for every kind of reader</p>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;width:100%;margin-top:0.5rem;">
            <?php foreach (['Programming','Data Science','Fiction','Business','Self Help','Cybersecurity'] as $cat): ?>
              <span style="background:rgba(212,175,55,0.1);border:1px solid rgba(212,175,55,0.2);padding:0.4rem 0.75rem;border-radius:6px;font-size:0.78rem;color:var(--gold-light);text-align:center;"><?= $cat ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Contact ── -->
<section class="section" style="background:var(--dark-3);" id="contact">
  <div class="container">
    <div class="section-header centered">
      <p class="section-subtitle">Get In Touch</p>
      <h2 class="section-title">Contact Us</h2>
      <p class="section-desc">Have a question? We'd love to hear from you.</p>
    </div>
    <div class="contact-grid">
      <div>
        <h3 style="font-family:var(--font-heading);margin-bottom:1.5rem;font-size:1.3rem;">Send a Message</h3>
        <form onsubmit="handleContactSubmit(event)">
          <div class="form-group">
            <label class="form-label">Your Name</label>
            <input type="text" class="form-control" placeholder="Enter your name" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" placeholder="your@email.com" required>
          </div>
          <div class="form-group">
            <label class="form-label">Message</label>
            <textarea class="form-control" rows="5" placeholder="Your message..." required style="resize:vertical;"></textarea>
          </div>
          <button type="submit" class="btn btn-gold btn-block">
            <i class="fas fa-paper-plane"></i> Send Message
          </button>
        </form>
      </div>
      <div>
        <h3 style="font-family:var(--font-heading);margin-bottom:1.5rem;font-size:1.3rem;">Contact Information</h3>
        <?php
        $contactInfo = [
          ['icon' => 'fa-map-marker-alt', 'title' => 'Address',        'info' => '123 Book Street, Literature Lane,<br>Mumbai, Maharashtra 400001'],
          ['icon' => 'fa-phone',          'title' => 'Phone',          'info' => '+91 98765 43210'],
          ['icon' => 'fa-envelope',       'title' => 'Email',          'info' => 'hello@bookverse.in'],
          ['icon' => 'fa-clock',          'title' => 'Business Hours', 'info' => 'Mon – Sat: 9AM – 8PM IST'],
        ];
        foreach ($contactInfo as $c): ?>
        <div class="feature-item mb-3">
          <div class="feature-icon"><i class="fas <?= $c['icon'] ?>"></i></div>
          <div class="feature-text">
            <h4><?= $c['title'] ?></h4>
            <p><?= $c['info'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── Footer ── -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <span class="logo-text">BookVerse</span>
        <p class="footer-desc">
          Your premium destination for books of every genre.
          Discover, read, and collect the stories that matter most to you.
        </p>
        <div class="social-links">
          <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div>
        <h4 class="footer-heading">Quick Links</h4>
        <ul class="footer-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="books.php">Browse Books</a></li>
          <li><a href="register.php">Sign Up</a></li>
          <li><a href="login.php">Login</a></li>
        </ul>
      </div>
      <div>
        <h4 class="footer-heading">Categories</h4>
        <ul class="footer-links">
          <li><a href="books.php?category=Programming">Programming</a></li>
          <li><a href="books.php?category=Data+Science">Data Science</a></li>
          <li><a href="books.php?category=Fiction">Fiction</a></li>
          <li><a href="books.php?category=Business">Business</a></li>
          <li><a href="books.php?category=Cybersecurity">Cybersecurity</a></li>
        </ul>
      </div>
      <div>
        <h4 class="footer-heading">Newsletter</h4>
        <p style="color:var(--gray);font-size:0.85rem;margin-bottom:1rem;">
          Get book recommendations and exclusive deals.
        </p>
        <div class="newsletter-form">
          <input type="email" class="newsletter-input" placeholder="your@email.com">
          <button class="newsletter-btn" onclick="showToast('Subscribed! 🎉','success')">
            <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p class="footer-copy">&copy; <?= date('Y') ?> BookVerse. All rights reserved.</p>
      <div class="footer-legal">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Refund Policy</a>
      </div>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
<script>
function handleContactSubmit(e) {
  e.preventDefault();
  showToast('Message sent! We\'ll get back to you soon.', 'success');
  e.target.reset();
}
</script>
</body>
</html>

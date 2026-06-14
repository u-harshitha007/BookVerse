/**
 * BookVerse - Main JavaScript
 * Handles navbar, animations, search, cart interactions, and UI utilities
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Navbar scroll effect ── */
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    });
  }

  /* ── Hamburger menu ── */
  const hamburger = document.getElementById('hamburger');
  const navMenu   = document.getElementById('navMenu');
  if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
      navMenu.classList.toggle('open');
      const spans = hamburger.querySelectorAll('span');
      hamburger.classList.toggle('active');
    });
    // Close menu on outside click
    document.addEventListener('click', (e) => {
      if (!navbar.contains(e.target)) navMenu.classList.remove('open');
    });
  }

  /* ── Hero particles ── */
  const particlesWrap = document.querySelector('.hero-particles');
  if (particlesWrap) {
    for (let i = 0; i < 30; i++) {
      const p = document.createElement('div');
      p.classList.add('particle');
      p.style.cssText = `
        left: ${Math.random() * 100}%;
        width: ${Math.random() * 4 + 2}px;
        height: ${Math.random() * 4 + 2}px;
        animation-duration: ${Math.random() * 15 + 10}s;
        animation-delay: ${Math.random() * 10}s;
        opacity: ${Math.random() * 0.5 + 0.1};
      `;
      particlesWrap.appendChild(p);
    }
  }

  /* ── Smooth scroll for anchor links ── */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        navMenu && navMenu.classList.remove('open');
      }
    });
  });

  /* ── Active nav link ── */
  const currentPage = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-link').forEach(link => {
    const href = link.getAttribute('href')?.split('/').pop();
    if (href === currentPage || (currentPage === '' && href === 'index.php')) {
      link.classList.add('active');
    }
  });

  /* ── Intersection Observer - Animate on scroll ── */
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.book-card, .testimonial-card, .stat-card, .feature-item').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
  });

  /* ── Counter animation ── */
  function animateCounter(el) {
    const target = parseInt(el.getAttribute('data-target'));
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
    const timer = setInterval(() => {
      current += step;
      if (current >= target) {
        el.textContent = target.toLocaleString() + (el.getAttribute('data-suffix') || '');
        clearInterval(timer);
      } else {
        el.textContent = Math.floor(current).toLocaleString() + (el.getAttribute('data-suffix') || '');
      }
    }, 16);
  }

  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        counterObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('[data-target]').forEach(el => counterObserver.observe(el));

  /* ── Toast Notification System ── */
  window.showToast = function(message, type = 'info') {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.classList.add('toast-container');
      document.body.appendChild(container);
    }

    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
    const toast = document.createElement('div');
    toast.classList.add('toast', type);
    toast.innerHTML = `
      <i class="fas ${icons[type] || icons.info}"></i>
      <span class="toast-msg">${message}</span>
    `;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = 'slide-in 0.3s ease reverse';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  };

  /* ── Cart: Add to cart via AJAX ── */
  document.querySelectorAll('.btn-cart, .overlay-btn.cart').forEach(btn => {
    btn.addEventListener('click', function (e) {
      const bookId = this.dataset.bookId;
      if (!bookId) return;

      e.preventDefault();

      fetch(`${BASE_URL}/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&book_id=${bookId}&quantity=1`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showToast('Added to cart!', 'success');
          // Update cart badge
          const badge = document.querySelector('.cart-badge .badge');
          if (badge) badge.textContent = data.cart_count;
        } else {
          showToast(data.message || 'Please login first.', 'error');
        }
      })
      .catch(() => showToast('Something went wrong.', 'error'));
    });
  });

  /* ── Search filtering on books page ── */
  const searchInput = document.getElementById('searchInput');
  const categoryFilter = document.getElementById('categoryFilter');
  const sortFilter = document.getElementById('sortFilter');
  const booksGrid = document.getElementById('booksGrid');

  if (searchInput && booksGrid) {
    function filterBooks() {
      const query    = searchInput.value.toLowerCase().trim();
      const category = categoryFilter ? categoryFilter.value.toLowerCase() : '';
      const sort     = sortFilter ? sortFilter.value : '';

      const cards = Array.from(booksGrid.querySelectorAll('.book-card'));

      cards.forEach(card => {
        const title  = card.dataset.title?.toLowerCase() || '';
        const author = card.dataset.author?.toLowerCase() || '';
        const cat    = card.dataset.category?.toLowerCase() || '';

        const matchSearch   = !query || title.includes(query) || author.includes(query) || cat.includes(query);
        const matchCategory = !category || cat === category;

        card.style.display = (matchSearch && matchCategory) ? '' : 'none';
      });

      // Sort
      if (sort) {
        const visible = cards.filter(c => c.style.display !== 'none');
        visible.sort((a, b) => {
          if (sort === 'price-asc')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
          if (sort === 'price-desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
          if (sort === 'rating')     return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
          if (sort === 'title')      return (a.dataset.title || '').localeCompare(b.dataset.title || '');
          return 0;
        });
        visible.forEach(card => booksGrid.appendChild(card));
      }

      // Empty state
      const visible = cards.filter(c => c.style.display !== 'none');
      let emptyState = booksGrid.querySelector('.empty-state');
      if (visible.length === 0) {
        if (!emptyState) {
          emptyState = document.createElement('div');
          emptyState.classList.add('empty-state');
          emptyState.innerHTML = `
            <i class="fas fa-search"></i>
            <h3>No books found</h3>
            <p>Try adjusting your search or filter</p>
          `;
          booksGrid.appendChild(emptyState);
        }
      } else if (emptyState) {
        emptyState.remove();
      }
    }

    searchInput.addEventListener('input', filterBooks);
    if (categoryFilter) categoryFilter.addEventListener('change', filterBooks);
    if (sortFilter) sortFilter.addEventListener('change', filterBooks);
  }

  /* ── Cart page: quantity controls ── */
  document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const cartId = this.closest('.cart-item').dataset.cartId;
      const input  = this.closest('.qty-control').querySelector('.qty-val');
      let qty = parseInt(input.value);

      if (this.classList.contains('minus')) qty = Math.max(1, qty - 1);
      if (this.classList.contains('plus'))  qty += 1;

      input.value = qty;

      fetch(`${BASE_URL}/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&cart_id=${cartId}&quantity=${qty}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Update subtotal for the item
          const priceEl = this.closest('.cart-item').querySelector('.cart-item-price');
          if (priceEl && data.item_total) priceEl.textContent = data.item_total;
          // Update summary totals
          updateCartSummary();
        }
      });
    });
  });

  /* ── Cart: remove item ── */
  document.querySelectorAll('.cart-item-remove').forEach(btn => {
    btn.addEventListener('click', function () {
      const item = this.closest('.cart-item');
      const cartId = item.dataset.cartId;

      fetch(`${BASE_URL}/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&cart_id=${cartId}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          item.style.animation = 'none';
          item.style.opacity = '0';
          item.style.transform = 'translateX(30px)';
          item.style.transition = 'all 0.3s ease';
          setTimeout(() => { item.remove(); updateCartSummary(); }, 300);
          showToast('Item removed from cart', 'info');
          const badge = document.querySelector('.cart-badge .badge');
          if (badge) badge.textContent = data.cart_count;
        }
      });
    });
  });

  function updateCartSummary() {
    fetch(`${BASE_URL}/cart.php?action=summary`)
    .then(res => res.json())
    .then(data => {
      if (data) {
        const subtotalEl = document.getElementById('summarySubtotal');
        const totalEl    = document.getElementById('summaryTotal');
        const countEl    = document.getElementById('summaryCount');
        if (subtotalEl) subtotalEl.textContent = data.subtotal;
        if (totalEl)    totalEl.textContent    = data.total;
        if (countEl)    countEl.textContent    = data.count + ' item(s)';
      }
    });
  }

  /* ── Form validation helper ── */
  window.validateForm = function(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let valid = true;

    form.querySelectorAll('[required]').forEach(field => {
      field.classList.remove('invalid');
      if (!field.value.trim()) {
        field.classList.add('invalid');
        valid = false;
      }
    });

    // Email validation
    form.querySelectorAll('[type="email"]').forEach(field => {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (field.value && !re.test(field.value)) {
        field.classList.add('invalid');
        valid = false;
      }
    });

    // Password match
    const pwd  = form.querySelector('#password');
    const conf = form.querySelector('#confirm_password');
    if (pwd && conf && pwd.value !== conf.value) {
      conf.classList.add('invalid');
      const err = conf.nextElementSibling;
      if (err && err.classList.contains('form-error')) {
        err.textContent = 'Passwords do not match';
        err.style.display = 'block';
      }
      valid = false;
    }

    return valid;
  };

  /* ── Password toggle ── */
  document.querySelectorAll('.password-toggle').forEach(toggle => {
    toggle.addEventListener('click', function () {
      const input = this.previousElementSibling;
      if (input.type === 'password') {
        input.type = 'text';
        this.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type = 'password';
        this.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });
  });

  /* ── Modal system ── */
  window.openModal = function(id) {
    const overlay = document.getElementById(id);
    if (overlay) overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  window.closeModal = function(id) {
    const overlay = document.getElementById(id);
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
  };

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function (e) {
      if (e.target === this) closeModal(this.id);
    });
  });

  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', function () {
      const modal = this.closest('.modal-overlay');
      if (modal) closeModal(modal.id);
    });
  });

  /* ── Auto-dismiss alerts ── */
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  });

});

/* BASE_URL for AJAX calls */
const BASE_URL = document.querySelector('meta[name="base-url"]')?.content || '';

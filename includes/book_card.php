<?php
/**
 * BookVerse - Reusable Book Card Component
 * Expects $book array to be set in the including scope
 */
$discount = ($book['original_price'] > $book['price'])
    ? round((($book['original_price'] - $book['price']) / $book['original_price']) * 100)
    : 0;
?>
<div class="book-card"
     data-title="<?= htmlspecialchars($book['title']) ?>"
     data-author="<?= htmlspecialchars($book['author']) ?>"
     data-category="<?= htmlspecialchars($book['category']) ?>"
     data-price="<?= $book['price'] ?>"
     data-rating="<?= $book['rating'] ?>">

  <div class="book-cover">
    <img src="assets/images/books/<?= htmlspecialchars($book['cover_image']) ?>"
         alt="<?= htmlspecialchars($book['title']) ?>"
         onerror="this.src='assets/images/default_book.jpg'">

    <!-- Badges -->
    <div class="book-badges">
      <?php if ($book['is_featured']):   ?><span class="badge-tag badge-featured">Featured</span><?php endif; ?>
      <?php if ($book['is_bestseller']): ?><span class="badge-tag badge-bestseller">Bestseller</span><?php endif; ?>
      <?php if ($book['is_new_arrival']): ?><span class="badge-tag badge-new">New</span><?php endif; ?>
    </div>

    <?php if ($discount > 0): ?>
      <div class="book-discount">-<?= $discount ?>%</div>
    <?php endif; ?>

    <!-- Hover Overlay -->
    <div class="book-overlay">
      <button class="overlay-btn cart btn-cart"
              data-book-id="<?= $book['id'] ?>"
              title="Add to Cart">
        <i class="fas fa-shopping-cart"></i>
      </button>
      <a href="book_details.php?id=<?= $book['id'] ?>"
         class="overlay-btn view" title="View Details">
        <i class="fas fa-eye"></i>
      </a>
    </div>
  </div>

  <div class="book-info">
    <div class="book-category"><?= htmlspecialchars($book['category']) ?></div>
    <h3 class="book-title">
      <a href="book_details.php?id=<?= $book['id'] ?>"
         style="color:inherit;text-decoration:none;"><?= htmlspecialchars($book['title']) ?></a>
    </h3>
    <p class="book-author">by <?= htmlspecialchars($book['author']) ?></p>

    <div class="book-rating">
      <div class="stars">
        <?php
        $rating = floatval($book['rating']);
        for ($i = 1; $i <= 5; $i++):
          if ($i <= floor($rating)): ?>
            <i class="fas fa-star"></i>
          <?php elseif ($i - 0.5 <= $rating): ?>
            <i class="fas fa-star-half-alt"></i>
          <?php else: ?>
            <i class="far fa-star"></i>
          <?php endif;
        endfor; ?>
      </div>
      <span class="rating-count">(<?= number_format($book['total_reviews']) ?>)</span>
    </div>

    <div class="book-price">
      <span class="price-current">₹<?= number_format($book['price'], 2) ?></span>
      <?php if ($book['original_price'] > $book['price']): ?>
        <span class="price-original">₹<?= number_format($book['original_price'], 2) ?></span>
      <?php endif; ?>
    </div>

    <button class="btn-cart" data-book-id="<?= $book['id'] ?>">
      <i class="fas fa-shopping-cart"></i> Add to Cart
    </button>
  </div>
</div>

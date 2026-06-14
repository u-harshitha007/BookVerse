<?php
/**
 * BookVerse - Helper Functions
 * Utility functions used across the application
 */

require_once __DIR__ . '/db.php';

/**
 * Sanitize user input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Generate a unique order ID
 * Format: BV2026001
 */
function generateOrderId() {
    global $conn;
    $year = date('Y');
    $result = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE YEAR(created_at) = $year");
    $row = $result->fetch_assoc();
    $count = $row['cnt'] + 1;
    return 'BV' . $year . str_pad($count, 3, '0', STR_PAD_LEFT);
}

/**
 * Get all books with optional filters
 */
function getBooks($filter = [], $limit = null, $offset = 0) {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if (!empty($filter['category'])) {
        $where[] = "category = ?";
        $params[] = $filter['category'];
        $types .= 's';
    }
    if (!empty($filter['search'])) {
        $where[] = "(title LIKE ? OR author LIKE ? OR category LIKE ?)";
        $s = '%' . $filter['search'] . '%';
        $params[] = $s; $params[] = $s; $params[] = $s;
        $types .= 'sss';
    }
    if (isset($filter['is_featured'])) {
        $where[] = "is_featured = ?";
        $params[] = $filter['is_featured'];
        $types .= 'i';
    }
    if (isset($filter['is_bestseller'])) {
        $where[] = "is_bestseller = ?";
        $params[] = $filter['is_bestseller'];
        $types .= 'i';
    }
    if (isset($filter['is_new_arrival'])) {
        $where[] = "is_new_arrival = ?";
        $params[] = $filter['is_new_arrival'];
        $types .= 'i';
    }

    $sql = "SELECT * FROM books";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY created_at DESC";

    if ($limit !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $books;
}

/**
 * Get a single book by ID
 */
function getBookById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();
    return $book;
}

/**
 * Get cart items for a user
 */
function getCartItems($user_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT c.id as cart_id, c.quantity, b.*
        FROM cart c
        JOIN books b ON c.book_id = b.id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $items;
}

/**
 * Get cart item count for a user
 */
function getCartCount($user_id) {
    global $conn;
    if (!$user_id) return 0;
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'] ?? 0;
}

/**
 * Add to cart
 */
function addToCart($user_id, $book_id, $quantity = 1) {
    global $conn;

    // Check if already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $stmt->close();

    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $newQty, $existing['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $book_id, $quantity);
        $stmt->execute();
        $stmt->close();
    }
    return true;
}

/**
 * Remove from cart
 */
function removeFromCart($cart_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Update cart quantity
 */
function updateCartQty($cart_id, $user_id, $quantity) {
    global $conn;
    if ($quantity <= 0) {
        removeFromCart($cart_id, $user_id);
        return;
    }
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Get orders for a user (or all orders for admin)
 */
function getOrders($user_id = null) {
    global $conn;
    if ($user_id) {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
    } else {
        $stmt = $conn->prepare("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $orders;
}

/**
 * Get order items
 */
function getOrderItems($order_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT oi.*, b.title, b.author, b.cover_image
        FROM order_items oi
        LEFT JOIN books b ON oi.book_id = b.id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $items;
}

/**
 * Format price in INR
 */
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

/**
 * Generate star rating HTML
 */
function starRating($rating) {
    $html = '<div class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="fas fa-star"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $html .= '<i class="far fa-star"></i>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * Get all categories
 */
function getCategories() {
    global $conn;
    $result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    return $categories;
}

/**
 * Book cover image path helper
 */
function bookCover($image) {
    $path = BASE_URL . '/assets/images/books/' . $image;
    return $path;
}

/**
 * Get discount percentage
 */
function getDiscount($original, $current) {
    if ($original > $current) {
        return round((($original - $current) / $original) * 100);
    }
    return 0;
}

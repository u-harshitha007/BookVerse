<?php
/**
 * BookVerse - Database Connection
 * Establishes MySQLi connection using prepared statements
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bookverse');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');

/**
 * Base URL helper
 */
define('BASE_URL', 'http://localhost/sia/BookVerse');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('SITE_NAME', 'BookVerse');

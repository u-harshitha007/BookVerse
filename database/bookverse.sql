-- BookVerse Database Schema
-- Created for BookVerse Online Book Store Management System

CREATE DATABASE IF NOT EXISTS bookverse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookverse;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_picture VARCHAR(255) DEFAULT 'default.png',
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table: books
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    stock INT DEFAULT 0,
    cover_image VARCHAR(255) DEFAULT 'default_book.jpg',
    rating DECIMAL(2,1) DEFAULT 0.0,
    total_reviews INT DEFAULT 0,
    isbn VARCHAR(20),
    publisher VARCHAR(150),
    publish_year YEAR,
    pages INT,
    language VARCHAR(50) DEFAULT 'English',
    is_featured TINYINT(1) DEFAULT 0,
    is_bestseller TINYINT(1) DEFAULT 0,
    is_new_arrival TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table: cart
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, book_id)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table: orders
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(20),
    shipping_address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table: order_items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table: payments
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Sample Data: Admin User
-- Password: admin123 (hashed)
-- --------------------------------------------------------
INSERT INTO users (name, email, password, phone, role) VALUES
('Admin', 'admin@bookverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'admin');

-- --------------------------------------------------------
-- Sample Data: Books (12 books across 6 categories)
-- --------------------------------------------------------
INSERT INTO books (title, author, category, description, price, original_price, stock, cover_image, rating, total_reviews, isbn, publisher, publish_year, pages, is_featured, is_bestseller, is_new_arrival) VALUES

-- Programming
('Clean Code', 'Robert C. Martin', 'Programming', 'A handbook of agile software craftsmanship. Learn to write code that is readable, maintainable, and professional.', 599.00, 799.00, 50, 'clean_code.jpg', 4.8, 2341, '9780132350884', 'Prentice Hall', 2008, 431, 1, 1, 0),

('The Pragmatic Programmer', 'David Thomas', 'Programming', 'Your journey to mastery. A timeless guide that helps developers think about their craft and continuously improve.', 649.00, 849.00, 35, 'pragmatic_programmer.jpg', 4.7, 1892, '9780201616224', 'Addison-Wesley', 2019, 352, 1, 0, 1),

-- Data Science
('Python for Data Analysis', 'Wes McKinney', 'Data Science', 'Data wrangling with pandas, NumPy, and IPython. The definitive guide for Python-based data analysis.', 749.00, 999.00, 40, 'python_data.jpg', 4.6, 1543, '9781491957660', "O'Reilly", 2022, 544, 1, 1, 0),

('Hands-On Machine Learning', 'Aurélien Géron', 'Data Science', 'Using Scikit-Learn, Keras & TensorFlow. Build intelligent systems with practical machine learning techniques.', 899.00, 1199.00, 28, 'ml_book.jpg', 4.9, 3210, '9781492032649', "O'Reilly", 2022, 851, 1, 1, 1),

-- Fiction
('The Midnight Library', 'Matt Haig', 'Fiction', 'Between life and death there is a library. A dazzling novel about all the choices that go into a life well lived.', 399.00, 499.00, 60, 'midnight_library.jpg', 4.5, 8921, '9780525559474', 'Viking', 2020, 288, 0, 1, 0),

('Project Hail Mary', 'Andy Weir', 'Fiction', 'A lone astronaut must save Earth from disaster in this propulsive thriller that combines hard science with adventure.', 449.00, 549.00, 45, 'project_hail.jpg', 4.9, 12043, '9780593135204', 'Ballantine Books', 2021, 476, 1, 1, 1),

-- Business
('Zero to One', 'Peter Thiel', 'Business', 'Notes on startups, or how to build the future. Essential reading for entrepreneurs looking to create something truly new.', 499.00, 649.00, 55, 'zero_to_one.jpg', 4.4, 4321, '9780804139021', 'Crown Business', 2014, 224, 0, 0, 0),

('The Lean Startup', 'Eric Ries', 'Business', 'How constant innovation creates radically successful businesses. A revolutionary approach to building companies.', 549.00, 699.00, 38, 'lean_startup.jpg', 4.6, 5678, '9780307887894', 'Crown Business', 2011, 336, 1, 1, 0),

-- Self Help
('Atomic Habits', 'James Clear', 'Self Help', 'An easy and proven way to build good habits and break bad ones. Tiny changes, remarkable results.', 479.00, 599.00, 75, 'atomic_habits.jpg', 4.8, 15432, '9780735211292', 'Avery', 2018, 320, 1, 1, 1),

('Think and Grow Rich', 'Napoleon Hill', 'Self Help', 'The landmark bestseller now revised and updated for the 21st century. Master the secrets of success and achievement.', 299.00, 399.00, 80, 'think_grow.jpg', 4.5, 9876, '9781585424337', 'TarcherPerigee', 2005, 320, 0, 1, 0),

-- Cybersecurity
('The Web Application Hacker''s Handbook', 'Dafydd Stuttard', 'Cybersecurity', 'Finding and exploiting security flaws in web applications. Essential reading for every web security professional.', 799.00, 999.00, 22, 'web_hacker.jpg', 4.7, 2109, '9781118026472', 'Wiley', 2011, 912, 0, 0, 0),

('Hacking: The Art of Exploitation', 'Jon Erickson', 'Cybersecurity', 'Get inside the mind of a hacker and understand how exploits are created. Comprehensive guide to offensive security.', 699.00, 899.00, 30, 'hacking_art.jpg', 4.6, 1876, '9781593271442', 'No Starch Press', 2008, 488, 1, 0, 1);

-- ============================================================
-- BookVerse - Safe Seed Script (re-importable)
-- Run this via phpMyAdmin or: mysql -u root bookverse < seed_books.sql
-- ============================================================

USE bookverse;

-- ── Create missing tables if they don't exist ──────────────

CREATE TABLE IF NOT EXISTS order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT NOT NULL,
    book_id     INT,
    quantity    INT NOT NULL,
    unit_price  DECIMAL(10,2) NOT NULL,
    subtotal    DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id)  REFERENCES books(id)  ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    user_id         INT NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    payment_method  VARCHAR(50)  NOT NULL,
    transaction_id  VARCHAR(100),
    status          ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    paid_at         TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Clear existing books to avoid duplicates ───────────────
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE books;
SET FOREIGN_KEY_CHECKS = 1;

-- Reset auto_increment
ALTER TABLE books AUTO_INCREMENT = 1;

-- ── Insert 12 books across 6 categories ────────────────────
-- cover_image values match files in assets/images/books/

INSERT INTO books
  (title, author, category, description, price, original_price, stock,
   cover_image, rating, total_reviews, isbn, publisher, publish_year,
   pages, language, is_featured, is_bestseller, is_new_arrival)
VALUES

-- ── Programming (2 books) ──────────────────────────────────
(
  'Clean Code',
  'Robert C. Martin',
  'Programming',
  'A handbook of agile software craftsmanship. Every line of code should be readable, maintainable, and expressive. Uncle Bob shares decades of experience in writing code that not only works, but reads like well-written prose. Learn to write code your teammates will thank you for.',
  599.00, 799.00, 50,
  'clean_code.jpg',
  4.8, 2341,
  '9780132350884', 'Prentice Hall', 2008, 431, 'English',
  1, 1, 0
),
(
  'The Pragmatic Programmer',
  'David Thomas & Andrew Hunt',
  'Programming',
  'Your journey to mastery. Updated for its 20th anniversary, this book helps programmers examine their craft from first principles: what makes a pragmatic programmer, and what sets them apart from the rest. Filled with practical advice that applies to every project and every career.',
  649.00, 849.00, 35,
  'pragmatic_programmer.jpg',
  4.7, 1892,
  '9780135957059', 'Addison-Wesley', 2019, 352, 'English',
  1, 0, 1
),

-- ── Data Science (2 books) ────────────────────────────────
(
  'Python for Data Analysis',
  'Wes McKinney',
  'Data Science',
  'Data wrangling with pandas, NumPy, and IPython. Written by the creator of pandas himself, this book is the definitive guide to manipulating, processing, cleaning, and crunching datasets in Python. Essential reading for data scientists and analysts at every level.',
  749.00, 999.00, 40,
  'python_data.jpg',
  4.6, 1543,
  '9781491957660', 'O\'Reilly Media', 2022, 544, 'English',
  1, 1, 0
),
(
  'Hands-On Machine Learning',
  'Aurélien Géron',
  'Data Science',
  'Build intelligent systems using Scikit-Learn, Keras and TensorFlow. Through clear explanations, intuitive visualizations, and dozens of code examples, this bestseller teaches you to build everything from simple linear models to deep neural networks. The most comprehensive ML book available.',
  899.00, 1199.00, 28,
  'ml_book.jpg',
  4.9, 3210,
  '9781492032649', 'O\'Reilly Media', 2022, 851, 'English',
  1, 1, 1
),

-- ── Fiction (2 books) ─────────────────────────────────────
(
  'The Midnight Library',
  'Matt Haig',
  'Fiction',
  'Between life and death there is a library, and within that library, the shelves go on forever. Every book provides a chance to try another life you could have lived. A dazzling, life-affirming novel about all the choices that go into a life well lived.',
  399.00, 499.00, 60,
  'midnight_library.jpg',
  4.5, 8921,
  '9780525559474', 'Viking', 2020, 288, 'English',
  0, 1, 0
),
(
  'Project Hail Mary',
  'Andy Weir',
  'Fiction',
  'A lone astronaut must save the Earth from disaster in this propulsive, surprising, and uplifting adventure. Ryland Grace wakes up alone on a spacecraft millions of miles from home, with no memory and only the fate of humanity resting on his shoulders.',
  449.00, 549.00, 45,
  'project_hail.jpg',
  4.9, 12043,
  '9780593135204', 'Ballantine Books', 2021, 476, 'English',
  1, 1, 1
),

-- ── Business (2 books) ────────────────────────────────────
(
  'Zero to One',
  'Peter Thiel',
  'Business',
  'Notes on startups, or how to build the future. Every moment in business happens only once. The next Bill Gates will not build an operating system. The next Larry Page will not make a search engine. If you are copying these people, you are not learning from them. Create something truly new.',
  499.00, 649.00, 55,
  'zero_to_one.jpg',
  4.4, 4321,
  '9780804139021', 'Crown Business', 2014, 224, 'English',
  0, 0, 0
),
(
  'The Lean Startup',
  'Eric Ries',
  'Business',
  'How constant innovation creates radically successful businesses. Most startups fail — but many of those failures are preventable. The Lean Startup is a revolutionary approach that is being adopted across the globe, transforming the way companies are built and new products are launched.',
  549.00, 699.00, 38,
  'lean_startup.jpg',
  4.6, 5678,
  '9780307887894', 'Crown Business', 2011, 336, 'English',
  1, 1, 0
),

-- ── Self Help (2 books) ───────────────────────────────────
(
  'Atomic Habits',
  'James Clear',
  'Self Help',
  'An easy and proven way to build good habits and break bad ones. If you are having trouble changing your habits, the problem is not you — it is your system. Tiny changes, remarkable results. This book will reshape the way you think about progress and success.',
  479.00, 599.00, 75,
  'atomic_habits.jpg',
  4.8, 15432,
  '9780735211292', 'Avery', 2018, 320, 'English',
  1, 1, 1
),
(
  'Think and Grow Rich',
  'Napoleon Hill',
  'Self Help',
  'The landmark bestseller now revised and updated for the 21st century. Distilled from 500 interviews with successful people, this timeless classic reveals the secret that has made millionaires: a burning desire backed by definite plans and a mastermind alliance.',
  299.00, 399.00, 80,
  'think_grow.jpg',
  4.5, 9876,
  '9781585424337', 'TarcherPerigee', 2005, 320, 'English',
  0, 1, 0
),

-- ── Cybersecurity (2 books) ───────────────────────────────
(
  'The Web Application Hacker\'s Handbook',
  'Dafydd Stuttard & Marcus Pinto',
  'Cybersecurity',
  'Finding and exploiting security flaws in web applications. This comprehensive guide covers every major attack surface from authentication and session management to SQL injection, XSS, CSRF and beyond. An essential reference for every security professional and developer.',
  799.00, 999.00, 22,
  'web_hacker.jpg',
  4.7, 2109,
  '9781118026472', 'Wiley', 2011, 912, 'English',
  0, 0, 0
),
(
  'Hacking: The Art of Exploitation',
  'Jon Erickson',
  'Cybersecurity',
  'Get inside the mind of a hacker. Instead of just learning techniques, you will learn to think creatively about security problems. This book dives deep into C programming, shellcode, buffer overflows, format strings, and network exploits with a live bootable CD for practice.',
  699.00, 899.00, 30,
  'hacking_art.jpg',
  4.6, 1876,
  '9781593271442', 'No Starch Press', 2008, 488, 'English',
  1, 0, 1
);

-- ── Verify insert ──────────────────────────────────────────
SELECT
  id,
  CONCAT(LEFT(title,30),'...') AS title,
  category,
  price,
  stock,
  is_featured   AS feat,
  is_bestseller AS best,
  is_new_arrival AS new_arr,
  cover_image
FROM books
ORDER BY id;

SELECT
  CONCAT('Total books: ', COUNT(*))              AS summary FROM books
UNION ALL
SELECT CONCAT('Featured: ',    COUNT(*)) FROM books WHERE is_featured    = 1
UNION ALL
SELECT CONCAT('Bestsellers: ', COUNT(*)) FROM books WHERE is_bestseller  = 1
UNION ALL
SELECT CONCAT('New Arrivals: ',COUNT(*)) FROM books WHERE is_new_arrival = 1;

# BookVerse

BookVerse is a full-stack online bookstore platform that enables users to discover, browse, purchase, and manage books through a modern web interface. The platform provides customers with a seamless shopping experience while offering administrators complete control over inventory, orders, and payment records.

---

## Features

### User Authentication

* User Registration
* User Login
* Logout
* Session Management
* Password Hashing using Bcrypt
* Remember Me functionality using Cookies

### Book Catalog

* Browse all books
* Search by Title
* Search by Author
* Search by Category
* View detailed book information
* Ratings and Reviews display

### Shopping Cart

* Add books to cart
* Remove books from cart
* Update quantities
* Calculate total amount automatically

### Checkout System

* Shipping information collection
* Multiple payment methods
* Automatic order generation
* Order confirmation

### Order Management

* View order history
* Track order status
* View purchased items

### User Profile

* Update personal information
* Change password
* Upload profile picture

### Admin Panel

* Dashboard Statistics
* Add New Books
* Update Book Details
* Delete Books
* Manage Orders
* Manage Inventory
* View Registered Users

---

## Technology Stack

### Frontend

* HTML5
* CSS3
* JavaScript

### Backend

* PHP

### Database

* MySQL

### Server Environment

* XAMPP

---

## Database Design

### Users Table

Stores customer and administrator information.

Fields:

* id
* name
* email
* password
* phone
* address
* profile_picture
* role
* created_at

### Books Table

Stores book details.

Fields:

* id
* title
* author
* category
* description
* price
* stock
* cover_image
* rating
* isbn
* publisher
* publish_year

### Cart Table

Stores user cart items.

Fields:

* id
* user_id
* book_id
* quantity

### Orders Table

Stores customer orders.

Fields:

* id
* order_id
* user_id
* total_amount
* payment_method
* status

### Order Items Table

Stores books belonging to each order.

Fields:

* id
* order_id
* book_id
* quantity
* unit_price
* subtotal

### Payments Table

Stores payment information.

Fields:

* id
* order_id
* user_id
* amount
* payment_method
* status

---

## Project Structure

```text
BookVerse/
│
├── index.php
├── login.php
├── register.php
├── books.php
├── book_details.php
├── cart.php
├── checkout.php
├── orders.php
├── profile.php
├── admin.php
├── logout.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── uploads/
│
├── includes/
│   ├── db.php
│   ├── auth.php
│   ├── functions.php
│   └── book_card.php
│
└── database/
    └── bookverse.sql
```

---

## Authentication Flow

1. User registers an account.
2. Password is hashed using PASSWORD_BCRYPT.
3. User logs in using email and password.
4. Session is created upon successful authentication.
5. Protected pages require active login sessions.
6. Logout destroys sessions and cookies.

---

## Cart and Checkout Flow

1. User browses books.
2. Books are added to cart.
3. Cart calculates total price.
4. User proceeds to checkout.
5. Shipping and payment details are collected.
6. Order ID is generated automatically.

Example:

```text
BV2026001
```

7. Order details are stored in:

   * Orders Table
   * Order Items Table
   * Payments Table

8. Cart is cleared after successful order placement.

---

## Security Features

* Password Hashing
* Prepared Statements
* Input Validation
* Session Management
* Cookie Handling
* SQL Injection Prevention
* Authentication Middleware

---

  
 

# E-commerce Website Project Documentation

## Overview
This is a simple e-commerce website built with PHP, MySQL, and Bootstrap. It allows users to browse products, search, filter by category, add items to a cart, register/login, and checkout. There is also an admin dashboard for managing products, orders, and users.

---

## Tools & Technologies Used
- **PHP**: Server-side scripting language for backend logic.
- **MySQL**: Relational database to store products, categories, users, and orders.
- **Bootstrap 5**: For responsive and modern UI.
- **HTML/CSS/JavaScript**: For front-end structure and interactivity.
- **phpMyAdmin** (optional): For easy database management.

---

## Project Structure
```
/htdocs/ecommerce_Page/
├── admin.php               # Admin dashboard (manage products, orders, users)
├── assets/images/          # Product images
├── cart.php                # Shopping cart page
├── category_setup.sql      # SQL script for categories
├── db.php                  # Database connection
├── index.php               # Homepage
├── judging_tool_completed.txt # Judging checklist
├── login.php               # Login page
├── order_history.php       # User order history
├── product.php             # Product listing, search, filter
├── product_details.php     # Single product page
├── recover.php             # Password recovery
├── register.php            # User registration
└── ...
```

---

## Step-by-Step Setup & Usage

### 1. **Database Setup**
- Create a MySQL database (e.g., `ecommerce_db`).
- Run the SQL in `category_setup.sql` to create `categories` and add a `category_id` to `products`.
- Create the `users` table:
  ```sql
  CREATE TABLE users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(100) NOT NULL,
      email VARCHAR(255) NOT NULL UNIQUE,
      password VARCHAR(255) NOT NULL
  );
  ```
- Create the `products` table (if not present):
  ```sql
  CREATE TABLE products (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(100) NOT NULL,
      description TEXT,
      price DECIMAL(10,2) NOT NULL,
      image VARCHAR(255),
      category_id INT
  );
  ```
- (Optional) Create `orders` and other tables as needed.
- Insert sample categories and products.

### 2. **Configuration**
- Set your database credentials in `db.php`:
  ```php
  $host = "localhost";
  $user = "your_db_user";
  $pass = "your_db_password";
  $dbname = "ecommerce_db";
  $conn = mysqli_connect($host, $user, $pass, $dbname);
  ```

### 3. **Homepage (`index.php`)**
- Shows a welcome message, featured products, and navigation bar.
- Navbar includes Home, Products (dropdown with categories, opens full products page on click), Login, Cart, and Order History (if logged in).

### 4. **Product Listing (`product.php`)**
- Displays all products, grouped by category.
- Features search, price filter, and sort options.
- Each product links to its details page.
- Social sharing buttons included.
- Navbar dropdown lets you filter by category.

### 5. **Product Details (`product_details.php`)**
- Shows full product info, image, price, and Add to Cart button.
- Social sharing buttons.
- If not logged in, prompts user to log in or register before adding to cart.

### 6. **Cart (`cart.php`)**
- Shows cart contents, allows quantity update, remove, and clear cart.
- Displays total and checkout form (shipping/payment options).
- Requires login to checkout.

### 7. **User Authentication**
- **register.php**: User registration (username, email, password).
- **login.php**: User login form.
- **recover.php**: Password recovery (demo: shows message, does not send email).
- User session is used to track login state and cart.

### 8. **Order History (`order_history.php`)**
- Shows logged-in users their past orders (requires `orders` table and logic).

### 9. **Admin Dashboard (`admin.php`)**
- Placeholder for managing products, orders, customers, and inventory.
- Extend as needed for full admin CRUD operations.

---

## Custom Features & UX
- **Navbar Products Dropdown**: Opens on hover, clicking 'Products' goes to full product list.
- **Cart Icon**: Shows item count if cart is not empty.
- **Responsive Design**: Works on desktop and mobile.
- **Auth Modal**: Prompts guest users to log in/register for protected actions.
- **Social Sharing**: Share products via Facebook, Twitter, WhatsApp.

---

## How It All Works (Flow)
1. **User visits homepage**: Sees featured products and navigation.
2. **Browses products**: Uses dropdown or full products page, can search/filter/sort.
3. **Views product**: Can see details, share, and add to cart (if logged in).
4. **Manages cart**: Updates/removes/clears items, proceeds to checkout (login required).
5. **Registers or logs in**: To purchase and view order history.
6. **Admin**: Can manage products, orders, categories (extend admin.php for full CRUD).

---

## Extending the Project
- Add product CRUD (admin panel)
- Add order processing/payment integration
- Add email notifications
- Improve security (input validation, CSRF protection, etc.)
- Enhance UI/UX with more Bootstrap features

---

## Getting Started (Summary)
1. Clone/download the project.
2. Set up your MySQL database and import the provided SQL schemas.
3. Configure `db.php`.
4. Place product images in `assets/images/`.
5. Start your XAMPP/LAMPP server and open `index.php` in your browser.
6. Register a user, log in, and test all features.

---

## Support
If you get stuck, check the code comments, review this README, or reach out to your instructor/team lead.

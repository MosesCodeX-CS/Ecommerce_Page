-- Consolidated SQL setup for ecommerce_Page
-- Run in phpMyAdmin as needed (you can execute section-by-section)

START TRANSACTION;

-- 1) Core reference tables
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

-- 2) Products table adjustments (id/name/price/image/description are assumed to already exist)
-- Add product category_id and stock quantity if missing
ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT NULL;
ALTER TABLE products ADD COLUMN IF NOT EXISTS quantity INT DEFAULT 0;

-- (Optional) Foreign key for category → ensure products.category_id references categories.id
-- You may need to drop an existing FK with another name if present
-- ALTER TABLE products
--   ADD CONSTRAINT fk_products_category
--   FOREIGN KEY (category_id) REFERENCES categories(id)
--   ON UPDATE CASCADE ON DELETE SET NULL;

-- 3) Users table (for authentication and roles)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') DEFAULT 'user'
);

-- 4) Orders table (simple per-item rows)
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  date DATETIME NOT NULL
);

COMMIT;

-- =====================================================================
-- DATA MANAGEMENT SCRIPTS (run selectively as needed)
-- =====================================================================

-- A) CLEAR ALL PRODUCTS AND CATEGORIES (fresh start)
-- WARNING: This removes all products and categories
-- DELETE FROM products;
-- DELETE FROM categories;
-- ALTER TABLE products AUTO_INCREMENT = 1;

-- B) INSERT PROFESSIONAL CATEGORIES
INSERT INTO categories (name) VALUES 
  ('Electronics'),
  ('Clothing & Fashion'),
  ('Books & Media'),
  ('Home & Kitchen'),
  ('Sports & Fitness'),
  ('Beauty & Health'),
  ('Automotive'),
  ('Toys & Games')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- C) SAMPLE PRODUCTS (map to existing images in assets/images)
-- Adjust prices as needed. Uses images: phone.jpg, laptop.jpg, headphones.jpg
INSERT INTO products (name, description, price, image, category_id, quantity) VALUES 
  ('iPhone 15 Pro', 'Latest Apple smartphone with advanced camera system', 120000.00, 'phone.jpg', 1, 10),
  ('MacBook Air M2', 'Powerful laptop for work and creativity', 150000.00, 'laptop.jpg', 1, 8),
  ('Sony WH-1000XM5', 'Premium noise-cancelling headphones', 45000.00, 'headphones.jpg', 1, 15),
  ('Samsung Galaxy S24', 'Android flagship with AI features', 95000.00, 'phone.jpg', 1, 12)
ON DUPLICATE KEY UPDATE
  description = VALUES(description),
  price = VALUES(price),
  image = VALUES(image),
  category_id = VALUES(category_id),
  quantity = VALUES(quantity);

-- D) QUICK QUERIES FOR AUDIT / CLEANUP
-- Find uncategorized products
-- SELECT id, name FROM products WHERE category_id IS NULL OR category_id = 0;

-- Assign default category (Electronics) to uncategorized
-- UPDATE products SET category_id = 1 WHERE category_id IS NULL OR category_id = 0;

-- List categories
-- SELECT * FROM categories ORDER BY name;

-- Verify products mapped to category names
-- SELECT p.id, p.name, p.price, c.name AS category
-- FROM products p LEFT JOIN categories c ON p.category_id = c.id
-- ORDER BY c.name, p.name;



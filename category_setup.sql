      3>@za-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Add category_id to products table
ALTER TABLE products ADD COLUMN category_id INT;

-- Example: Insert some categories
INSERT INTO categories (name) VALUES ('Electronics'), ('Clothing'), ('Books'), ('Home & Kitchen');

-- Example: Assign category_id to products (update these as appropriate)
-- UPDATE products SET category_id = 1 WHERE id = ...;
-- UPDATE products SET category_id = 2 WHERE id = ...;
-- etc.

-- E-Commerce Product Catalog System
-- Import via phpMyAdmin (Import tab) or run in the SQL tab

CREATE DATABASE IF NOT EXISTS ecommerce_catalog
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ecommerce_catalog;

-- ---------- Tables ----------
CREATE TABLE IF NOT EXISTS categories (
  category_id   INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL UNIQUE,
  description   TEXT,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  product_id     INT AUTO_INCREMENT PRIMARY KEY,
  category_id    INT NOT NULL,
  product_name   VARCHAR(150) NOT NULL,
  description    TEXT,
  price          DECIMAL(10,2) NOT NULL,
  image          VARCHAR(255) DEFAULT 'no-image.png',
  stock_quantity INT DEFAULT 0,
  is_featured    TINYINT(1) DEFAULT 0,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_product_name (product_name),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_users (
  admin_id      INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Sample categories ----------
INSERT INTO categories (category_name, description) VALUES
('Electronics',     'Gadgets, audio devices and accessories'),
('Fashion',         'Clothing, footwear and accessories'),
('Books',           'Academic, fiction and self-help books'),
('Home & Kitchen',  'Appliances and everyday home essentials'),
('Sports & Fitness','Sports gear and fitness equipment');

-- ---------- Sample products ----------
INSERT INTO products (category_id, product_name, description, price, image, stock_quantity, is_featured) VALUES
(1, 'Wireless Bluetooth Headphones', 'Over-ear headphones with deep bass, 20-hour battery life and built-in microphone.', 2499.00, 'headphones.jpg', 25, 1),
(1, 'Smart Fitness Watch',           'Water-resistant smartwatch with heart-rate monitor, step counter and sleep tracking.', 3299.00, 'smartwatch.jpg', 18, 0),
(2, 'Men''s Cotton Casual Shirt',    'Slim-fit cotton shirt, breathable fabric, suitable for daily and office wear.', 899.00, 'shirt.jpg', 40, 1),
(2, 'Women''s Running Shoes',        'Lightweight running shoes with cushioned sole and anti-slip grip.', 1799.00, 'shoes.jpg', 30, 0),
(3, 'Learning PHP and MySQL',        'Beginner-friendly guide to building dynamic websites using PHP and MySQL.', 549.00, 'php-book.jpg', 50, 1),
(3, 'Atomic Habits',                 'A practical book on building good habits and breaking bad ones.', 399.00, 'atomic-habits.jpg', 60, 0),
(4, 'Electric Kettle 1.5L',          'Stainless steel electric kettle with auto shut-off and boil-dry protection.', 699.00, 'kettle.jpg', 35, 1),
(4, 'Non-Stick Cookware Set',        '3-piece non-stick cookware set suitable for gas and induction cooktops.', 1499.00, 'cookware.jpg', 20, 0),
(5, 'Yoga Mat 6mm',                  'Anti-slip, eco-friendly yoga mat with carrying strap.', 599.00, 'yoga-mat.jpg', 45, 1),
(5, 'Adjustable Dumbbell Set',       'Adjustable dumbbell set, 2 x 10 kg, for home workouts.', 2199.00, 'dumbbells.jpg', 15, 0);

-- Admin user is created later using a PHP script (password must be hashed).

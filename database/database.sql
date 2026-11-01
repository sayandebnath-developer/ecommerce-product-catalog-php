-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2026 at 03:52 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce_catalog`
--
CREATE DATABASE IF NOT EXISTS `ecommerce_catalog` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ecommerce_catalog`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'phpproject', '$2y$10$ekBNGcg1SpgVHpSCNl2YbOswFoD/AUHk.sQw5Z5R45xf3AYnJCXai', '2026-10-23 15:18:15');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`) VALUES
(1, 'Electronics', 'Gadgets, and accessories', '2026-10-21 12:32:36'),
(2, 'Fashion', 'Clothing, footwear and accessories', '2026-10-21 12:32:36'),
(3, 'Books', 'Academic, fiction and self-help books', '2026-10-21 12:32:36'),
(4, 'Home & Kitchen', 'Appliances and everyday home essentials', '2026-10-21 12:32:36'),
(5, 'Sports & Fitness', 'Sports gear and fitness equipment', '2026-10-21 12:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT 'no-image.png',
  `stock_quantity` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `image`, `stock_quantity`, `is_featured`, `created_at`) VALUES
(1, 1, 'Wireless Bluetooth Headphones', 'Over-ear headphones with deep bass, 20-hour battery life and built-in microphone.', 2499.00, 'product_20261029152712_a54e70a4012f.jpg', 25, 1, '2026-10-21 12:32:36'),
(2, 1, 'Smart Fitness Watch', 'Water-resistant smartwatch with heart-rate monitor, step counter and sleep tracking.', 3299.00, 'product_20261029152657_68c45003a3e2.jpg', 18, 0, '2026-10-21 12:32:36'),
(3, 2, 'Men\'s Cotton Casual Shirt', 'Slim-fit cotton shirt, breathable fabric, suitable for daily and office wear.', 899.00, 'product_20261029152557_7d2aa5fcc250.jpg', 40, 1, '2026-10-21 12:32:36'),
(4, 2, 'Women\'s Running Shoes', 'Lightweight running shoes with cushioned sole and anti-slip grip.', 1799.00, 'product_20261029152456_8b94de2ad7ad.jpg', 30, 0, '2026-10-21 12:32:36'),
(5, 3, 'Learning PHP and MySQL', 'Beginner-friendly guide to building dynamic websites using PHP and MySQL.', 549.00, 'product_20261029152337_81ee0540276b.jpg', 50, 1, '2026-10-21 12:32:36'),
(6, 3, 'Atomic Habits', 'A practical book on building good habits and breaking bad ones.', 399.00, 'product_20261029152321_c7123e513859.jpg', 6, 0, '2026-10-21 12:32:36'),
(7, 4, 'Electric Kettle 1.5L', 'Stainless steel electric kettle with auto shut-off and boil-dry protection.', 699.00, 'product_20261029152115_edaae4b4bad6.jpg', 35, 1, '2026-10-21 12:32:36'),
(8, 4, 'Non-Stick Cookware Set', '3-piece non-stick cookware set suitable for gas and induction cooktops.', 1499.00, 'product_20261029152029_4367e78d63cc.png', 0, 1, '2026-10-21 12:32:36'),
(9, 5, 'Yoga Mat 6mm', 'Anti-slip, eco-friendly yoga mat with carrying strap.', 599.00, 'product_20261029151749_d2fabc90e22f.jpg', 45, 1, '2026-10-21 12:32:36'),
(10, 5, 'Adjustable Dumbbell Set', 'Adjustable dumbbell set, 2 x 10 kg, for home workouts.', 2199.00, 'product_20261029151644_c9b8617553e4.jpg', 15, 1, '2026-10-21 12:32:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_product_name` (`product_name`),
  ADD KEY `fk_products_category` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

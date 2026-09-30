-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2026 at 03:17 AM
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
-- Database: `fnh_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Fruits', 'Fresh fruits'),
(2, 'Vegetables', 'Fresh vegetables'),
(3, 'Dairy', 'Milk and dairy products'),
(4, 'Bakery', 'Bread and bakery products'),
(5, 'Meat', 'Fresh meat'),
(7, 'Frozen Foods', 'Frozen grocery products');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `city` varchar(50) NOT NULL,
  `zip_code` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`) VALUES
(5, 'Beverages'),
(2, 'Dairy'),
(6, 'Frozen Foods'),
(3, 'Hadware'),
(4, 'Meat'),
(7, 'Pantry'),
(1, 'Produce');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_code`, `name`, `category_id`, `department_id`, `price`, `quantity`) VALUES
(1, '1234567', 'Adware', 7, 6, 32.00, 226),
(3, '12345670', 'voting system', 4, 3, 89.00, 220),
(6, '123456700', 'ded', 1, 2, 5.00, 45),
(7, '5555555', 'ded', 1, 6, 5.00, 28);

-- --------------------------------------------------------

--
-- Table structure for table `register`
--

CREATE TABLE `register` (
  `register_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `register_number` varchar(20) NOT NULL,
  `status` enum('Active','Maintenance','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `operator_id` int(10) UNSIGNED NOT NULL,
  `started_at` datetime NOT NULL,
  `checkout_at` datetime DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cash_tendered` decimal(10,2) DEFAULT NULL,
  `change_due` decimal(10,2) DEFAULT NULL,
  `status` enum('OPEN','COMPLETED','CANCELLED') NOT NULL DEFAULT 'OPEN',
  `cancelled_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `operator_id`, `started_at`, `checkout_at`, `subtotal`, `tax`, `total`, `cash_tendered`, `change_due`, `status`, `cancelled_at`) VALUES
(4, 1, '2026-09-27 12:05:11', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'OPEN', NULL),
(5, 1, '2026-09-27 12:34:07', '2026-09-27 13:52:26', 302.00, 23.41, 325.41, 330.00, 4.59, 'COMPLETED', NULL),
(6, 1, '2026-09-27 13:57:50', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'OPEN', NULL),
(7, 1, '2026-09-27 13:58:24', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'OPEN', NULL),
(8, 1, '2026-09-27 13:59:54', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'CANCELLED', '2026-09-27 14:07:35'),
(9, 1, '2026-09-27 14:07:38', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'CANCELLED', '2026-09-27 14:07:51'),
(10, 1, '2026-09-27 14:07:54', '2026-09-27 14:08:53', 5.00, 0.39, 5.39, 6.00, 0.61, 'COMPLETED', NULL),
(11, 1, '2026-09-27 14:09:10', '2026-09-27 14:09:25', 32.00, 2.48, 34.48, 35.00, 0.52, 'COMPLETED', NULL),
(12, 1, '2026-09-27 14:33:44', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'OPEN', NULL),
(13, 1, '2026-09-27 14:34:05', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'OPEN', NULL),
(14, 1, '2026-09-27 14:34:30', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'OPEN', NULL),
(15, 1, '2026-09-27 14:38:33', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'OPEN', NULL),
(16, 1, '2026-09-27 14:39:18', '2026-09-27 15:28:56', 5.00, 0.39, 5.39, 6.00, 0.61, 'COMPLETED', NULL),
(17, 1, '2026-09-27 15:33:02', '2026-09-27 15:44:18', 42.00, 3.26, 45.26, 100.00, 54.74, 'COMPLETED', NULL),
(18, 1, '2026-09-27 15:54:42', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'CANCELLED', '2026-09-27 15:55:19'),
(19, 1, '2026-09-27 15:55:21', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'CANCELLED', '2026-09-27 15:56:00'),
(20, 1, '2026-09-27 15:56:02', '2026-09-27 15:57:07', 10.00, 0.78, 10.78, 10.78, 0.00, 'COMPLETED', NULL),
(21, 1, '2026-09-27 16:08:36', NULL, 0.00, 0.00, 0.00, NULL, NULL, 'CANCELLED', '2026-09-27 16:36:06'),
(22, 1, '2026-09-27 16:36:08', '2026-09-27 16:36:55', 74.00, 5.74, 79.74, 80.00, 0.26, 'COMPLETED', NULL),
(23, 1, '2026-09-27 19:30:39', '2026-09-27 19:31:36', 99.00, 7.67, 106.67, 107.00, 0.33, 'COMPLETED', NULL),
(24, 1, '2026-09-27 19:31:59', '2026-09-27 19:37:32', 131.00, 10.15, 141.15, 142.00, 0.85, 'COMPLETED', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `line_total`) VALUES
(1, 4, 3, 5, 89.00, 445.00),
(2, 4, 1, 2, 32.00, 64.00),
(3, 4, 7, 1, 5.00, 5.00),
(4, 5, 7, 2, 5.00, 10.00),
(5, 5, 3, 3, 89.00, 267.00),
(6, 5, 6, 5, 5.00, 25.00),
(7, 6, 3, 1, 89.00, 89.00),
(8, 7, 7, 1, 5.00, 5.00),
(9, 8, 7, 1, 5.00, 5.00),
(10, 9, 3, 1, 89.00, 89.00),
(11, 9, 7, 1, 5.00, 5.00),
(12, 10, 7, 1, 5.00, 5.00),
(13, 11, 1, 1, 32.00, 32.00),
(14, 16, 7, 1, 5.00, 5.00),
(15, 17, 7, 1, 5.00, 5.00),
(16, 17, 6, 1, 5.00, 5.00),
(17, 17, 1, 1, 32.00, 32.00),
(18, 18, 6, 3, 5.00, 15.00),
(19, 18, 7, 1, 5.00, 5.00),
(20, 18, 3, 3, 89.00, 267.00),
(21, 18, 1, 7, 32.00, 224.00),
(22, 19, 6, 1, 5.00, 5.00),
(23, 19, 7, 1, 5.00, 5.00),
(24, 19, 3, 1, 89.00, 89.00),
(25, 20, 7, 1, 5.00, 5.00),
(26, 20, 6, 1, 5.00, 5.00),
(27, 21, 3, 1, 89.00, 89.00),
(28, 22, 6, 1, 5.00, 5.00),
(29, 22, 1, 2, 32.00, 64.00),
(30, 22, 7, 1, 5.00, 5.00),
(31, 23, 7, 2, 5.00, 10.00),
(32, 23, 3, 1, 89.00, 89.00),
(33, 24, 7, 1, 5.00, 5.00),
(34, 24, 6, 1, 5.00, 5.00),
(35, 24, 3, 1, 89.00, 89.00),
(36, 24, 1, 1, 32.00, 32.00);

-- --------------------------------------------------------

--
-- Table structure for table `store`
--

CREATE TABLE `store` (
  `store_id` int(11) NOT NULL,
  `store_name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(50) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store`
--

INSERT INTO `store` (`store_id`, `store_name`, `address`, `city`, `state`, `zip_code`, `created_at`) VALUES
(1, 'bld', '34 wilis street', 'Framingham', 'MA', '01702', '2026-09-22 19:35:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(30) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(80) NOT NULL,
  `email` varchar(200) NOT NULL,
  `role` enum('admin','operator') NOT NULL DEFAULT 'operator',
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$PJ75cU46Ys/QLfHO./H0LeOvk1FKgRrUOzQjB0lWrjVEaIOdmTZiu', 'Sandra Peter', 'petersandra@gmail.com', 'admin', 1, '2026-09-18 15:15:12'),
(13, 'sage1994', '$2y$10$haejlMxQkD29fm8Zk7Zlu.Onl057QQz1dLUFoRulJSa2cuU/8LKn6', 'Sagesse Joseph', 'sagejoseph12@gmail.com', 'operator', 1, '2026-09-26 10:27:33'),
(14, 'sergo123', '$2y$10$Ab3q9rFKqv6L9ds/M35Cw.g1S4kXfzTtmtidypRLkiB8FXeZmyso.', 'Sergo Jeans', 'jeanssergo234@gmail.com', 'operator', 1, '2026-09-27 20:53:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `register`
--
ALTER TABLE `register`
  ADD PRIMARY KEY (`register_id`),
  ADD KEY `store_id` (`store_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operator_id` (`operator_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `store`
--
ALTER TABLE `store`
  ADD PRIMARY KEY (`store_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `register`
--
ALTER TABLE `register`
  MODIFY `register_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `store`
--
ALTER TABLE `store`
  MODIFY `store_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_departments` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `register`
--
ALTER TABLE `register`
  ADD CONSTRAINT `fk_register_store` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_users` FOREIGN KEY (`operator_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `fk_items_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_items_sales` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

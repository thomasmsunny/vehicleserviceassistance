-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 12, 2025 at 12:35 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vehicleservice`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminlogin`
--

DROP TABLE IF EXISTS `adminlogin`;
CREATE TABLE IF NOT EXISTS `adminlogin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `adminlogin`
--

INSERT INTO `adminlogin` (`admin_id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `vehicle_make` varchar(100) NOT NULL,
  `vehicle_model` varchar(100) NOT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `location_link` varchar(255) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `tow_service` tinyint(1) DEFAULT '0',
  `customer_notes` text,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `driver_id` int DEFAULT NULL,
  `quotation` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `customer_id`, `vehicle_make`, `vehicle_model`, `vehicle_number`, `location_link`, `service_type`, `tow_service`, `customer_notes`, `booking_date`, `booking_time`, `created_at`, `status`, `driver_id`, `quotation`) VALUES
(1, 9, 'toyota', 'camery', 'KL 35 C 1175', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Periodic Service', 0, '', '2025-10-21', '13:15:00', '2025-10-08 13:50:02', 'Delivered', 4, NULL),
(2, 9, 'toyota', 'camery', 'KL 22 B 9505', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Full Service', 0, '', '2025-10-05', '09:00:00', '2025-10-08 13:51:56', 'Delivered', NULL, NULL),
(3, 9, 'toyota', 'camery', 'KL 35 C 4575', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Waxing', 1, 'check the breaks', '2025-10-08', '09:00:00', '2025-10-08 16:24:09', 'Quoted', NULL, 0.00),
(5, 9, 'toyota', 'camery', 'KL 22 B 8505', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Periodic Service', 0, '', '2025-10-21', '13:15:00', '2025-10-08 13:50:02', 'Payment done', 3, NULL),
(6, 10, 'honda', 'AMAZE', 'KL 24 CA 3457', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Graphine Coating', 1, 'CHANGE BRAEK PAD', '2025-10-12', '09:00:00', '2025-10-12 17:41:23', 'Delivered', NULL, NULL),
(8, 10, 'honda', 'AMAZE', 'KL 24 CA 3457', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Graphine Coating', 1, 'CHANGE BRAEK PAD', '2025-10-12', '09:00:00', '2025-10-12 17:47:01', 'Delivered', NULL, NULL),
(9, 11, 'toyota', 'hycross', 'KL-29-B-6346', 'https://maps.app.goo.gl/gSB7AceKpEf3im4Q8', 'Waxing', 1, '9==', '2025-10-24', '04:18:00', '2025-10-13 18:44:06', 'Payment done', 3, NULL),
(10, 10, 'lexus', 'l2', 'KL-24-CA-3457', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Full Service', 1, 'paint correction needed', '2025-10-31', '23:23:00', '2025-10-19 12:50:24', 'Delivered', 3, 0.00),
(11, 10, 'suzuki', 'swift', 'KL-24-CC-3457', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Ceramic Coating', 0, '', '2025-10-30', '12:26:00', '2025-10-19 15:41:34', 'Delivered', 4, 0.00),
(12, 10, 'BMW', 'X5', 'KL-24-CC-0001', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Graphine Coating', 0, 'check break fluids', '2025-10-30', '13:30:00', '2025-10-21 16:57:01', 'Payment Done', NULL, 0.00),
(13, 10, 'Mercides', 's class', 'KL-24-CC-0009', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Washing', 1, 'TYRE CHANGE', '2025-10-31', '16:45:00', '2025-10-21 17:13:50', 'Delivered', 4, 0.00),
(14, 10, 'honda', 'city', 'KL-24-CC-0001', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Full Service', 0, '', '2025-10-21', '01:25:00', '2025-10-21 17:55:09', 'Payment Done', NULL, 0.00),
(16, 15, 'BYD ', 'Atto 3', 'KL 35 C 1234', 'https://maps.app.goo.gl/wGkCMXw4fowqxjEm6', 'Graphine Coating', 0, 'Replace the air filter', '2025-11-11', '15:00:00', '2025-10-26 05:45:17', 'Delivered', 4, 0.00),
(15, 12, 'Rolls Royce', 'Ghost', 'KL-13-KA-1313', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Graphine Coating', 0, '', '2025-10-31', '14:00:00', '2025-10-25 12:29:24', 'Delivered', 3, 0.00),
(17, 15, 'BYD ', 'Atto 3', 'KL 35 C 1234', 'https://maps.app.goo.gl/wGkCMXw4fowqxjEm6', 'Graphine Coating', 0, 'Replace the air filter', '2025-11-11', '15:00:00', '2025-10-26 05:45:49', 'Pay Now', 3, 0.00),
(18, 16, 'honda', 'city', 'KL 45 L 1326', 'https://maps.app.goo.gl/udWiXRbcbx9vtqEC6', 'Serv', 0, 'askasdk;as', '2025-10-30', '12:10:00', '2025-10-27 06:24:01', 'Payment Done', 5, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `replied_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`, `replied_at`) VALUES
(1, 'tomas', 'thomasalways20011@gmail.com', 'service', 'is it open on sunday', 'read', '2025-10-27 02:35:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customerreg`
--

DROP TABLE IF EXISTS `customerreg`;
CREATE TABLE IF NOT EXISTS `customerreg` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customerreg`
--

INSERT INTO `customerreg` (`customer_id`, `firstname`, `lastname`, `email`, `phone`, `password`, `status`, `created_at`) VALUES
(1, 'Tony', ' john', 'tony2001@gmail.com', '9876543210', '$2y$10$OAje0zDajwqOlz3XPS60Z.wH.yzKlfNw2bQ4Ub/6FEcjOjonDeij6', 1, '2025-10-07 17:17:23'),
(7, 'gokul', 'krishna', 'gokul2001@gmail.com', '1234567899', '$2y$10$UHSaq5LDtvqcMwRpNbTDV.kVMZo8cpObqaOX5JEb1Rx4uM0UxZynW', 1, '2025-10-07 18:31:55'),
(9, 'thomas', 'mathew', 'thomasalways2001@gmail.com', '9876543210', '$2y$10$iO6N81Wg2CJ/yYRE0q0G0.6U8i7sGLnW0eY.xzXk0Bzd0GHvepewG', 1, '2025-10-08 12:11:39'),
(10, 'gokul', 'krishna ku', 'gokul2002@gmail.com', '9539735698', '$2y$10$8I8cpzySjYpkGt/JWqT4C.FIWoMHYFjFfZsKLw0JlRhjfpNMz1sqC', 1, '2025-10-10 13:52:04'),
(11, 'gokul', 'krishna', 'gokulkrishna2001@gmail.com', '06532456984', '$2y$10$DD7itKYXDJZp7YGlTNNepOb9rYsCstkvh1OHgY0kLXlyY37ZEvfDq', 1, '2025-10-13 16:15:08'),
(12, 'kiran', '', 'kiran1313@gmail.com', '9539735698', '$2y$10$Y1r5hFHpPCEAVi.dCiaVQ.slU/nBmWg469rMrfzAuWbFdYin4y69e', 1, '2025-10-25 12:25:41'),
(13, 'Test', 'User', 'test@test.com', '1234567890', '$2y$10$Z1zICNXl6rOBfhndzuO5YOZcZ1pkodFXOGzQiLrbWdyx2ejoWFZz2', 1, '2025-10-25 19:03:12'),
(14, 'Test', 'User', 'test.registration@example.com', '1234567890', '$2y$10$n/qb4uDp566rPIEVtVTelesS/GuMVhHoOxq94ZabHp13BU0mrYMwK', 1, '2025-10-25 21:13:16'),
(15, 'athul', 'sajeev', 'athul100@gmail.com', '8129883397', '$2y$10$/E1kouAkAIlhigNsligm9.PVLJGNDSmu5iE/38lfX1NvEy8syFf.W', 1, '2025-10-26 05:29:20'),
(16, 'cust', '', 'cust@gmail.com', '1234567890', '$2y$10$pXpNvlYVbk.KZ.szJS/kCuCuLQcZp9DyqGjMIiRmESZCiWNdoGOyS', 1, '2025-10-27 06:14:53');

-- --------------------------------------------------------

--
-- Table structure for table `drivermanage`
--

DROP TABLE IF EXISTS `drivermanage`;
CREATE TABLE IF NOT EXISTS `drivermanage` (
  `did` int NOT NULL AUTO_INCREMENT,
  `drivername` varchar(30) NOT NULL,
  `phone` char(10) NOT NULL,
  `email` varchar(25) NOT NULL,
  `password` varchar(255) NOT NULL,
  `license_no` varchar(50) NOT NULL,
  `address` text,
  `status` enum('available','assigned','inactive') DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`did`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `drivermanage`
--

INSERT INTO `drivermanage` (`did`, `drivername`, `phone`, `email`, `password`, `license_no`, `address`, `status`, `created_at`) VALUES
(5, 'Driver', '1234567890', 'driver@gmail.com', '$2y$10$OQ4sW8J5F7xvqgZZ.Y6geeqOBHcoBp0cSsb8cCf71SUz7raVVHHi6', '123456', 'bxcbkzxmc', 'available', '2025-10-27 06:13:35'),
(3, 'thomas ', '0245689359', 'thomasalways2001@gmail.co', '', '0987654321', 'mattel house angamali', 'available', '2025-10-20 15:38:24'),
(4, 'Kiran ks', '8129883397', 'kiran123@gmail.com', '$2y$10$aHQFz.N2qm47nRkjz/4Qu.HmTnIvvacz28WrYGZjiT/k8evyRjNL6', '0987654321', 'marangattu aluva', 'available', '2025-10-20 16:26:38');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `feedback_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `feedback_text` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`),
  KEY `customer_id` (`customer_id`)
) ;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `customer_id`, `booking_id`, `rating`, `feedback_text`, `created_at`) VALUES
(1, 10, 0, 4, 'good service', '2025-10-11 08:15:35'),
(2, 10, 0, 5, 'excellent performance', '2025-10-11 08:15:56'),
(3, 10, 0, 4, 'good service', '2025-10-11 08:16:31'),
(4, 10, 0, 3, '', '2025-10-16 20:18:41'),
(5, 10, 0, 3, '', '2025-10-16 20:20:32'),
(6, 10, 0, 5, 'best service ever', '2025-10-16 20:20:47'),
(7, 15, 0, 3, 'good experience', '2025-10-26 08:30:38');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT 'Success',
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `booking_id` (`booking_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `booking_id`, `customer_id`, `amount_paid`, `payment_method`, `transaction_id`, `payment_status`, `payment_date`) VALUES
(1, 13, 10, 35400.00, 'card', NULL, 'Success', '2025-10-21 22:45:57'),
(2, 14, 10, 1416.00, 'upi', NULL, 'Success', '2025-10-21 23:57:27'),
(3, 12, 10, 118000.00, 'upi', NULL, 'Success', '2025-10-22 00:17:46'),
(4, 15, 12, 590000.00, 'card', NULL, 'Success', '2025-10-25 18:04:12'),
(5, 17, 15, 10030.00, 'card', NULL, 'Success', '2025-10-26 13:15:57'),
(6, 17, 15, 10030.00, 'card', NULL, 'Success', '2025-10-26 13:53:11');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

DROP TABLE IF EXISTS `quotations`;
CREATE TABLE IF NOT EXISTS `quotations` (
  `quote_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `quote_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `cgst_rate` decimal(5,2) DEFAULT '0.00',
  `sgst_rate` decimal(5,2) DEFAULT '0.00',
  `total_tax` decimal(10,2) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `other_works_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`quote_id`),
  UNIQUE KEY `booking_id` (`booking_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`quote_id`, `booking_id`, `admin_name`, `quote_date`, `subtotal`, `discount`, `cgst_rate`, `sgst_rate`, `total_tax`, `grand_total`, `other_works_notes`, `created_at`) VALUES
(1, 3, 'tom', '2025-10-19', 345.00, 0.00, 9.00, 9.00, 62.10, 407.10, '', '2025-10-19 12:41:46'),
(2, 10, 'tomy', '2025-10-19', 24500.00, 0.00, 9.00, 9.00, 4410.00, 28910.00, '', '2025-10-19 12:51:03'),
(3, 11, 'tom', '2025-10-19', 35000.00, 250.00, 9.00, 9.00, 6255.00, 41005.00, '', '2025-10-19 15:44:53'),
(4, 12, 'admin', '2025-10-21', 100000.00, 0.00, 9.00, 9.00, 18000.00, 118000.00, '', '2025-10-21 16:58:06'),
(5, 13, 'admin', '2025-10-21', 30000.00, 0.00, 9.00, 9.00, 5400.00, 35400.00, '', '2025-10-21 17:14:43'),
(6, 14, 'tom', '2025-10-21', 1200.00, 0.00, 9.00, 9.00, 216.00, 1416.00, '', '2025-10-21 17:59:25'),
(7, 7, 'admin', '2025-10-24', 50000.00, 0.00, 9.00, 9.00, 9000.00, 59000.00, '', '2025-10-24 14:28:09'),
(8, 4, 'tom', '2025-10-24', 10000.00, 0.00, 9.00, 9.00, 1800.00, 11800.00, '', '2025-10-24 15:22:58'),
(9, 15, 'admin', '2025-10-25', 500000.00, 0.00, 9.00, 9.00, 90000.00, 590000.00, '', '2025-10-25 12:33:06'),
(10, 16, 'john', '2025-10-26', 7500.00, 0.00, 9.00, 9.00, 1350.00, 8850.00, '', '2025-10-26 06:00:27'),
(11, 17, 'tom', '2025-10-26', 8500.00, 0.00, 9.00, 9.00, 1530.00, 10030.00, '', '2025-10-26 07:06:36');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

DROP TABLE IF EXISTS `quotation_items`;
CREATE TABLE IF NOT EXISTS `quotation_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `item_quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `item_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`item_id`, `booking_id`, `item_description`, `item_quantity`, `unit_price`, `item_total`) VALUES
(22, 3, 'waxing ', 1, 345.00, 345.00),
(21, 10, 'waxing ', 1, 24500.00, 24500.00),
(30, 11, 'oil change', 1, 35000.00, 35000.00),
(23, 12, 'oil change', 1, 100000.00, 100000.00),
(24, 13, 'oil change', 1, 30000.00, 30000.00),
(25, 14, 'OIL', 1, 1200.00, 1200.00),
(31, 7, 'OIL', 1, 50000.00, 50000.00),
(33, 4, 'pain correcion', 1, 10000.00, 10000.00),
(34, 15, 'graphics', 1, 500000.00, 500000.00),
(35, 16, 'air filter', 1, 7500.00, 7500.00),
(37, 17, 'oil', 1, 8500.00, 8500.00);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `sid` int NOT NULL AUTO_INCREMENT,
  `servicename` varchar(20) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`sid`, `servicename`, `description`, `image`, `status`) VALUES
(9, 'Periodic service', 'Periodic services are regular maintenance check-ups done on a vehicle after a specific time or distance (like every 5,000 km or 6 months). These services help keep the vehicle in good condition and avoid sudden breakdowns.', '68fef783cb159.jpeg', 'active'),
(10, 'Major service', 'A major service is a detailed, full check-up of the vehicle done after a certain time or mileage (like every 10,000–20,000 km or once a year). It helps keep the vehicle safe, smooth, and long-lasting.\r\n', '68fef82c2c76a.jpeg', 'active'),
(13, 'Denting And Painting', 'Denting and painting services restore your vehicle’s original look by repairing damaged body parts and giving them a fresh, smooth paint finish.', '68fefbaef20be.jpg', 'active'),
(14, 'Serv', 'mnmxnmnbbmn', '', 'active'),
(11, 'Ceramic coating', 'Ceramic coating is a protective layer applied to your vehicle’s paint to keep it shiny, smooth, and safe from damage. It bonds with the paint surface and creates a strong, long-lasting shield.\r\n\r\n✅ Benefits of Ceramic Coating\r\nHigh Gloss & Shine – Gives your car a showroom-like finish.\r\nProtection from UV Rays & Weather – Prevents paint fading and oxidation.\r\nWater & Dirt Repellent – Water beads off easily, making cleaning simple.', '68fef91267cb7.jpeg', 'active'),
(12, 'Graphine coating', 'Graphene coating is an advanced paint protection technology that offers stronger, longer-lasting protection than traditional ceramic coatings. It uses graphene, a highly durable and flexible material, to create a shield over your vehicle’s paint.\r\n\r\n✅ Benefits of Graphene Coating\r\n\r\nSuperior Durability – Lasts longer than ceramic coating (up to 5+ years).\r\n\r\nHeat & UV Resistance – Protects paint from sun damage and high temperatures.', '68fefa89244e4.jpg', 'active');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

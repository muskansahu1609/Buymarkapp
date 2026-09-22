-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2026 at 06:21 AM
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
-- Database: `buymark`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_table`
--

CREATE TABLE `admin_table` (
  `admin_id` int(10) UNSIGNED NOT NULL,
  `admin_username` varchar(100) NOT NULL,
  `admin_email` varchar(190) NOT NULL,
  `admin_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_table`
--

INSERT INTO `admin_table` (`admin_id`, `admin_username`, `admin_email`, `admin_password`) VALUES
(1, 'Rahul Sahu', 'sahurahulkumar802@gmail.com', '$2y$10$.LhCWsAEDH9sdm81Z/iwg.FFGDc5xeJv4YBh0CaCRpWsOHO3gRvEi');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(10) UNSIGNED NOT NULL,
  `brand_title` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_title`) VALUES
(1, 'Buymark'),
(2, 'Fashion Hub'),
(3, 'Urban Style'),
(4, 'Classic Wear'),
(5, 'Trend Store');

-- --------------------------------------------------------

--
-- Table structure for table `cart_details`
--

CREATE TABLE `cart_details` (
  `cart_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ip_address` varchar(45) NOT NULL,
  `cart_owner` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size_name` varchar(50) DEFAULT NULL,
  `color_name` varchar(100) DEFAULT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_details`
--

INSERT INTO `cart_details` (`cart_id`, `product_id`, `product_price`, `total_price`, `ip_address`, `cart_owner`, `quantity`, `size_name`, `color_name`, `variant_id`) VALUES
(3, 6, 1499.00, 1499.00, '::1', 'guest:vg7verq99u2cic6s13bgi7q42n', 1, NULL, NULL, NULL),
(4, 6, 1499.00, 1499.00, '::1', 'guest:p98imv82705h6g7h8bi36hij33', 1, NULL, NULL, NULL),
(5, 2, 899.00, 899.00, '::1', 'guest:p98imv82705h6g7h8bi36hij33', 1, NULL, NULL, NULL),
(8, 3, 1299.00, 1299.00, '::1', 'guest:fto26v9eldpm0l458enu0hg861', 1, 'S', NULL, NULL),
(9, 1, 799.00, 1598.00, '::1', 'guest:eu0j2bqv3um5q9hkd073dat3c8', 2, NULL, NULL, NULL),
(10, 13, 349.00, 349.00, '::1', 'guest:eu0j2bqv3um5q9hkd073dat3c8', 1, 'S', NULL, NULL),
(11, 8, 399.00, 399.00, '::1', 'guest:eu0j2bqv3um5q9hkd073dat3c8', 1, NULL, NULL, NULL),
(12, 5, 599.00, 599.00, '10.137.158.124', 'user:1', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `category_title` varchar(150) NOT NULL,
  `category_image_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_title`, `category_image_name`) VALUES
(1, 'Men', 'category-1.jpg'),
(2, 'Women', 'category-2.jpg'),
(3, 'Kids', 'category-3.jpg'),
(4, 'Shoes', 'category-4.jpg'),
(5, 'Bags', 'category-5.jpg'),
(6, 'Accessories', 'category-8.jpg'),
(7, 'Fashion', 'showcase-img-3.jpg'),
(8, 'New Arrivals', 'showcase-img-5.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `compare_details`
--

CREATE TABLE `compare_details` (
  `compare_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `user_ip` varchar(45) NOT NULL,
  `product_title` varchar(255) DEFAULT NULL,
  `product_discription` text DEFAULT NULL,
  `product_keyword` varchar(500) DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `brand_id` int(10) UNSIGNED DEFAULT NULL,
  `product_image1` varchar(255) DEFAULT NULL,
  `product_price` decimal(12,2) DEFAULT 0.00,
  `Stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `address_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `house` varchar(160) DEFAULT NULL,
  `street` varchar(160) DEFAULT NULL,
  `landmark` varchar(160) DEFAULT NULL,
  `city` varchar(80) NOT NULL,
  `state` varchar(80) DEFAULT NULL,
  `pincode` varchar(12) DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'Home',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletters`
--

CREATE TABLE `newsletters` (
  `newsletter_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `subscribed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(40) NOT NULL,
  `title` varchar(160) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `reference_type` varchar(40) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_panding`
--

CREATE TABLE `orders_panding` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `invoice_number` bigint(20) NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `order_status` varchar(50) NOT NULL DEFAULT 'panding',
  `size_name` varchar(50) DEFAULT NULL,
  `color_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders_panding`
--

INSERT INTO `orders_panding` (`order_id`, `user_id`, `invoice_number`, `product_id`, `quantity`, `order_status`, `size_name`, `color_name`) VALUES
(1, 1, 111109943, 10, 1, 'panding', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_title` varchar(255) NOT NULL,
  `product_discription` text DEFAULT NULL,
  `product_keyword` varchar(500) DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `brand_id` int(10) UNSIGNED DEFAULT NULL,
  `shop_id` int(10) UNSIGNED DEFAULT NULL,
  `product_image1` varchar(255) DEFAULT NULL,
  `product_image2` varchar(255) DEFAULT NULL,
  `product_image3` varchar(255) DEFAULT NULL,
  `product_image4` varchar(255) DEFAULT NULL,
  `product_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `product_old_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `Stock` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_title`, `product_discription`, `product_keyword`, `category_id`, `brand_id`, `shop_id`, `product_image1`, `product_image2`, `product_image3`, `product_image4`, `product_price`, `product_old_price`, `Stock`, `created_at`, `deleted_at`) VALUES
(1, 'Classic Fashion Shirt', 'Comfortable casual shirt for everyday wear.', 'shirt,men,fashion', 1, 1, 1, 'product-1-1.jpg', 'product-1-2.jpg', 'product-2-1.jpg', NULL, 799.00, 999.00, 25, '2026-09-02 13:24:10', NULL),
(2, 'Colorful Pattern Shirt', 'Stylish patterned shirt with a modern look.', 'shirt,pattern,men', 1, 2, 1, 'http://10.137.158.154/buymark68/php_api/api/images/product-1-1.jpg', 'product-2-2.jpg', 'product-1-1.jpg', NULL, 899.00, 1199.00, 20, '2026-09-02 13:24:10', NULL),
(3, 'Women Fashion Dress', 'Modern fashion dress for casual and party wear.', 'dress,women,fashion', 2, 3, 1, 'http://10.137.158.154/buymark68/php_api/api/images/showcase-img-7.jpg', 'product_11137b5a06f7c761.jpg', 'product-6-1.jpg', NULL, 1299.00, 1599.00, 15, '2026-09-02 13:24:10', NULL),
(4, 'Casual Women Top', 'Lightweight and comfortable top.', 'top,women,clothing', 2, 2, 1, 'product-5-2.jpg', 'product_10fdae901ca79600.jpg', 'product-6-2.jpg', NULL, 699.00, 899.00, 30, '2026-09-02 13:24:10', NULL),
(5, 'Kids Casual Wear', 'Comfortable clothing for kids.', 'kids,clothing,fashion', 3, 4, 1, 'product-6-1.jpg', 'product-6-2.jpg', 'product-8-1.jpg', NULL, 599.00, 749.00, 35, '2026-09-02 13:24:10', NULL),
(6, 'Fashion Shoes', 'Trendy footwear for daily use.', 'shoes,fashion,footwear', 4, 3, 1, 'product-8-1.jpg', 'product-8-2.jpg', 'product-9-1.jpg', NULL, 1499.00, 1899.00, 12, '2026-09-02 13:24:10', NULL),
(7, 'Stylish Bag', 'Compact stylish bag for everyday use.', 'bag,women,fashion', 5, 1, 1, 'product-9-1.jpg', 'product-9-2.jpg', 'showcase-img-7.jpg', NULL, 999.00, 1299.00, 18, '2026-09-02 13:24:10', NULL),
(8, 'Fashion Accessory', 'Simple accessory to complete your look.', 'accessory,fashion', 6, 5, 1, 'product-11-1.jpg', 'product-11-2.jpg', 'product-8-1.jpg', NULL, 399.00, 499.00, 40, '2026-09-02 13:24:10', NULL),
(12, 'T-Shirt', 'T-Shirt', 'T-Shirt', 1, 3, 1, 'product_491a78c98df1a4197c5b.png', 'product_b299e23ae1017d138b3e.png', 'product_6558c492d7f071ad1203.png', 'product_d60f057dac5df3342021.png', 329.00, 529.00, 15, '2026-09-02 16:02:26', NULL),
(13, 'Woman T-Shirt', 'Casual Woman T-shirt', 'Woman T Shirt', 2, 2, 1, 'product_e663dd9f32f709929ae1.jpg', 'product_2727eda3bc6b03e071f4.jpg', NULL, NULL, 349.00, 549.00, 10, '2026-09-02 20:08:50', NULL),
(14, 'Blue shirt ', 'Best price ', NULL, 1, NULL, 2, 'http://10.137.158.154/buymark68/php_api/api/images/product-1-1.jpg', NULL, NULL, NULL, 199.00, 599.00, 10, '2026-09-21 21:39:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_colors`
--

CREATE TABLE `product_colors` (
  `color_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `color_name` varchar(100) NOT NULL,
  `color_image` varchar(255) NOT NULL,
  `color_image2` varchar(255) DEFAULT NULL,
  `color_image3` varchar(255) DEFAULT NULL,
  `color_image4` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_colors`
--

INSERT INTO `product_colors` (`color_id`, `product_id`, `color_name`, `color_image`, `color_image2`, `color_image3`, `color_image4`, `created_at`) VALUES
(3, 12, 'Navy Blue T-Shirt', 'color_62d9749bd1db028902a8.png', 'color_372aff3f06203b3c8cec.png', 'color_ec6fc83a4bc3d031b329.png', 'color_fbe4c8d73151f6ce549a.png', '2026-09-02 16:02:26');

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `size_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `size_name` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`size_id`, `product_id`, `size_name`, `stock`) VALUES
(10, 12, 'S', 5),
(11, 12, 'M', 5),
(12, 12, 'L', 5),
(13, 3, 'S', 10),
(14, 13, 'S', 5),
(15, 13, 'M', 5);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `size` varchar(60) DEFAULT NULL,
  `color` varchar(60) DEFAULT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `stock` int(10) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `razorpay_webhook_events`
--

CREATE TABLE `razorpay_webhook_events` (
  `event_id` varchar(100) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `received_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shops`
--

CREATE TABLE `shops` (
  `shop_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `shop_name` varchar(200) NOT NULL,
  `shop_slug` varchar(220) NOT NULL,
  `category` varchar(100) NOT NULL,
  `offer_type` enum('Products','Services','Products & Services') NOT NULL DEFAULT 'Products',
  `description` text DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `area` varchar(150) NOT NULL,
  `address` varchar(500) NOT NULL,
  `landmark` varchar(200) DEFAULT NULL,
  `owner_name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `shop_photo` varchar(255) DEFAULT NULL,
  `shop_logo` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `opening_time` varchar(30) DEFAULT '10:00 AM',
  `closing_time` varchar(30) DEFAULT '9:00 PM',
  `services` text DEFAULT NULL,
  `offers` text DEFAULT NULL,
  `is_pro` tinyint(1) NOT NULL DEFAULT 0,
  `pro_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shops`
--

INSERT INTO `shops` (`shop_id`, `user_id`, `shop_name`, `shop_slug`, `category`, `offer_type`, `description`, `city`, `area`, `address`, `landmark`, `owner_name`, `phone`, `whatsapp`, `email`, `shop_photo`, `shop_logo`, `status`, `is_verified`, `created_at`, `updated_at`, `opening_time`, `closing_time`, `services`, `offers`, `is_pro`, `pro_expires_at`) VALUES
(1, 1, 'Rahul Mans Wear', 'rahul-mans-wear', 'Men\'s Wear', 'Products', 'Rahuls Shop', 'Rajnandgoan', 'somni kakrel', 'Kakrel Somni', 'panchayat', 'Rahul Sahu', '9755834970', '9755834970', 'sahurahulkumar802@gmail.com', 'uploads/shops/shop_1_b7d1cf0c6dcd2a28.png', NULL, 'pending', 0, '2026-09-18 09:05:18', '2026-09-18 09:05:18', '10:00 AM', '9:00 PM', NULL, NULL, 0, NULL),
(2, 1, 'Sumit fashion ', 'sumit-fashion', 'Men\'s Wear', 'Products', 'Best shop Rajnandgaon ', 'Rajnandgaon', 'Ge road Rajnandgaon ', '110', 'Manav mandir ', 'Rahul Sahu', '9755834970', '9755834970', 'sahurahulkumar802@gmail.com', 'upload_1_87625dc33a17.png', 'upload_1_90a9b5558818.jpeg', 'approved', 0, '2026-09-21 21:28:15', '2026-09-21 21:28:15', '10:00 AM', '9:00 PM', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shop_followers`
--

CREATE TABLE `shop_followers` (
  `id` int(10) UNSIGNED NOT NULL,
  `shop_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shop_followers`
--

INSERT INTO `shop_followers` (`id`, `shop_id`, `user_id`, `created_at`) VALUES
(2, 1, 1, '2026-09-21 20:00:59');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `subscription_id` int(10) UNSIGNED NOT NULL,
  `shop_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `plan` varchar(60) NOT NULL DEFAULT 'Pro Monthly',
  `amount` decimal(10,2) NOT NULL DEFAULT 99.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `payment_id` varchar(80) DEFAULT NULL,
  `order_id` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `address_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `addressline1` varchar(255) DEFAULT NULL,
  `addressline2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postalcode` varchar(30) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`address_id`, `user_id`, `first_name`, `last_name`, `addressline1`, `addressline2`, `city`, `state`, `country`, `postalcode`, `phone`) VALUES
(1, 1, 'Rahul', 'Sahu', 'Kakrel Somni', '', 'Rajnandgoan', 'Chhatisgarh', 'India', '491441', '09755834970');

-- --------------------------------------------------------

--
-- Table structure for table `user_intraction`
--

CREATE TABLE `user_intraction` (
  `interaction_id` int(10) UNSIGNED NOT NULL,
  `user_ip` varchar(45) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_intraction`
--

INSERT INTO `user_intraction` (`interaction_id`, `user_ip`, `user_id`, `product_id`) VALUES
(1, '::1', 0, 5),
(2, '::1', 0, 11),
(3, '::1', 0, 12),
(4, '::1', 0, 6),
(5, '::1', 0, 10),
(6, '::1', 0, 3),
(7, '::1', 0, 13),
(8, '::1', 0, 4),
(9, '::1', 0, 2),
(10, '::1', 0, 1),
(11, '::1', 0, 7);

-- --------------------------------------------------------

--
-- Table structure for table `user_orders`
--

CREATE TABLE `user_orders` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `shop_id` int(10) UNSIGNED DEFAULT NULL,
  `ammount_due` decimal(12,2) NOT NULL DEFAULT 0.00,
  `invoice_number` bigint(20) NOT NULL,
  `total_products` int(11) NOT NULL DEFAULT 1,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `order_status` varchar(50) NOT NULL DEFAULT 'panding',
  `size_name` varchar(50) DEFAULT NULL,
  `color_name` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_price` decimal(12,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `customer_name` varchar(200) DEFAULT NULL,
  `customer_email` varchar(190) DEFAULT NULL,
  `shipping_first_name` varchar(100) DEFAULT NULL,
  `shipping_last_name` varchar(100) DEFAULT NULL,
  `shipping_address1` varchar(255) DEFAULT NULL,
  `shipping_address2` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT NULL,
  `shipping_postalcode` varchar(30) DEFAULT NULL,
  `shipping_phone` varchar(30) DEFAULT NULL,
  `stock_reserved` tinyint(1) NOT NULL DEFAULT 0,
  `razorpay_order_id` varchar(80) DEFAULT NULL,
  `razorpay_payment_id` varchar(80) DEFAULT NULL,
  `razorpay_signature` varchar(128) DEFAULT NULL,
  `payment_status` varchar(30) DEFAULT NULL,
  `reservation_expires_at` datetime DEFAULT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `sku` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_orders`
--

INSERT INTO `user_orders` (`order_id`, `user_id`, `product_id`, `shop_id`, `ammount_due`, `invoice_number`, `total_products`, `order_date`, `order_status`, `size_name`, `color_name`, `product_name`, `product_price`, `quantity`, `customer_name`, `customer_email`, `shipping_first_name`, `shipping_last_name`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_state`, `shipping_country`, `shipping_postalcode`, `shipping_phone`, `stock_reserved`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `payment_status`, `reservation_expires_at`, `variant_id`, `sku`) VALUES
(1, 1, 10, NULL, 749.00, 111109943, 1, '2026-09-02 19:36:17', 'Payment Failed', NULL, NULL, 'Daily Wear Collection', 749.00, 1, 'Rahul Sahu', 'sahurahulkumar802@gmail.com', 'Rahul', 'Sahu', 'Kakrel Somni', '', 'Rajnandgoan', 'Chhatisgarh', 'India', '491441', '09755834970', 0, 'order_TXDoHT2h73C5hS', 'pay_TXDogLZVRn8Cd0', '1e0263d6dd470fff5934a1869bb022a2929dead88223009ef2c509cd1dcdd657', 'captured', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_payment`
--

CREATE TABLE `user_payment` (
  `payment_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `invoice_number` bigint(20) NOT NULL,
  `ammount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_mode` varchar(100) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `razorpay_payment_id` varchar(80) DEFAULT NULL,
  `razorpay_order_id` varchar(80) DEFAULT NULL,
  `payment_status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_payment`
--

INSERT INTO `user_payment` (`payment_id`, `user_id`, `order_id`, `invoice_number`, `ammount`, `payment_mode`, `date`, `razorpay_payment_id`, `razorpay_order_id`, `payment_status`) VALUES
(1, 1, 1, 111109943, 749.00, 'Razorpay', '2026-09-02 19:37:02', 'pay_TXDogLZVRn8Cd0', 'order_TXDoHT2h73C5hS', 'captured');

-- --------------------------------------------------------

--
-- Table structure for table `user_review`
--

CREATE TABLE `user_review` (
  `review_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `shop_id` int(10) UNSIGNED DEFAULT NULL,
  `review_data` text DEFAULT NULL,
  `rewew_date` datetime NOT NULL DEFAULT current_timestamp(),
  `review_star` int(11) NOT NULL DEFAULT 5,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `user_email` varchar(190) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_image` varchar(255) DEFAULT NULL,
  `user_ip` varchar(45) DEFAULT NULL,
  `user_address` text DEFAULT NULL,
  `user_mobile` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`user_id`, `username`, `user_email`, `user_password`, `user_image`, `user_ip`, `user_address`, `user_mobile`) VALUES
(1, 'Rahul Sahu', 'sahurahulkumar802@gmail.com', '$2y$10$JlTM2TljPsIIhJKY1XCV2Ol2LE3vDT2s/pzxtfWtrUHfWWVYvQ/Xq', NULL, '::1', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `owner_key` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `size_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `product_id`, `ip_address`, `owner_key`, `quantity`, `size_name`) VALUES
(1, 12, '::1', 'guest:vg7verq99u2cic6s13bgi7q42n', 0, NULL),
(3, 3, '::1', 'guest:fto26v9eldpm0l458enu0hg861', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_table`
--
ALTER TABLE `admin_table`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `uq_admin_username` (`admin_username`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `cart_details`
--
ALTER TABLE `cart_details`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `idx_cart_ip` (`ip_address`),
  ADD KEY `idx_cart_product` (`product_id`),
  ADD KEY `idx_cart_owner` (`cart_owner`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `compare_details`
--
ALTER TABLE `compare_details`
  ADD PRIMARY KEY (`compare_id`),
  ADD KEY `idx_compare_ip` (`user_ip`),
  ADD KEY `fk_compare_product` (`product_id`);

--
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_addr_user` (`user_id`);

--
-- Indexes for table `newsletters`
--
ALTER TABLE `newsletters`
  ADD PRIMARY KEY (`newsletter_id`),
  ADD UNIQUE KEY `uq_newsletter_email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notif_user` (`user_id`,`is_read`);

--
-- Indexes for table `orders_panding`
--
ALTER TABLE `orders_panding`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_pending_invoice` (`invoice_number`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_brand` (`brand_id`),
  ADD KEY `idx_products_shop` (`shop_id`);

--
-- Indexes for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`color_id`),
  ADD UNIQUE KEY `uq_product_color` (`product_id`,`color_name`),
  ADD KEY `idx_product_colors_product` (`product_id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`size_id`),
  ADD UNIQUE KEY `uq_product_size` (`product_id`,`size_name`),
  ADD KEY `idx_product_sizes_product` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `idx_variant_product` (`product_id`),
  ADD KEY `idx_variant_sku` (`sku`);

--
-- Indexes for table `razorpay_webhook_events`
--
ALTER TABLE `razorpay_webhook_events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`shop_id`),
  ADD UNIQUE KEY `uq_shop_slug` (`shop_slug`),
  ADD KEY `idx_shops_user` (`user_id`),
  ADD KEY `idx_shops_category` (`category`),
  ADD KEY `idx_shops_city_area` (`city`,`area`),
  ADD KEY `idx_shops_status` (`status`);

--
-- Indexes for table `shop_followers`
--
ALTER TABLE `shop_followers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_follow` (`user_id`,`shop_id`),
  ADD KEY `idx_follow_shop` (`shop_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `idx_sub_shop` (`shop_id`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_address_user` (`user_id`);

--
-- Indexes for table `user_intraction`
--
ALTER TABLE `user_intraction`
  ADD PRIMARY KEY (`interaction_id`),
  ADD KEY `idx_interaction_ip` (`user_ip`);

--
-- Indexes for table `user_orders`
--
ALTER TABLE `user_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_invoice` (`invoice_number`),
  ADD KEY `idx_orders_shop` (`shop_id`);

--
-- Indexes for table `user_payment`
--
ALTER TABLE `user_payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `uq_user_payment_razorpay` (`razorpay_payment_id`),
  ADD KEY `idx_payment_order` (`order_id`);

--
-- Indexes for table `user_review`
--
ALTER TABLE `user_review`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_review_product` (`product_id`),
  ADD KEY `idx_review_shop` (`shop_id`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_user_email` (`user_email`),
  ADD KEY `idx_user_ip` (`user_ip`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD KEY `idx_wishlist_ip` (`ip_address`),
  ADD KEY `idx_wishlist_product` (`product_id`),
  ADD KEY `idx_wishlist_owner` (`owner_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_table`
--
ALTER TABLE `admin_table`
  MODIFY `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart_details`
--
ALTER TABLE `cart_details`
  MODIFY `cart_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `compare_details`
--
ALTER TABLE `compare_details`
  MODIFY `compare_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  MODIFY `address_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletters`
--
ALTER TABLE `newsletters`
  MODIFY `newsletter_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders_panding`
--
ALTER TABLE `orders_panding`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `color_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `size_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shops`
--
ALTER TABLE `shops`
  MODIFY `shop_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shop_followers`
--
ALTER TABLE `shop_followers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `subscription_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `address_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_intraction`
--
ALTER TABLE `user_intraction`
  MODIFY `interaction_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_orders`
--
ALTER TABLE `user_orders`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_payment`
--
ALTER TABLE `user_payment`
  MODIFY `payment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_review`
--
ALTER TABLE `user_review`
  MODIFY `review_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_details`
--
ALTER TABLE `cart_details`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `compare_details`
--
ALTER TABLE `compare_details`
  ADD CONSTRAINT `fk_compare_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD CONSTRAINT `fk_colors_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `fk_sizes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shops`
--
ALTER TABLE `shops`
  ADD CONSTRAINT `fk_shops_user` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `fk_address_user` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_review`
--
ALTER TABLE `user_review`
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

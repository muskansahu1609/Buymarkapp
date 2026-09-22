-- =====================================================================
-- BuyMark — SAFE migration for the Android/REST API.
-- Run ONCE against your existing `buymark` database.
-- Non-destructive: only ADDs columns/keys. No DROP, no DELETE, no data loss.
-- Requires MySQL 8.0+ / MariaDB 10.4+ (IF NOT EXISTS on ADD COLUMN).
-- If your server does not support IF NOT EXISTS, remove it and run each line
-- only if the column is missing (check with: SHOW COLUMNS FROM products;).
-- =====================================================================

-- 1) PRODUCT -> SHOP relationship (the core missing link) + soft delete.
ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `shop_id` INT(10) UNSIGNED NULL AFTER `brand_id`,
  ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `products`
  ADD KEY IF NOT EXISTS `idx_products_shop` (`shop_id`);

-- Optional FK (skip if legacy products must survive shop deletion):
-- ALTER TABLE `products`
--   ADD CONSTRAINT `fk_products_shop` FOREIGN KEY (`shop_id`)
--   REFERENCES `shops` (`shop_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 2) SHOP enrichment for the app (logo, hours, services & offers as JSON).
ALTER TABLE `shops`
  ADD COLUMN IF NOT EXISTS `shop_logo` VARCHAR(255) NULL AFTER `shop_photo`,
  ADD COLUMN IF NOT EXISTS `opening_time` VARCHAR(30) NULL DEFAULT '10:00 AM',
  ADD COLUMN IF NOT EXISTS `closing_time` VARCHAR(30) NULL DEFAULT '9:00 PM',
  ADD COLUMN IF NOT EXISTS `services` TEXT NULL,
  ADD COLUMN IF NOT EXISTS `offers` TEXT NULL;

-- 3) ORDER -> SHOP link so a shopkeeper sees only their own orders.
ALTER TABLE `user_orders`
  ADD COLUMN IF NOT EXISTS `shop_id` INT(10) UNSIGNED NULL AFTER `product_id`;
ALTER TABLE `user_orders`
  ADD KEY IF NOT EXISTS `idx_orders_shop` (`shop_id`);

-- 3b) Variant snapshot columns on order lines (historical, never recomputed).
ALTER TABLE `user_orders`
  ADD COLUMN IF NOT EXISTS `variant_id` INT(10) UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS `size_name` VARCHAR(60) NULL,
  ADD COLUMN IF NOT EXISTS `color_name` VARCHAR(60) NULL,
  ADD COLUMN IF NOT EXISTS `sku` VARCHAR(80) NULL;

-- 3c) Variant reference on cart lines.
ALTER TABLE `cart_details`
  ADD COLUMN IF NOT EXISTS `variant_id` INT(10) UNSIGNED NULL;

-- =====================================================================
-- SELLING CORE (Phase 1): variants, Pro subscription, Razorpay orders.
-- =====================================================================

-- Product variants: size / colour / SKU / stock / price (variant-level).
CREATE TABLE IF NOT EXISTS `product_variants` (
  `variant_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `size`       VARCHAR(60) NULL,
  `color`      VARCHAR(60) NULL,
  `sku`        VARCHAR(80) NULL,
  `stock`      INT(10) NOT NULL DEFAULT 0,
  `price`      DECIMAL(10,2) NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`variant_id`),
  KEY `idx_variant_product` (`product_id`),
  KEY `idx_variant_sku` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shop Pro flag + expiry (Pro = unlimited products for 30 days per payment).
ALTER TABLE `shops`
  ADD COLUMN IF NOT EXISTS `is_pro` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `pro_expires_at` DATETIME NULL DEFAULT NULL;

-- Subscription ledger (one row per successful ₹99 payment).
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `subscription_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id`   INT(10) UNSIGNED NOT NULL,
  `user_id`   INT(10) UNSIGNED NOT NULL,
  `plan`      VARCHAR(60) NOT NULL DEFAULT 'Pro Monthly',
  `amount`    DECIMAL(10,2) NOT NULL DEFAULT 99,
  `status`    VARCHAR(20) NOT NULL DEFAULT 'active',
  `start_at`  DATETIME NOT NULL,
  `end_at`    DATETIME NOT NULL,
  `payment_id` VARCHAR(80) NULL,
  `order_id`   VARCHAR(80) NULL,
  PRIMARY KEY (`subscription_id`),
  KEY `idx_sub_shop` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- PHASE 2 (Customer engagement): follow, notifications, saved addresses.
-- =====================================================================

CREATE TABLE IF NOT EXISTS `shop_followers` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_follow` (`user_id`,`shop_id`),
  KEY `idx_follow_shop` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `type` VARCHAR(40) NOT NULL,
  `title` VARCHAR(160) NULL,
  `message` VARCHAR(255) NULL,
  `image` VARCHAR(255) NULL,
  `reference_id` INT(10) UNSIGNED NULL,
  `reference_type` VARCHAR(40) NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_notif_user` (`user_id`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `customer_addresses` (
  `address_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `full_name` VARCHAR(120) NOT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `house` VARCHAR(160) NULL,
  `street` VARCHAR(160) NULL,
  `landmark` VARCHAR(160) NULL,
  `city` VARCHAR(80) NOT NULL,
  `state` VARCHAR(80) NULL,
  `pincode` VARCHAR(12) NULL,
  `type` VARCHAR(20) NOT NULL DEFAULT 'Home',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`address_id`),
  KEY `idx_addr_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- 4) Legacy data handling (SAFE): attach any product with NO shop to the
--    FIRST existing shop so it stays discoverable. Review before running in
--    production if you prefer to leave legacy products unassigned.
UPDATE `products`
   SET `shop_id` = (SELECT `shop_id` FROM `shops` ORDER BY `shop_id` ASC LIMIT 1)
 WHERE `shop_id` IS NULL;

-- 5) Backfill existing orders' shop_id from their product where possible.
UPDATE `user_orders` o
   JOIN `products` p ON p.`product_id` = o.`product_id`
    SET o.`shop_id` = p.`shop_id`
 WHERE o.`shop_id` IS NULL;

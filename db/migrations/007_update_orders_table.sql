-- Update orders table to support structured addresses and link to users

-- Add user_id to link orders to the users table (can be NULL for guest checkouts)
ALTER TABLE `orders` ADD COLUMN `user_id` INT NULL DEFAULT NULL AFTER `id`, ADD CONSTRAINT `fk_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- Add structured shipping address fields
ALTER TABLE `orders` 
ADD COLUMN `shipping_province` VARCHAR(100) NOT NULL AFTER `customer_phone`,
ADD COLUMN `shipping_city` VARCHAR(100) NOT NULL AFTER `shipping_province`,
ADD COLUMN `shipping_address_line` TEXT NOT NULL AFTER `shipping_city`,
ADD COLUMN `shipping_postal_code` VARCHAR(20) NOT NULL AFTER `shipping_address_line`;

-- Rename old columns to avoid confusion, but keep them for any old data
ALTER TABLE `orders` 
CHANGE `customer_name` `billing_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `customer_email` `billing_email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `customer_address` `legacy_customer_address` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;


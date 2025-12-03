-- Add the colors column to the products table if it doesn't exist
ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `colors` VARCHAR(255) DEFAULT NULL COMMENT 'Comma-separated list of available colors';

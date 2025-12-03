-- Add the is_featured column to the products table if it doesn't exist
ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `is_featured` BOOLEAN DEFAULT 0;

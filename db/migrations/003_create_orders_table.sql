CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(255) NOT NULL,
    `customer_email` VARCHAR(255) NOT NULL,
    `customer_address` TEXT NOT NULL,
    `customer_phone` VARCHAR(50) DEFAULT NULL,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `items_json` JSON NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add the status column to the orders table if it doesn't exist
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `status` VARCHAR(50) NOT NULL DEFAULT 'Pending' COMMENT 'e.g., Pending, Processing, Shipped, Delivered, Canceled';
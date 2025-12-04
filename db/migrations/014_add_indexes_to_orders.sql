-- Add indexes to the orders table to improve query performance for the dashboard

-- Index for the status column, as it's frequently used in WHERE clauses
ALTER TABLE `orders` ADD INDEX `idx_status` (`status`);

-- Index for the order date column, as it's used for grouping and filtering by date
ALTER TABLE `orders` ADD INDEX `idx_created_at` (`created_at`);

-- Convert the orders table and its text-based columns to utf8mb4 to support Persian characters
ALTER TABLE `orders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Additionally, ensure individual text columns are correctly set
ALTER TABLE `orders`
    MODIFY `billing_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    MODIFY `billing_email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    MODIFY `legacy_customer_address` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    MODIFY `billing_phone` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    MODIFY `billing_province` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    MODIFY `billing_city` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    MODIFY `billing_address` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    MODIFY `billing_postal_code` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    MODIFY `items_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    MODIFY `status` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    MODIFY `tracking_id` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

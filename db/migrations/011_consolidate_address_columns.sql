-- Rename shipping_* columns to billing_* to match the application logic
ALTER TABLE `orders` 
CHANGE `shipping_province` `billing_province` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
CHANGE `shipping_city` `billing_city` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
CHANGE `shipping_address_line` `billing_address` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
CHANGE `shipping_postal_code` `billing_postal_code` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
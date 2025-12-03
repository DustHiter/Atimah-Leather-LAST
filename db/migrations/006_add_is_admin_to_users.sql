-- Add is_admin flag to users table to differentiate admins from regular users
ALTER TABLE `users` ADD `is_admin` BOOLEAN NOT NULL DEFAULT FALSE AFTER `password`;

-- Database Schema for Recipe Management System
-- Tables: items, recipe, ingredients
USE FOOD_PRODUCT; 
-- Create items table
CREATE TABLE IF NOT EXISTS `items` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `short_name` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `balance` DECIMAL(10, 3) NOT NULL DEFAULT 0.000,
    `unit` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_short_name` (`short_name`),
    KEY `idx_short_name` (`short_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create recipe table
CREATE TABLE IF NOT EXISTS `recipes` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_day` (`date`),
    KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ingredients table (junction table with M:1 relationships)
CREATE TABLE IF NOT EXISTS `ingredients` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `recipe_id` INT(11) UNSIGNED NOT NULL,
    `item_id` INT(11) UNSIGNED NOT NULL,
    `quantity` DECIMAL(10, 3) NOT NULL DEFAULT 0.000,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_recipe_id` (`recipe_id`),
    KEY `idx_item_id` (`item_id`),
    CONSTRAINT `fk_ingredients_recipe` FOREIGN KEY (`recipe_id`) 
        REFERENCES `recipes` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_ingredients_item` FOREIGN KEY (`item_id`) 
        REFERENCES `items` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    UNIQUE KEY `unique_recipe_item` (`recipe_id`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transactions`(
    id INT NOT NULL AUTO_INCREMENT,
    `item_id` INT(11) UNSIGNED NOT NULL,
    `recipe_id` INT(11) UNSIGNED NULL,
    `qty_used` DECIMAL(10,2) NOT NULL, 
    `operation` CHAR(1), 
    `balance_before` DECIMAL(10,2) NOT NULL, 
    `balance_after` DECIMAL(10,2) NOT NULL, 
    `type` ENUM('update item','recipe usage') NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_recipe_id` (`recipe_id`),
    KEY `idx_item_id` (`item_id`)
) 

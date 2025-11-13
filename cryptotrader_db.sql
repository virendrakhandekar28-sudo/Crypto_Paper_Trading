SET NAMES utf8mb4;
SET CHARACTER_SET_CLIENT = utf8mb4;
SET CHARACTER_SET_RESULTS = utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;
SET default_storage_engine = InnoDB;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS cryptotrader_db;
USE cryptotrader_db;

DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `trade_positions`;
DROP TABLE IF EXISTS `portfolios`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_username_unique` (`username` ASC),
  UNIQUE INDEX `idx_email_unique` (`email` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `portfolios` (
  `user_id` INT UNSIGNED NOT NULL,
  `cash_balance` DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_portfolios_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `trade_positions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `symbol` VARCHAR(20) NOT NULL,
  `trading_pair` VARCHAR(30) NOT NULL,
  `direction` ENUM('LONG', 'SHORT') NOT NULL,
  `quantity` DECIMAL(24, 12) NOT NULL,
  `entry_price` DECIMAL(20, 8) NOT NULL,
  `entry_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('OPEN', 'CLOSED') NOT NULL DEFAULT 'OPEN',
  PRIMARY KEY (`id`),
  INDEX `idx_user_symbol_status` (`user_id` ASC, `symbol` ASC, `status` ASC),
  INDEX `idx_entry_timestamp` (`entry_timestamp` ASC),
  CONSTRAINT `fk_trade_positions_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `position_id_closed` INT UNSIGNED NULL DEFAULT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `symbol` VARCHAR(30) NOT NULL,
  `type` ENUM('BUY', 'SELL') NOT NULL,
  `quantity` DECIMAL(24, 12) NOT NULL,
  `price` DECIMAL(20, 8) NOT NULL,
  `total` DECIMAL(20, 8) NOT NULL,
  `status` ENUM('FILLED', 'PENDING', 'CANCELLED', 'FAILED') NOT NULL DEFAULT 'FILLED',
  `order_purpose` ENUM('OPEN', 'CLOSE') NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_user_timestamp` (`user_id` ASC, `timestamp` DESC),
  INDEX `idx_order_symbol` (`symbol` ASC),
  INDEX `idx_position_closed` (`position_id_closed` ASC),
  CONSTRAINT `fk_orders_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;
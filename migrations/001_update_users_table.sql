-- Migration: update users table to support secure verification tokens and hashed passwords
-- Run this in your MySQL database for the project (e.g., via phpMyAdmin or mysql CLI).

-- Example: create table if it does not exist (adjust types to your needs)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  verification_code VARCHAR(64) DEFAULT NULL,
  token_expiry DATETIME DEFAULT NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- If you already have a users table, run the ALTER below instead of creating a new table
ALTER TABLE users
  MODIFY password VARCHAR(255) NOT NULL,
  MODIFY verification_code VARCHAR(64) NULL,
  ADD COLUMN token_expiry DATETIME NULL,
  ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0;

CREATE INDEX IF NOT EXISTS idx_verification_code ON users (verification_code);

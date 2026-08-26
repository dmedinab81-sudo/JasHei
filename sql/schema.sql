CREATE DATABASE IF NOT EXISTS jashei CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jashei;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','doctor','nurse','staff') NOT NULL DEFAULT 'staff',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (full_name, email, password_hash, role, is_active)
VALUES
  ('Administrador del Sistema', 'admin@jashei.local', '$2y$12$G0w2/jDpIOIrZjTZ2WDUIOvFlnbE5zT/vMWEZqZs9eYUmvnSncRfy', 'admin', 1)
ON DUPLICATE KEY UPDATE email = email;

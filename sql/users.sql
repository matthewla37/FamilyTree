-- General user schema for manual user imports

CREATE
DATABASE IF NOT EXISTS familytree CHARACTER
SET
    utf8mb4
COLLATE utf8mb4_unicode_ci;

USE familytree;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone_number VARCHAR(30) NOT NULL,
    is_admin TINYINT (1) NOT NULL DEFAULT 0,
    status ENUM ('active', 'deleted') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email),
    UNIQUE KEY unique_phone (phone_number)
);

-- Add general users manually to this table. Do not insert admin users here.
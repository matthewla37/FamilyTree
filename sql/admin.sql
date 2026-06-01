-- Admin database schema and initial admin account

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

CREATE TABLE IF NOT EXISTS signup_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone_number VARCHAR(30) NOT NULL,
    status ENUM (
        'pending',
        'accepted',
        'declined'
    ) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    UNIQUE KEY pending_contact (email, phone_number)
);

CREATE TABLE IF NOT EXISTS login_pins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(30) NOT NULL,
    pin VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone_number)
);

INSERT INTO
    users (
        first_name,
        last_name,
        email,
        phone_number,
        is_admin,
        status
    )
VALUES (
        'Jamie',
        'Adler',
        'jamie.adler@example.com',
        '+15553456789',
        1,
        'active'
    ) ON DUPLICATE KEY
UPDATE first_name =
VALUES (first_name),
    last_name =
VALUES (last_name),
    email =
VALUES (email),
    is_admin = 1,
    status = 'active';
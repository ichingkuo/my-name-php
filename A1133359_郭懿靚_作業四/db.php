<?php
require_once 'config.php';

// 連線並建立資料庫（如不存在）
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) die("資料庫連線失敗：" . $conn->connect_error);
$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db(DB_NAME);

// 郵件地址表
$conn->query("CREATE TABLE IF NOT EXISTS `emails` (
    `no` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 寄送任務進度表
$conn->query("CREATE TABLE IF NOT EXISTS `send_jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `total` INT NOT NULL DEFAULT 0,
    `sent` INT NOT NULL DEFAULT 0,
    `status` VARCHAR(20) NOT NULL DEFAULT 'running',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// SMTP 設定表
$conn->query("CREATE TABLE IF NOT EXISTS `smtp_config` (
    `id` INT PRIMARY KEY DEFAULT 1,
    `host` VARCHAR(255) NOT NULL,
    `port` INT NOT NULL DEFAULT 587,
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `encryption` VARCHAR(10) DEFAULT 'tls',
    `from_email` VARCHAR(255) NOT NULL,
    `from_name` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 若無 SMTP 設定則寫入預設值（建議盡快修改）
$check = $conn->query("SELECT id FROM smtp_config WHERE id=1");
if ($check->num_rows == 0) {
    $conn->query("INSERT INTO smtp_config (id, host, port, username, password, encryption, from_email, from_name)
    VALUES (1, 'smtp.gmail.com', 587, '', '', 'tls', '', '')");
}

// 新增：郵件寄送紀錄表
$conn->query("CREATE TABLE IF NOT EXISTS `send_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `status` VARCHAR(20) NOT NULL,
    `error_msg` TEXT,
    `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX(`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
CREATE DATABASE IF NOT EXISTS url_shortener;
USE url_shortener;

CREATE TABLE IF NOT EXISTS urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    long_url VARCHAR(255) NOT NULL,
    short_code VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    clicks INT DEFAULT 0,
    user_ip VARCHAR(45),
    user_agent VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url_id INT NOT NULL,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    referrer VARCHAR(255),
    ip_address VARCHAR(45),
    country VARCHAR(100),
    device_type VARCHAR(50),
    FOREIGN KEY (url_id) REFERENCES urls(id)
);
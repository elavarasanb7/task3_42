-- MySQL version of forum setup script

CREATE DATABASE IF NOT EXISTS community_forum;
USE community_forum;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    date_joined TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

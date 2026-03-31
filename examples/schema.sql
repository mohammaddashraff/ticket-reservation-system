-- Starter schema inferred from the PHP domain classes in this repository.
-- You can adjust names/types to match your existing database standards.

CREATE DATABASE IF NOT EXISTS ticket_reservation;
USE ticket_reservation;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    tel VARCHAR(30) NOT NULL,
    type VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS companies (
    user_id INT UNSIGNED PRIMARY KEY,
    companyName VARCHAR(150) NOT NULL,
    bio TEXT,
    address VARCHAR(255),
    location VARCHAR(120),
    logoImg VARCHAR(255),
    account DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS passengers (
    user_id INT UNSIGNED PRIMARY KEY,
    photo VARCHAR(255),
    passportImg VARCHAR(255),
    account DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS flights (
    flightId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    itinerary VARCHAR(255) NOT NULL,
    fees DECIMAL(10,2) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    completed TINYINT(1) NOT NULL DEFAULT 0,
    companyId INT UNSIGNED NOT NULL,
    numPassengers INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (companyId) REFERENCES companies(user_id)
);

CREATE TABLE IF NOT EXISTS passengersOnFlight (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    passengerId INT UNSIGNED NOT NULL,
    flightId INT UNSIGNED NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (passengerId) REFERENCES passengers(user_id),
    FOREIGN KEY (flightId) REFERENCES flights(flightId)
);

CREATE TABLE IF NOT EXISTS messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    companyId INT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (companyId) REFERENCES companies(user_id)
);

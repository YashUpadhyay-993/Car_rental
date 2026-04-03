-- Driver Module Database Updates
-- Run this after importing the main database.sql file

USE if0_41559190_carrental;

-- Drivers table
CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    license_number VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Rides table (links bookings with drivers)
CREATE TABLE IF NOT EXISTS rides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    driver_id INT NOT NULL,
    status ENUM('assigned', 'accepted', 'rejected', 'on_the_way', 'started', 'completed', 'cancelled') DEFAULT 'assigned',
    pickup_location TEXT,
    drop_location TEXT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
);

-- Insert sample drivers
INSERT INTO drivers (name, email, phone, license_number, password, status) VALUES 
('John Driver', 'john.driver@carrental.com', '555-0101', 'DL001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active'),
('Sarah Driver', 'sarah.driver@carrental.com', '555-0102', 'DL002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active'),
('Mike Driver', 'mike.driver@carrental.com', '555-0103', 'DL003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inactive');

-- Update bookings table to add driver-related fields
ALTER TABLE bookings 
ADD COLUMN driver_assigned BOOLEAN DEFAULT FALSE,
ADD COLUMN ride_status ENUM(
    'pending_driver',
    'driver_assigned',
    'ride_accepted',
    'ride_started',
    'ride_completed',
    'ride_cancelled'
) DEFAULT 'pending_driver' AFTER status;




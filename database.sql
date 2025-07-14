CREATE DATABASE yogofura;
USE yogofura;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'vendor', 'customer') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    size ENUM('small', 'medium', 'large') NOT NULL,
    toppings TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    FOREIGN KEY (vendor_id) REFERENCES users(id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    vendor_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT NOT NULL,
    delivery_type ENUM('pickup', 'delivery') NOT NULL,
    delivery_address VARCHAR(255),
    scheduled_time DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'delivered') DEFAULT 'pending',
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (vendor_id) REFERENCES users(id),
    FOREIGN KEY (menu_id) REFERENCES menu(id)
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method VARCHAR(50) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    reference VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (sender_id) REFERENCES users(id)
);

-- Seed Data
INSERT INTO users (name, email, password, role, status) VALUES
('Admin User', 'admin@yogofura.com', '$2y$10$4z5X9Y7Z2X8Y9Z0A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T', 'admin', 'approved'),
('Vendor One', 'vendor1@yogofura.com', '$2y$10$4z5X9Y7Z2X8Y9Z0A1B2C3D4E5F6G7H8I9J0K1L23M4O5P6Q7R8S9T', 'vendor', 'approved'),
('Customer One', 'customer1@yogofura.com', '$2y$10$4z5X9Y7Z2X8Y9Z0A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T', 'customer', 'approved');

INSERT INTO menu (vendor_id, name, size, toppings, price, image) VALUES
(2, 'Classic Fura', 'medium', 'Nuts, Honey', 500.00, 'fura1.jpg'),
(2, 'Spiced Yoghurt', 'large', 'Fruit, Granola', 700.00, 'yoghurt1.jpg');

INSERT INTO orders (customer_id, vendor_id, menu_id, quantity, delivery_type, delivery_address, scheduled_time, status, total_price) VALUES
(3, 2, 1, 2, 'delivery', '123 Lagos Street, Nigeria', '2025-07-11 14:00:00', 'pending', 1000.00);

INSERT INTO payments (order_id, method, status, reference) VALUES
(1, 'Paystack', 'completed', 'TRX_123456789');
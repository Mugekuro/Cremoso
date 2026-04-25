-- Create database
CREATE DATABASE IF NOT EXISTS cremoso_db;
USE cremoso_db;

-- Branches table
CREATE TABLE branches (
    branch_id INT PRIMARY KEY AUTO_INCREMENT,
    branch_name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL
);

-- Users table (using username instead of email)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') NOT NULL,
    branch_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL
);

-- Customers
CREATE TABLE customers (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(100) NOT NULL,
    contact VARCHAR(50) NULL
);

-- Order channels
CREATE TABLE order_channels (
    channel_id INT PRIMARY KEY AUTO_INCREMENT,
    channel_name VARCHAR(50) NOT NULL
);

-- Payment methods
CREATE TABLE payment_methods (
    payment_method_id INT PRIMARY KEY AUTO_INCREMENT,
    method_name VARCHAR(50) NOT NULL
);

-- Flavors
CREATE TABLE flavors (
    flavor_id INT PRIMARY KEY AUTO_INCREMENT,
    flavor_name VARCHAR(50) NOT NULL
);

-- Item sizes
CREATE TABLE item_sizes (
    size_id INT PRIMARY KEY AUTO_INCREMENT,
    size_name VARCHAR(20) NOT NULL,
    price_multiplier DECIMAL(3,2) DEFAULT 1.00
);

-- Items
CREATE TABLE items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    item_name VARCHAR(100) NOT NULL,
    flavor_id INT NOT NULL,
    size_id INT NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (flavor_id) REFERENCES flavors(flavor_id),
    FOREIGN KEY (size_id) REFERENCES item_sizes(size_id)
);

-- Transactions
CREATE TABLE transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    user_id INT NOT NULL,
    branch_id INT NOT NULL,
    channel_id INT NOT NULL,
    payment_method_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    FOREIGN KEY (channel_id) REFERENCES order_channels(channel_id),
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(payment_method_id)
);

-- Transaction items
CREATE TABLE transaction_items (
    transaction_item_id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(item_id)
);

-- Insert data
INSERT INTO branches (branch_name, location) VALUES
('Cremoso HQ (Main)', 'Malaybalay City'),
('Cremoso Branch', 'Malaybalay City');

INSERT INTO order_channels (channel_name) VALUES
('Walk-in'), ('Facebook Messenger'), ('Foodpanda');

INSERT INTO payment_methods (method_name) VALUES
('Cash'), ('GCash'), ('Credit/Debit Card');

INSERT INTO flavors (flavor_name) VALUES
('Vanilla'), ('Chocolate'), ('Strawberry'), ('Ube'), ('Cheese');

INSERT INTO item_sizes (size_name, price_multiplier) VALUES
('Small', 1.0), ('Medium', 1.3), ('Large', 1.6);

INSERT INTO items (item_name, flavor_id, size_id, base_price) VALUES
('Soft Serve', 1, 1, 50.00),
('Soft Serve', 1, 2, 65.00),
('Soft Serve', 1, 3, 80.00),
('Soft Serve', 2, 1, 55.00),
('Soft Serve', 2, 2, 70.00),
('Soft Serve', 2, 3, 85.00),
('Soft Serve', 3, 1, 55.00),
('Soft Serve', 4, 1, 60.00),
('Soft Serve', 5, 1, 60.00);

-- Users with plain text passwords (for simplicity)
INSERT INTO users (username, fullname, password, role, branch_id) VALUES
('admin', 'Admin User', 'admin', 'admin', NULL),
('staff1', 'Maria Staff - HQ', 'staff1', 'staff', 1),
('staff2', 'John Staff', 'staff2', 'staff', 2);

-- Default customer
INSERT INTO customers (customer_name, contact) VALUES ('Walk-in Customer', 'N/A');
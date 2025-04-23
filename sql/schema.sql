-- Drop & recreate the database
DROP DATABASE IF EXISTS inventory_system;
CREATE DATABASE IF NOT EXISTS inventory_system;
USE inventory_system;

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20), -- Optional phone number
    profile_picture VARCHAR(255), -- Path or URL to profile picture
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: customers
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT, -- Added to store customer address
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL, -- Cost price
    selling_price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    min_stock INT DEFAULT 5,
    location VARCHAR(50),
    image VARCHAR(255), -- Path under uploads/images/
    description TEXT, -- Replaces "color"
    barcode VARCHAR(50), -- SKU / barcode
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Table: product_sizes
CREATE TABLE product_sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size_name VARCHAR(10) NOT NULL, -- Renamed from size
    stock INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Table: batches
CREATE TABLE batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    product_size_id INT NOT NULL,
    batch_number VARCHAR(50) NOT NULL,
    manufactured_date DATE NOT NULL, -- Field for manufactured date
    stock INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (product_size_id) REFERENCES product_sizes(id),
    INDEX idx_product_id (product_id),
    INDEX idx_product_size_id (product_size_id),
    INDEX idx_manufactured_date (manufactured_date) -- Index on manufactured_date
);

-- Table: sales
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    discount_total DECIMAL(10,2) DEFAULT 0, -- Calculated based on percentage input
    status ENUM('pending', 'confirmed', 'delivered', 'canceled') NOT NULL DEFAULT 'pending', -- Added status column
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    INDEX idx_created_at (created_at), -- Added index for performance
    INDEX idx_status (status) -- Added index for performance
);

-- Table: sale_items
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    product_size_id INT DEFAULT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0, -- Discount percentage for this item
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (product_size_id) REFERENCES product_sizes(id)
);

-- Table: stock_logs
CREATE TABLE stock_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    changes VARCHAR(50) NOT NULL, -- Stores "Added 5 Stock" or "Reduced 5 Stock"
    reason VARCHAR(100) NOT NULL,
    user_id INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table: expenses
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    expense_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table: audit_logs
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sale_id INT DEFAULT NULL, -- Added to track sale-specific actions
    action VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (sale_id) REFERENCES sales(id)
);

-- Table: settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(50) NOT NULL,
    value VARCHAR(255) NOT NULL,
    user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Seed data
INSERT INTO users (username, password_hash, role, email)
VALUES ('admin', '$2y$10$3zP9qX8zP9qX8zP9qX8zP9qX8zP9qX8zP9qX8zP9qX8zP9qX8zP9', 'admin', 'admin@example.com');

INSERT INTO customers (name, phone, email)
VALUES ('Default Customer', '1234567890', 'customer@example.com');

INSERT INTO settings (key_name, value)
VALUES ('email_notifications_low_stock', 'enabled');
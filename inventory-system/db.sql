-- Database: inventory_management

CREATE DATABASE IF NOT EXISTS inventory_management;
USE inventory_management;

-- Users table for authentication
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'manager', 'staff') NOT NULL DEFAULT 'staff',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Suppliers table
CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    tax_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT,
    supplier_id INT,
    unit_price DECIMAL(10, 2) NOT NULL,
    reorder_level INT NOT NULL DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
);

-- Inventory table (stock levels)
CREATE TABLE inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    location VARCHAR(50),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Transactions table (inventory movements)
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    transaction_type ENUM('in', 'out', 'adjustment', 'return') NOT NULL,
    quantity INT NOT NULL,
    reference VARCHAR(100),
    notes TEXT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Purchase Orders table
CREATE TABLE purchase_orders (
    po_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    order_date DATE NOT NULL,
    expected_delivery DATE,
    status ENUM('draft', 'ordered', 'received', 'cancelled') NOT NULL DEFAULT 'draft',
    total_amount DECIMAL(12, 2),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Purchase Order Items table
CREATE TABLE po_items (
    po_item_id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    received_quantity INT DEFAULT 0,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Insert mock data
INSERT INTO users (username, password, full_name, email, role) VALUES 
('admin', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Admin User', 'admin@inventory.com', 'admin'),
('manager1', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Manager One', 'manager@inventory.com', 'manager'),
('staff1', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Staff Member', 'staff@inventory.com', 'staff');

INSERT INTO categories (name, description) VALUES 
('Electronics', 'Electronic components and devices'),
('Hardware', 'Construction and building materials'),
('Office Supplies', 'Items for office use'),
('Tools', 'Hand and power tools');

INSERT INTO suppliers (name, contact_person, email, phone, address, tax_id) VALUES 
('Tech Supplies Inc.', 'John Smith', 'john@techsupplies.com', '555-123-4567', '123 Tech Street, Tech City', 'TAX123456'),
('Builders World', 'Sarah Johnson', 'sarah@builders.com', '555-987-6543', '456 Construction Ave, Build Town', 'TAX654321'),
('Office Plus', 'Mike Brown', 'mike@officeplus.com', '555-456-7890', '789 Business Rd, Office Park', 'TAX789012');

INSERT INTO products (sku, name, description, category_id, supplier_id, unit_price, reorder_level) VALUES 
('ELEC-001', 'Laptop', 'High performance business laptop', 1, 1, 899.99, 5),
('ELEC-002', 'Monitor', '24-inch LED monitor', 1, 1, 199.99, 10),
('HW-001', 'Steel Beam', '10ft steel beam for construction', 2, 2, 49.99, 20),
('OFF-001', 'Stapler', 'Heavy duty office stapler', 3, 3, 12.99, 15),
('TOOL-001', 'Drill', 'Cordless power drill', 4, 2, 89.99, 8),
('OFF-002', 'Notebook', '100-page lined notebook', 3, 3, 4.99, 50);

INSERT INTO inventory (product_id, quantity, location) VALUES 
(1, 8, 'Warehouse A, Shelf 1'),
(2, 15, 'Warehouse A, Shelf 2'),
(3, 25, 'Warehouse B, Rack 5'),
(4, 12, 'Warehouse A, Shelf 3'),
(5, 6, 'Warehouse B, Rack 3'),
(6, 45, 'Warehouse A, Shelf 4');

INSERT INTO transactions (product_id, user_id, transaction_type, quantity, reference, notes) VALUES 
(1, 1, 'in', 10, 'PO-1001', 'Initial stock'),
(2, 1, 'in', 20, 'PO-1001', 'Initial stock'),
(3, 1, 'in', 30, 'PO-1002', 'Initial stock'),
(4, 1, 'in', 15, 'PO-1003', 'Initial stock'),
(5, 1, 'in', 10, 'PO-1002', 'Initial stock'),
(6, 1, 'in', 50, 'PO-1003', 'Initial stock'),
(1, 2, 'out', 2, 'SALE-001', 'Sold to customer'),
(2, 2, 'out', 5, 'SALE-001', 'Sold to customer'),
(4, 3, 'out', 3, 'INT-001', 'Internal use');

-- Reports table
CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('inventory', 'purchases', 'suppliers', 'custom') NOT NULL,
    time_period VARCHAR(50) NOT NULL,
    chart_type ENUM('bar', 'pie', 'line', 'none') DEFAULT 'none',
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Report formats table
CREATE TABLE report_formats (
    report_format_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    format ENUM('pdf', 'excel', 'html') NOT NULL,
    FOREIGN KEY (report_id) REFERENCES reports(report_id) ON DELETE CASCADE
);

-- Report filters (for category/supplier filters)
CREATE TABLE report_filters (
    filter_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    filter_type ENUM('category', 'supplier') NOT NULL,
    filter_value INT NOT NULL,
    FOREIGN KEY (report_id) REFERENCES reports(report_id) ON DELETE CASCADE
);

-- Report data points (for chart configuration)
CREATE TABLE report_data_points (
    data_point_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    data_point ENUM('quantity', 'value', 'category') NOT NULL,
    FOREIGN KEY (report_id) REFERENCES reports(report_id) ON DELETE CASCADE
);

-- Report schedules (optional - for scheduled reports)
CREATE TABLE report_schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    frequency ENUM('daily', 'weekly', 'monthly', 'quarterly') NOT NULL,
    next_run DATETIME NOT NULL,
    recipients TEXT,
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (report_id) REFERENCES reports(report_id) ON DELETE CASCADE
);

-- Report logs (track report generation history)
CREATE TABLE report_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    user_id INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    format ENUM('pdf', 'excel', 'html') NOT NULL,
    file_path VARCHAR(255),
    FOREIGN KEY (report_id) REFERENCES reports(report_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
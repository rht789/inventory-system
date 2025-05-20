-- Migration: 003_add_performance_indexes.sql
-- Description: Adds indexes to frequently queried columns for performance

-- Indexes for user search and filtering
ALTER TABLE users 
ADD INDEX idx_users_role (role),
ADD INDEX idx_users_status (status),
ADD INDEX idx_users_email (email(50)),
ADD INDEX idx_users_created_at (created_at);

-- Indexes for products search and filtering
ALTER TABLE products 
ADD INDEX idx_products_name (name(50)),
ADD INDEX idx_products_price (price),
ADD INDEX idx_products_selling_price (selling_price),
ADD INDEX idx_products_stock (stock),
ADD INDEX idx_products_barcode (barcode(20));

-- Indexes for sales search and filtering
ALTER TABLE sales 
ADD INDEX idx_sales_user_customer (user_id, customer_id),
ADD INDEX idx_sales_total (total);

-- Indexes for reporting and dashboard queries
ALTER TABLE stock_logs 
ADD INDEX idx_stock_logs_product_timestamp (product_id, timestamp);

-- Indexes for better notification filtering
ALTER TABLE notifications 
ADD INDEX idx_notifications_type_role (type, role);

-- Indexes for audit logs filtering
ALTER TABLE audit_logs
ADD INDEX idx_audit_logs_user_timestamp (user_id, timestamp);

-- Indexes for better search on customers
ALTER TABLE customers
ADD INDEX idx_customers_name (name(50)),
ADD INDEX idx_customers_email (email(50)),
ADD INDEX idx_customers_phone (phone(20)); 
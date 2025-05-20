-- Migration: 002_add_soft_deletes.sql
-- Description: Adds deleted_at columns to tables for soft delete functionality

-- Add deleted_at column to customers table
ALTER TABLE customers 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL,
ADD INDEX idx_customers_deleted_at (deleted_at);

-- Add deleted_at column to categories table
ALTER TABLE categories 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL,
ADD INDEX idx_categories_deleted_at (deleted_at);

-- Add deleted_at column to users table
ALTER TABLE users 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL,
ADD INDEX idx_users_deleted_at (deleted_at);

-- Add deleted_at column to product_sizes table
ALTER TABLE product_sizes 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL,
ADD INDEX idx_product_sizes_deleted_at (deleted_at);

-- Add deleted_at column to batches table
ALTER TABLE batches 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL,
ADD INDEX idx_batches_deleted_at (deleted_at);

-- Add deleted_at column to sales table
ALTER TABLE sales 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL,
ADD INDEX idx_sales_deleted_at (deleted_at); 
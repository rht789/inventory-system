-- Migration: 001_create_migrations_table.sql
-- Description: Creates the migrations table to track applied migrations

CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration_name VARCHAR(255) NOT NULL UNIQUE,
    batch INT NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add a comment to indicate this is the initial migration
INSERT INTO migrations (migration_name, batch) VALUES ('001_create_migrations_table', 1); 
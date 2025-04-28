-- Table: notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL, -- 'low_stock', 'sale', 'stock', 'audit', 'other'
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'all', -- 'all', 'admin', 'staff'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: notification_reads
CREATE TABLE notification_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_notification_user (notification_id, user_id)
); 
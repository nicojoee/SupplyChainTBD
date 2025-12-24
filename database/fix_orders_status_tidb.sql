-- Fix orders.status ENUM for TiDB Cloud
-- Run this SQL directly in TiDB Cloud SQL Editor if migrations don't work

-- Add 'pickup' and 'in_delivery' to the status ENUM
ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'processing', 'pickup', 'in_delivery', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending';

-- Verify the change
SHOW COLUMNS FROM orders LIKE 'status';

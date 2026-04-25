-- Migration to add pending orders functionality
USE cremoso_db;

-- Add status column to transactions table
ALTER TABLE transactions 
ADD COLUMN status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending' AFTER total_amount;

-- Update existing transactions to be confirmed (backward compatibility)
UPDATE transactions SET status = 'confirmed' WHERE status = 'pending';
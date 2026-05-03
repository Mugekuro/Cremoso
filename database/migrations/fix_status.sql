-- Fix any existing transactions with 'confirmed' status to 'completed'
-- This ensures all existing data matches the schema definition

USE cremoso_db;

-- Update any transactions with 'confirmed' status to 'completed'
UPDATE transactions 
SET status = 'completed' 
WHERE status = 'confirmed';

-- Show the result
SELECT 'Status fix completed!' AS message;
SELECT status, COUNT(*) as count 
FROM transactions 
GROUP BY status;

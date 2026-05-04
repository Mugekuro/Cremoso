-- Add addons_detail column back to transaction_items table
-- This column stores add-ons information as JSON or "NONE" if no add-ons

USE cremoso_db;

-- Add the column if it doesn't exist
ALTER TABLE transaction_items 
ADD COLUMN IF NOT EXISTS addons_detail TEXT NULL COMMENT 'JSON array of selected add-ons with names and prices, or "NONE" if no add-ons';

-- Update existing NULL values to "NONE"
UPDATE transaction_items 
SET addons_detail = 'NONE' 
WHERE addons_detail IS NULL OR addons_detail = '';

SELECT '✓ addons_detail column added/updated successfully!' AS Status;

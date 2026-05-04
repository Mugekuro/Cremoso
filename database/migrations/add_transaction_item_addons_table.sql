-- =====================================================
-- ADD TRANSACTION_ITEM_ADDONS TABLE
-- This migration creates a proper junction table to replace
-- the JSON addons_detail field in transaction_items
-- =====================================================

USE cremoso_db;

-- Step 1: Create the new junction table
CREATE TABLE IF NOT EXISTS transaction_item_addons (
    transaction_item_addon_id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_item_id INT NOT NULL,
    addon_id INT NOT NULL,
    addon_name VARCHAR(100) NOT NULL COMMENT 'Snapshot of addon name at time of order',
    addon_type ENUM('topping','sauce','fruit') NOT NULL COMMENT 'Snapshot of type',
    price DECIMAL(10,2) NOT NULL COMMENT 'Price at time of order',
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_item_id) REFERENCES transaction_items(transaction_item_id) ON DELETE CASCADE,
    FOREIGN KEY (addon_id) REFERENCES addons(addon_id),
    INDEX idx_transaction_item (transaction_item_id),
    INDEX idx_addon (addon_id)
) COMMENT='Junction table linking transaction items to their selected addons';

SELECT '✓ transaction_item_addons table created successfully!' AS Status;

-- Step 2: Migrate existing JSON data (if any exists)
-- Note: This assumes addons_detail is stored as JSON array like:
-- [{"addon_id": 1, "name": "Rice Crispies", "type": "topping", "price": 10.00, "quantity": 1}]
-- If you have existing data in addons_detail, you'll need to parse and migrate it manually
-- or provide the JSON structure so we can write a proper migration script

SELECT '✓ Ready to migrate existing addons_detail data (manual step required if data exists)' AS Status;

-- Step 3: After migration is complete and verified, you can optionally:
-- ALTER TABLE transaction_items DROP COLUMN addons_detail;
-- (Keep this commented out until you're sure the migration is successful)

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Check the new table structure
DESCRIBE transaction_item_addons;

-- Count existing transaction items
SELECT 
    'Total transaction items' AS Metric,
    COUNT(*) AS Count
FROM transaction_items;

-- Check if any transaction items have addons_detail data
SELECT 
    'Items with addons' AS Metric,
    COUNT(*) AS Count
FROM transaction_items
WHERE addons_detail IS NOT NULL 
  AND addons_detail != 'NONE' 
  AND addons_detail != '';

SELECT '✓ Migration file executed successfully!' AS Status;
SELECT 'Next step: Migrate existing addons_detail JSON data to the new table' AS 'Action Required';

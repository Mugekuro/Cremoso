-- =====================================================
-- DROP TRANSACTION_ITEM_ADDONS TABLE
-- Use this to remove the junction table if needed
-- =====================================================

USE cremoso_db;

-- Drop the table
DROP TABLE IF EXISTS transaction_item_addons;

SELECT '✓ transaction_item_addons table dropped successfully!' AS Status;

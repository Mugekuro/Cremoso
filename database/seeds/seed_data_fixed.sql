-- =====================================================
-- CREMOSO POS - SEED DATA (FIXED)
-- =====================================================
USE cremoso_db;

-- Cleanup
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM transaction_items;
DELETE FROM transactions;
DELETE FROM customers;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE transaction_items AUTO_INCREMENT = 1;
ALTER TABLE transactions AUTO_INCREMENT = 1;
ALTER TABLE customers AUTO_INCREMENT = 1;

-- Customers
INSERT INTO customers (customer_name, phone, email) VALUES
('Walk-in Customer', NULL, NULL),
('Juan Dela Cruz', '09171234567', 'juan.delacruz@email.com'),
('Maria Santos', '09181234568', 'maria.santos@email.com'),
('Pedro Reyes', '09191234569', NULL),
('Ana Garcia', '09201234570', 'ana.garcia@email.com'),
('Carlos Mendoza', '09211234571', NULL),
('Rosa Villanueva', '09221234572', 'rosa.v@email.com'),
('Jose Torres', '09231234573', NULL),
('Luz Fernandez', '09241234574', 'luz.fernandez@email.com'),
('Miguel Castro', '09251234575', NULL),
('Elena Ramos', '09261234576', 'elena.ramos@email.com'),
('Andres Bautista', '09271234577', NULL),
('Sofia Cruz', '09281234578', 'sofia.cruz@email.com'),
('Rafael Aquino', '09291234579', NULL),
('Carmen Lopez', '09301234580', 'carmen.lopez@email.com'),
('Diego Morales', '09311234581', NULL),
('Isabel Reyes', '09321234582', 'isabel.reyes@email.com'),
('Francisco Santos', '09331234583', NULL),
('Gabriela Diaz', '09341234584', 'gab.diaz@email.com'),
('Ricardo Cruz', '09351234585', NULL),
('Teresa Ramos', '09361234586', 'teresa.ramos@email.com'),
('Antonio Flores', '09371234587', NULL),
('Beatriz Gonzales', '09381234588', 'bea.gonzales@email.com'),
('Fernando Silva', '09391234589', NULL),
('Cristina Navarro', '09401234590', 'cristina.n@email.com');

-- Today's Transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, account_name, transaction_date, total_amount, status, notes) VALUES
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0001'), 1, 2, 1, 1, 1, NULL, CONCAT(CURDATE(), ' 09:15:00'), 64.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0002'), 2, 2, 1, 2, 2, 'Juan D.', CONCAT(CURDATE(), ' 10:30:00'), 158.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0003'), 5, 3, 2, 1, 1, NULL, CONCAT(CURDATE(), ' 12:20:00'), 89.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0004'), 8, 2, 1, 3, 3, NULL, CONCAT(CURDATE(), ' 14:00:00'), 234.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0005'), 12, 3, 2, 1, 2, 'Sofia C.', CONCAT(CURDATE(), ' 15:30:00'), 118.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0006'), 15, 2, 1, 2, 2, 'Carmen L.', CONCAT(CURDATE(), ' 16:45:00'), 198.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0007'), 1, 3, 2, 1, 1, NULL, CONCAT(CURDATE(), ' 17:20:00'), 49.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0008'), 18, 2, 1, 1, 1, NULL, CONCAT(CURDATE(), ' 18:00:00'), 139.00, 'completed', NULL);

-- Today's Transaction Items
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(1, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, NULL, 0.00, 50.00, NULL),
(1, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, '[{"addon_id":5,"addon_name":"Chocolate Sprinkles","price":15.00}]', 15.00, 44.00, NULL),
(2, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, '[{"addon_id":10,"addon_name":"Crushed Oreos","price":15.00},{"addon_id":17,"addon_name":"Chocolate","price":20.00}]', 35.00, 114.00, NULL),
(2, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, '[{"addon_id":5,"addon_name":"Chocolate Sprinkles","price":15.00}]', 15.00, 44.00, NULL),
(3, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, NULL, 0.00, 89.00, NULL),
(4, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, '[{"addon_id":26,"addon_name":"Banana","price":15.00},{"addon_id":27,"addon_name":"Mango","price":20.00},{"addon_id":14,"addon_name":"Almond Slices","price":20.00},{"addon_id":16,"addon_name":"Biscoff Crumbs","price":25.00},{"addon_id":22,"addon_name":"Honey","price":20.00}]', 100.00, 289.00, NULL),
(5, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, NULL, 0.00, 118.00, NULL),
(6, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 1, NULL, 0.00, 99.00, NULL),
(6, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 1, '[{"addon_id":12,"addon_name":"Choco Kisses","price":15.00}]', 15.00, 104.00, NULL),
(7, 6, 'Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 49.00, 1, NULL, 0.00, 49.00, NULL),
(8, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 1, '[{"addon_id":21,"addon_name":"Tiger Sugar","price":20.00},{"addon_id":7,"addon_name":"Koko Crunch","price":15.00},{"addon_id":27,"addon_name":"Mango","price":20.00}]', 55.00, 134.00, NULL);

SELECT '✅ Seed data loaded successfully!' AS '';
SELECT CONCAT('📊 ', COUNT(*), ' transactions created') AS '' FROM transactions;
SELECT CONCAT('🛒 ', COUNT(*), ' transaction items created') AS '' FROM transaction_items;
SELECT CONCAT('👥 ', COUNT(*), ' customers added') AS '' FROM customers;

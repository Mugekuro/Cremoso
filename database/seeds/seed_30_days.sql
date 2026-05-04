-- =====================================================
-- CREMOSO POS - 30 DAYS TRANSACTION DATA
-- 3-6 transactions per day
-- =====================================================
USE cremoso_db;

-- Cleanup existing data
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM transaction_items WHERE transaction_id > 0;
DELETE FROM transactions WHERE transaction_id > 0;
DELETE FROM customers WHERE customer_id > 1;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE transaction_items AUTO_INCREMENT = 1;
ALTER TABLE transactions AUTO_INCREMENT = 1;
ALTER TABLE customers AUTO_INCREMENT = 2;

-- Insert additional customers
INSERT INTO customers (customer_name) VALUES
('Juan Dela Cruz'),('Maria Santos'),('Pedro Reyes'),('Ana Garcia'),('Carlos Mendoza'),
('Rosa Villanueva'),('Jose Torres'),('Luz Fernandez'),('Miguel Castro'),('Elena Ramos'),
('Andres Bautista'),('Sofia Cruz'),('Rafael Aquino'),('Carmen Lopez'),('Diego Morales'),
('Isabel Reyes'),('Francisco Santos'),('Gabriela Diaz'),('Ricardo Cruz'),('Teresa Ramos');

-- DAY 1 (30 days ago) - 4 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260404-0001', 2, 2, 1, 1, 1, '2026-04-04 09:15:00', 75.00, 'completed'),
('ORD-20260404-0002', 5, 2, 1, 2, 2, '2026-04-04 12:30:00', 158.00, 'completed'),
('ORD-20260404-0003', 8, 3, 2, 1, 1, '2026-04-04 15:45:00', 234.00, 'completed'),
('ORD-20260404-0004', 12, 2, 1, 3, 3, '2026-04-04 18:20:00', 118.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(1, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 3, 0, 75.00),
(2, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 0, 158.00),
(3, 6, 'Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 49.00, 3, 39.00, 186.00),
(3, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, 19.00, 48.00),
(4, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00);

-- DAY 2 (29 days ago) - 5 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260405-0001', 3, 2, 1, 1, 1, '2026-04-05 08:30:00', 89.00, 'completed'),
('ORD-20260405-0002', 7, 3, 2, 1, 2, '2026-04-05 11:15:00', 198.00, 'completed'),
('ORD-20260405-0003', 15, 2, 1, 2, 2, '2026-04-05 13:45:00', 267.00, 'completed'),
('ORD-20260405-0004', 10, 3, 2, 1, 1, '2026-04-05 16:30:00', 139.00, 'completed'),
('ORD-20260405-0005', 18, 2, 1, 3, 3, '2026-04-05 19:00:00', 344.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(5, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, 0, 89.00),
(6, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00),
(7, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 3, 0, 267.00),
(8, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(8, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(9, 6, 'Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 49.00, 4, 148.00, 344.00);

-- DAY 3 (28 days ago) - 3 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260406-0001', 4, 2, 1, 1, 1, '2026-04-06 10:00:00', 178.00, 'completed'),
('ORD-20260406-0002', 14, 3, 2, 2, 2, '2026-04-06 14:20:00', 213.00, 'completed'),
('ORD-20260406-0003', 20, 2, 1, 1, 1, '2026-04-06 17:45:00', 99.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(10, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 1, 35.00, 134.00),
(10, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, 15.00, 44.00),
(11, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00),
(12, 19, 'Float - Caramel Macchiato - 16oz', 'Float', '16oz', 99.00, 1, 0, 99.00);

-- DAY 4 (27 days ago) - 6 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260407-0001', 6, 2, 1, 1, 1, '2026-04-07 09:00:00', 125.00, 'completed'),
('ORD-20260407-0002', 11, 3, 2, 1, 2, '2026-04-07 11:30:00', 158.00, 'completed'),
('ORD-20260407-0003', 16, 2, 1, 2, 2, '2026-04-07 13:00:00', 289.00, 'completed'),
('ORD-20260407-0004', 1, 3, 2, 1, 1, '2026-04-07 15:15:00', 64.00, 'completed'),
('ORD-20260407-0005', 19, 2, 1, 3, 3, '2026-04-07 17:30:00', 198.00, 'completed'),
('ORD-20260407-0006', 9, 3, 2, 1, 1, '2026-04-07 19:45:00', 178.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(13, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 5, 0, 125.00),
(14, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 0, 158.00),
(15, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 45.00, 234.00),
(15, 3, 'Soft-serve - Vanilla - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 39.00, 1, 16.00, 55.00),
(16, 5, 'Soft-serve - Chocolate - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 39.00, 1, 25.00, 64.00),
(17, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00),
(18, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00);

-- DAY 5 (26 days ago) - 4 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260408-0001', 13, 2, 1, 1, 1, '2026-04-08 08:45:00', 234.00, 'completed'),
('ORD-20260408-0002', 17, 3, 2, 2, 2, '2026-04-08 12:15:00', 118.00, 'completed'),
('ORD-20260408-0003', 21, 2, 1, 1, 1, '2026-04-08 15:30:00', 267.00, 'completed'),
('ORD-20260408-0004', 2, 3, 2, 3, 3, '2026-04-08 18:50:00', 344.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(19, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00),
(20, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00),
(21, 6, 'Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 49.00, 3, 120.00, 267.00),
(22, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(22, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00);


-- DAY 6 (25 days ago) - 5 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260409-0001', 5, 2, 1, 1, 1, '2026-04-09 09:30:00', 139.00, 'completed'),
('ORD-20260409-0002', 8, 3, 2, 1, 2, '2026-04-09 11:45:00', 198.00, 'completed'),
('ORD-20260409-0003', 12, 2, 1, 2, 2, '2026-04-09 14:00:00', 289.00, 'completed'),
('ORD-20260409-0004', 15, 3, 2, 1, 1, '2026-04-09 16:20:00', 89.00, 'completed'),
('ORD-20260409-0005', 18, 2, 1, 3, 3, '2026-04-09 19:10:00', 213.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(23, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(23, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(24, 19, 'Float - Caramel Macchiato - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00),
(25, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 55.00, 244.00),
(25, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, 16.00, 45.00),
(26, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, 0, 89.00),
(27, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00);

-- DAY 7 (24 days ago) - 6 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260410-0001', 3, 2, 1, 1, 1, '2026-04-10 08:20:00', 75.00, 'completed'),
('ORD-20260410-0002', 7, 3, 2, 1, 2, '2026-04-10 10:50:00', 158.00, 'completed'),
('ORD-20260410-0003', 14, 2, 1, 2, 2, '2026-04-10 13:15:00', 234.00, 'completed'),
('ORD-20260410-0004', 19, 3, 2, 1, 1, '2026-04-10 15:40:00', 118.00, 'completed'),
('ORD-20260410-0005', 10, 2, 1, 3, 3, '2026-04-10 17:55:00', 267.00, 'completed'),
('ORD-20260410-0006', 16, 3, 2, 1, 1, '2026-04-10 20:00:00', 178.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(28, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 3, 0, 75.00),
(29, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 0, 158.00),
(30, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00),
(31, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00),
(32, 6, 'Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 49.00, 3, 120.00, 267.00),
(33, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00);

-- DAY 8 (23 days ago) - 4 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260411-0001', 4, 2, 1, 1, 1, '2026-04-11 09:10:00', 344.00, 'completed'),
('ORD-20260411-0002', 11, 3, 2, 2, 2, '2026-04-11 12:30:00', 198.00, 'completed'),
('ORD-20260411-0003', 20, 2, 1, 1, 1, '2026-04-11 15:45:00', 139.00, 'completed'),
('ORD-20260411-0004', 6, 3, 2, 3, 3, '2026-04-11 18:30:00', 289.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(34, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(34, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00),
(35, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00),
(36, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(36, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(37, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 45.00, 234.00),
(37, 3, 'Soft-serve - Vanilla - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 39.00, 1, 16.00, 55.00);

-- DAY 9 (22 days ago) - 5 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260412-0001', 9, 2, 1, 1, 1, '2026-04-12 08:40:00', 125.00, 'completed'),
('ORD-20260412-0002', 13, 3, 2, 1, 2, '2026-04-12 11:20:00', 213.00, 'completed'),
('ORD-20260412-0003', 17, 2, 1, 2, 2, '2026-04-12 14:10:00', 267.00, 'completed'),
('ORD-20260412-0004', 21, 3, 2, 1, 1, '2026-04-12 16:35:00', 89.00, 'completed'),
('ORD-20260412-0005', 2, 2, 1, 3, 3, '2026-04-12 19:20:00', 178.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(38, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 5, 0, 125.00),
(39, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00),
(40, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 3, 0, 267.00),
(41, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, 0, 89.00),
(42, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00);

-- DAY 10 (21 days ago) - 3 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260413-0001', 5, 2, 1, 1, 1, '2026-04-13 10:15:00', 234.00, 'completed'),
('ORD-20260413-0002', 8, 3, 2, 2, 2, '2026-04-13 13:40:00', 118.00, 'completed'),
('ORD-20260413-0003', 12, 2, 1, 1, 1, '2026-04-13 17:00:00', 198.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(43, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00),
(44, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00),
(45, 19, 'Float - Caramel Macchiato - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00);

-- DAY 11 (20 days ago) - 6 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260414-0001', 15, 2, 1, 1, 1, '2026-04-14 08:50:00', 139.00, 'completed'),
('ORD-20260414-0002', 18, 3, 2, 1, 2, '2026-04-14 11:10:00', 289.00, 'completed'),
('ORD-20260414-0003', 3, 2, 1, 2, 2, '2026-04-14 13:30:00', 344.00, 'completed'),
('ORD-20260414-0004', 7, 3, 2, 1, 1, '2026-04-14 15:50:00', 64.00, 'completed'),
('ORD-20260414-0005', 14, 2, 1, 3, 3, '2026-04-14 18:15:00', 213.00, 'completed'),
('ORD-20260414-0006', 19, 3, 2, 1, 1, '2026-04-14 20:30:00', 178.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(46, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(46, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(47, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 55.00, 244.00),
(47, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, 16.00, 45.00),
(48, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(48, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00),
(49, 5, 'Soft-serve - Chocolate - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 39.00, 1, 25.00, 64.00),
(50, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00),
(51, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00);

-- DAY 12 (19 days ago) - 4 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260415-0001', 10, 2, 1, 1, 1, '2026-04-15 09:25:00', 75.00, 'completed'),
('ORD-20260415-0002', 16, 3, 2, 2, 2, '2026-04-15 12:45:00', 158.00, 'completed'),
('ORD-20260415-0003', 20, 2, 1, 1, 1, '2026-04-15 15:20:00', 234.00, 'completed'),
('ORD-20260415-0004', 4, 3, 2, 3, 3, '2026-04-15 18:40:00', 267.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(52, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 3, 0, 75.00),
(53, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 0, 158.00),
(54, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00),
(55, 6, 'Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 49.00, 3, 120.00, 267.00);

-- DAY 13 (18 days ago) - 5 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260416-0001', 6, 2, 1, 1, 1, '2026-04-16 08:35:00', 118.00, 'completed'),
('ORD-20260416-0002', 11, 3, 2, 1, 2, '2026-04-16 11:00:00', 198.00, 'completed'),
('ORD-20260416-0003', 21, 2, 1, 2, 2, '2026-04-16 13:50:00', 289.00, 'completed'),
('ORD-20260416-0004', 9, 3, 2, 1, 1, '2026-04-16 16:10:00', 89.00, 'completed'),
('ORD-20260416-0005', 13, 2, 1, 3, 3, '2026-04-16 19:25:00', 213.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(56, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00),
(57, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00),
(58, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 45.00, 234.00),
(58, 3, 'Soft-serve - Vanilla - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 39.00, 1, 16.00, 55.00),
(59, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, 0, 89.00),
(60, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00);

-- DAY 14 (17 days ago) - 6 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260417-0001', 17, 2, 1, 1, 1, '2026-04-17 09:05:00', 344.00, 'completed'),
('ORD-20260417-0002', 2, 3, 2, 1, 2, '2026-04-17 11:30:00', 139.00, 'completed'),
('ORD-20260417-0003', 5, 2, 1, 2, 2, '2026-04-17 13:45:00', 178.00, 'completed'),
('ORD-20260417-0004', 8, 3, 2, 1, 1, '2026-04-17 16:00:00', 125.00, 'completed'),
('ORD-20260417-0005', 12, 2, 1, 3, 3, '2026-04-17 18:20:00', 267.00, 'completed'),
('ORD-20260417-0006', 15, 3, 2, 1, 1, '2026-04-17 20:45:00', 234.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(61, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(61, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00),
(62, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(62, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(63, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00),
(64, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 5, 0, 125.00),
(65, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 3, 0, 267.00),
(66, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00);

-- DAY 15 (16 days ago) - 4 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260418-0001', 18, 2, 1, 1, 1, '2026-04-18 10:20:00', 213.00, 'completed'),
('ORD-20260418-0002', 3, 3, 2, 2, 2, '2026-04-18 13:10:00', 198.00, 'completed'),
('ORD-20260418-0003', 7, 2, 1, 1, 1, '2026-04-18 16:25:00', 289.00, 'completed'),
('ORD-20260418-0004', 14, 3, 2, 3, 3, '2026-04-18 19:00:00', 118.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(67, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00),
(68, 19, 'Float - Caramel Macchiato - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00),
(69, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 55.00, 244.00),
(69, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, 16.00, 45.00),
(70, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00);


-- DAY 16 (15 days ago) - 5 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260419-0001', 19, 2, 1, 1, 1, '2026-04-19 08:55:00', 89.00, 'completed'),
('ORD-20260419-0002', 10, 3, 2, 1, 2, '2026-04-19 11:40:00', 178.00, 'completed'),
('ORD-20260419-0003', 16, 2, 1, 2, 2, '2026-04-19 14:15:00', 344.00, 'completed'),
('ORD-20260419-0004', 20, 3, 2, 1, 1, '2026-04-19 16:50:00', 139.00, 'completed'),
('ORD-20260419-0005', 4, 2, 1, 3, 3, '2026-04-19 19:30:00', 234.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(71, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, 0, 89.00),
(72, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00),
(73, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(73, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00),
(74, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(74, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(75, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00);

-- DAY 17 (14 days ago) - 3 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260420-0001', 6, 2, 1, 1, 1, '2026-04-20 09:40:00', 267.00, 'completed'),
('ORD-20260420-0002', 11, 3, 2, 2, 2, '2026-04-20 13:25:00', 125.00, 'completed'),
('ORD-20260420-0003', 21, 2, 1, 1, 1, '2026-04-20 17:10:00', 198.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(76, 6, 'Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 49.00, 3, 120.00, 267.00),
(77, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 5, 0, 125.00),
(78, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00);

-- DAY 18 (13 days ago) - 6 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260421-0001', 9, 2, 1, 1, 1, '2026-04-21 08:30:00', 213.00, 'completed'),
('ORD-20260421-0002', 13, 3, 2, 1, 2, '2026-04-21 10:55:00', 289.00, 'completed'),
('ORD-20260421-0003', 17, 2, 1, 2, 2, '2026-04-21 13:20:00', 118.00, 'completed'),
('ORD-20260421-0004', 2, 3, 2, 1, 1, '2026-04-21 15:45:00', 64.00, 'completed'),
('ORD-20260421-0005', 5, 2, 1, 3, 3, '2026-04-21 18:05:00', 178.00, 'completed'),
('ORD-20260421-0006', 8, 3, 2, 1, 1, '2026-04-21 20:20:00', 344.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(79, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00),
(80, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 55.00, 244.00),
(80, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, 16.00, 45.00),
(81, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00),
(82, 5, 'Soft-serve - Chocolate - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 39.00, 1, 25.00, 64.00),
(83, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00),
(84, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(84, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00);

-- DAY 19 (12 days ago) - 4 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260422-0001', 12, 2, 1, 1, 1, '2026-04-22 09:15:00', 139.00, 'completed'),
('ORD-20260422-0002', 15, 3, 2, 2, 2, '2026-04-22 12:30:00', 234.00, 'completed'),
('ORD-20260422-0003', 18, 2, 1, 1, 1, '2026-04-22 15:50:00', 198.00, 'completed'),
('ORD-20260422-0004', 3, 3, 2, 3, 3, '2026-04-22 18:35:00', 267.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(85, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(85, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(86, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00),
(87, 19, 'Float - Caramel Macchiato - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00),
(88, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 3, 0, 267.00);

-- DAY 20 (11 days ago) - 5 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260423-0001', 7, 2, 1, 1, 1, '2026-04-23 08:45:00', 75.00, 'completed'),
('ORD-20260423-0002', 14, 3, 2, 1, 2, '2026-04-23 11:20:00', 158.00, 'completed'),
('ORD-20260423-0003', 19, 2, 1, 2, 2, '2026-04-23 14:00:00', 289.00, 'completed'),
('ORD-20260423-0004', 10, 3, 2, 1, 1, '2026-04-23 16:30:00', 89.00, 'completed'),
('ORD-20260423-0005', 16, 2, 1, 3, 3, '2026-04-23 19:15:00', 213.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(89, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 3, 0, 75.00),
(90, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 0, 158.00),
(91, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 45.00, 234.00),
(91, 3, 'Soft-serve - Vanilla - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 39.00, 1, 16.00, 55.00),
(92, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, 0, 89.00),
(93, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00);

-- DAY 21 (10 days ago) - 6 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260424-0001', 20, 2, 1, 1, 1, '2026-04-24 09:00:00', 344.00, 'completed'),
('ORD-20260424-0002', 4, 3, 2, 1, 2, '2026-04-24 11:35:00', 178.00, 'completed'),
('ORD-20260424-0003', 6, 2, 1, 2, 2, '2026-04-24 13:55:00', 125.00, 'completed'),
('ORD-20260424-0004', 11, 3, 2, 1, 1, '2026-04-24 16:15:00', 267.00, 'completed'),
('ORD-20260424-0005', 21, 2, 1, 3, 3, '2026-04-24 18:40:00', 234.00, 'completed'),
('ORD-20260424-0006', 9, 3, 2, 1, 1, '2026-04-24 20:50:00', 118.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(94, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(94, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00),
(95, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00),
(96, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 5, 0, 125.00),
(97, 6, 'Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 49.00, 3, 120.00, 267.00),
(98, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00),
(99, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00);

-- DAY 22 (9 days ago) - 4 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260425-0001', 13, 2, 1, 1, 1, '2026-04-25 08:20:00', 198.00, 'completed'),
('ORD-20260425-0002', 17, 3, 2, 2, 2, '2026-04-25 12:10:00', 139.00, 'completed'),
('ORD-20260425-0003', 2, 2, 1, 1, 1, '2026-04-25 15:30:00', 289.00, 'completed'),
('ORD-20260425-0004', 5, 3, 2, 3, 3, '2026-04-25 18:55:00', 213.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(100, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00),
(101, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(101, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(102, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 55.00, 244.00),
(102, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, 16.00, 45.00),
(103, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00);

-- DAY 23 (8 days ago) - 5 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260426-0001', 8, 2, 1, 1, 1, '2026-04-26 09:30:00', 89.00, 'completed'),
('ORD-20260426-0002', 12, 3, 2, 1, 2, '2026-04-26 11:50:00', 178.00, 'completed'),
('ORD-20260426-0003', 15, 2, 1, 2, 2, '2026-04-26 14:20:00', 344.00, 'completed'),
('ORD-20260426-0004', 18, 3, 2, 1, 1, '2026-04-26 16:45:00', 125.00, 'completed'),
('ORD-20260426-0005', 3, 2, 1, 3, 3, '2026-04-26 19:10:00', 267.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(104, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, 0, 89.00),
(105, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00),
(106, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(106, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00),
(107, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 5, 0, 125.00),
(108, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 3, 0, 267.00);

-- DAY 24 (7 days ago) - 3 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260427-0001', 7, 2, 1, 1, 1, '2026-04-27 10:05:00', 234.00, 'completed'),
('ORD-20260427-0002', 14, 3, 2, 2, 2, '2026-04-27 13:35:00', 118.00, 'completed'),
('ORD-20260427-0003', 19, 2, 1, 1, 1, '2026-04-27 17:20:00', 198.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(109, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00),
(110, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00),
(111, 19, 'Float - Caramel Macchiato - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00);

-- DAY 25 (6 days ago) - 6 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260428-0001', 10, 2, 1, 1, 1, '2026-04-28 08:40:00', 139.00, 'completed'),
('ORD-20260428-0002', 16, 3, 2, 1, 2, '2026-04-28 11:05:00', 289.00, 'completed'),
('ORD-20260428-0003', 20, 2, 1, 2, 2, '2026-04-28 13:25:00', 344.00, 'completed'),
('ORD-20260428-0004', 4, 3, 2, 1, 1, '2026-04-28 15:50:00', 64.00, 'completed'),
('ORD-20260428-0005', 6, 2, 1, 3, 3, '2026-04-28 18:10:00', 213.00, 'completed'),
('ORD-20260428-0006', 11, 3, 2, 1, 1, '2026-04-28 20:35:00', 178.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(112, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(112, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(113, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 55.00, 244.00),
(113, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, 16.00, 45.00),
(114, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(114, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00),
(115, 5, 'Soft-serve - Chocolate - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 39.00, 1, 25.00, 64.00),
(116, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00),
(117, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00);


-- DAY 26 (5 days ago) - 4 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260429-0001', 21, 2, 1, 1, 1, '2026-04-29 09:20:00', 75.00, 'completed'),
('ORD-20260429-0002', 9, 3, 2, 2, 2, '2026-04-29 12:40:00', 158.00, 'completed'),
('ORD-20260429-0003', 13, 2, 1, 1, 1, '2026-04-29 15:55:00', 234.00, 'completed'),
('ORD-20260429-0004', 17, 3, 2, 3, 3, '2026-04-29 18:25:00', 267.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(118, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 3, 0, 75.00),
(119, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 0, 158.00),
(120, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00),
(121, 6, 'Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 49.00, 3, 120.00, 267.00);

-- DAY 27 (4 days ago) - 5 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260430-0001', 2, 2, 1, 1, 1, '2026-04-30 08:50:00', 118.00, 'completed'),
('ORD-20260430-0002', 5, 3, 2, 1, 2, '2026-04-30 11:15:00', 198.00, 'completed'),
('ORD-20260430-0003', 8, 2, 1, 2, 2, '2026-04-30 13:40:00', 289.00, 'completed'),
('ORD-20260430-0004', 12, 3, 2, 1, 1, '2026-04-30 16:05:00', 89.00, 'completed'),
('ORD-20260430-0005', 15, 2, 1, 3, 3, '2026-04-30 19:30:00', 213.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(122, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00),
(123, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00),
(124, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 45.00, 234.00),
(124, 3, 'Soft-serve - Vanilla - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 39.00, 1, 16.00, 55.00),
(125, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, 0, 89.00),
(126, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00);

-- DAY 28 (3 days ago) - 6 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260501-0001', 18, 2, 1, 1, 1, '2026-05-01 09:10:00', 344.00, 'completed'),
('ORD-20260501-0002', 3, 3, 2, 1, 2, '2026-05-01 11:40:00', 139.00, 'completed'),
('ORD-20260501-0003', 7, 2, 1, 2, 2, '2026-05-01 14:00:00', 178.00, 'completed'),
('ORD-20260501-0004', 14, 3, 2, 1, 1, '2026-05-01 16:25:00', 125.00, 'completed'),
('ORD-20260501-0005', 19, 2, 1, 3, 3, '2026-05-01 18:50:00', 267.00, 'completed'),
('ORD-20260501-0006', 10, 3, 2, 1, 1, '2026-05-01 20:15:00', 234.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(127, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(127, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00),
(128, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(128, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(129, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00),
(130, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 5, 0, 125.00),
(131, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 3, 0, 267.00),
(132, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00);

-- DAY 29 (2 days ago) - 4 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260502-0001', 16, 2, 1, 1, 1, '2026-05-02 08:35:00', 213.00, 'completed'),
('ORD-20260502-0002', 20, 3, 2, 2, 2, '2026-05-02 12:00:00', 198.00, 'completed'),
('ORD-20260502-0003', 4, 2, 1, 1, 1, '2026-05-02 15:20:00', 289.00, 'completed'),
('ORD-20260502-0004', 6, 3, 2, 3, 3, '2026-05-02 18:45:00', 118.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(133, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 24.00, 213.00),
(134, 19, 'Float - Caramel Macchiato - 16oz', 'Float', '16oz', 99.00, 2, 0, 198.00),
(135, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, 55.00, 244.00),
(135, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, 16.00, 45.00),
(136, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, 0, 118.00);

-- DAY 30 (yesterday) - 5 transactions
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, transaction_date, total_amount, status) VALUES
('ORD-20260503-0001', 11, 2, 1, 1, 1, '2026-05-03 09:00:00', 89.00, 'completed'),
('ORD-20260503-0002', 21, 3, 2, 1, 2, '2026-05-03 11:30:00', 178.00, 'completed'),
('ORD-20260503-0003', 9, 2, 1, 2, 2, '2026-05-03 14:15:00', 344.00, 'completed'),
('ORD-20260503-0004', 13, 3, 2, 1, 1, '2026-05-03 16:40:00', 139.00, 'completed'),
('ORD-20260503-0005', 17, 2, 1, 3, 3, '2026-05-03 19:05:00', 234.00, 'completed');

INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_total, subtotal) VALUES
(137, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, 0, 89.00),
(138, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 2, 20.00, 178.00),
(139, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 2, 86.00, 284.00),
(139, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 2, 10.00, 60.00),
(140, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, 20.00, 99.00),
(140, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 1, 5.00, 40.00),
(141, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 2, 56.00, 234.00);

-- =====================================================
-- SUMMARY
-- =====================================================
SELECT '✓ 30 days of transaction data created successfully!' AS Status;
SELECT 
    COUNT(DISTINCT DATE(transaction_date)) AS 'Days with Data',
    COUNT(*) AS 'Total Transactions',
    SUM(total_amount) AS 'Total Revenue (₱)',
    AVG(total_amount) AS 'Average Order (₱)',
    MIN(transaction_date) AS 'First Transaction',
    MAX(transaction_date) AS 'Last Transaction'
FROM transactions;

SELECT 
    DATE(transaction_date) AS 'Date',
    COUNT(*) AS 'Transactions',
    SUM(total_amount) AS 'Daily Revenue (₱)'
FROM transactions
GROUP BY DATE(transaction_date)
ORDER BY DATE(transaction_date) DESC
LIMIT 10;

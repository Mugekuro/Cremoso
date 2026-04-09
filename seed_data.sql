USE cremoso_db;

-- =====================================================
-- SEED DATA: Realistic sample transactions & customers
-- Generates ~50 transactions across the last 7 days
-- plus some today to populate all dashboard widgets
-- =====================================================

-- Additional customers
INSERT INTO customers (customer_name, contact) VALUES
('Juan Dela Cruz', '0917-123-4567'),
('Maria Santos', '0918-234-5678'),
('Pedro Reyes', '0919-345-6789'),
('Ana Garcia', '0920-456-7890'),
('Carlos Mendoza', '0921-567-8901'),
('Rosa Villanueva', '0922-678-9012'),
('Jose Torres', '0923-789-0123'),
('Luz Fernandez', '0924-890-1234'),
('Miguel Castro', '0925-901-2345'),
('Elena Ramos', '0926-012-3456'),
('Andres Bautista', '0927-123-4567'),
('Sofia Cruz', '0928-234-5678'),
('Rafael Aquino', '0929-345-6789'),
('Carmen Lopez', '0930-456-7890'),
('Diego Morales', '0931-567-8901'),
('Isabel Reyes', '0932-678-9012'),
('Francisco Santos', '0933-789-0123'),
('Gabriela Diaz', '0934-890-1234'),
('Ricardo Cruz', '0935-901-2345');

-- =====================================================
-- TRANSACTIONS (spread across last 7 days + today)
-- Each transaction has a unique order number
-- =====================================================

-- Helper variables for generating data
-- We'll use a stored procedure approach for MySQL compatibility

DROP PROCEDURE IF EXISTS generate_seed_data;

DELIMITER $$
CREATE PROCEDURE generate_seed_data()
BEGIN
    DECLARE txn_counter INT DEFAULT 1;
    DECLARE order_num VARCHAR(50);
    DECLARE cust_id INT;
    DECLARE staff_id INT;
    DECLARE branch_id INT;
    DECLARE channel_id INT;
    DECLARE payment_id INT;
    DECLARE total DECIMAL(10,2);
    DECLARE txn_date DATETIME;
    DECLARE item_id INT;
    DECLARE qty INT;
    DECLARE unit_prc DECIMAL(10,2);
    DECLARE subtotal DECIMAL(10,2);
    DECLARE day_offset INT;
    DECLARE hour_val INT;
    DECLARE minute_val INT;

    -- Transaction 1-8: Today (spread across hours 8-18)
    WHILE txn_counter <= 8 DO
        SET order_num = CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-', LPAD(txn_counter, 4, '0'));
        SET cust_id = FLOOR(1 + RAND() * 20);
        SET staff_id = IF(RAND() > 0.5, 2, 3);
        SET branch_id = IF(RAND() > 0.5, 1, 2);
        SET channel_id = FLOOR(1 + RAND() * 3);
        SET payment_id = FLOOR(1 + RAND() * 3);
        SET hour_val = FLOOR(8 + RAND() * 10);
        SET minute_val = FLOOR(RAND() * 60);
        SET txn_date = CONCAT(CURDATE(), ' ', LPAD(hour_val, 2, '0'), ':', LPAD(minute_val, 2, '0'), ':00');
        SET total = ROUND(50 + RAND() * 200, 2);

        INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, total_amount, transaction_date)
        VALUES (order_num, cust_id, staff_id, branch_id, channel_id, payment_id, total, txn_date);

        SET @last_txn_id = LAST_INSERT_ID();

        -- Add 1-3 items per transaction
        SET @item_count = FLOOR(1 + RAND() * 3);
        SET @i = 0;
        WHILE @i < @item_count DO
            SET item_id = FLOOR(1 + RAND() * 9);
            SET qty = FLOOR(1 + RAND() * 3);
            SELECT base_price INTO unit_prc FROM items WHERE item_id = item_id LIMIT 1;
            SET subtotal = ROUND(unit_prc * qty, 2);
            INSERT INTO transaction_items (transaction_id, item_id, quantity, unit_price, subtotal)
            VALUES (@last_txn_id, item_id, qty, unit_prc, subtotal);
            SET @i = @i + 1;
        END WHILE;

        SET txn_counter = txn_counter + 1;
    END WHILE;

    -- Transactions 9-25: Yesterday
    SET txn_counter = 1;
    WHILE txn_counter <= 17 DO
        SET order_num = CONCAT('ORD-', DATE_FORMAT(CURDATE() - INTERVAL 1 DAY, '%Y%m%d'), '-', LPAD(txn_counter, 4, '0'));
        SET cust_id = FLOOR(1 + RAND() * 20);
        SET staff_id = IF(RAND() > 0.5, 2, 3);
        SET branch_id = IF(RAND() > 0.5, 1, 2);
        SET channel_id = FLOOR(1 + RAND() * 3);
        SET payment_id = FLOOR(1 + RAND() * 3);
        SET hour_val = FLOOR(9 + RAND() * 9);
        SET minute_val = FLOOR(RAND() * 60);
        SET txn_date = CONCAT(CURDATE() - INTERVAL 1 DAY, ' ', LPAD(hour_val, 2, '0'), ':', LPAD(minute_val, 2, '0'), ':00');
        SET total = ROUND(50 + RAND() * 250, 2);

        INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, total_amount, transaction_date)
        VALUES (order_num, cust_id, staff_id, branch_id, channel_id, payment_id, total, txn_date);

        SET @last_txn_id = LAST_INSERT_ID();

        SET @item_count = FLOOR(1 + RAND() * 3);
        SET @i = 0;
        WHILE @i < @item_count DO
            SET item_id = FLOOR(1 + RAND() * 9);
            SET qty = FLOOR(1 + RAND() * 3);
            SELECT base_price INTO unit_prc FROM items WHERE item_id = item_id LIMIT 1;
            SET subtotal = ROUND(unit_prc * qty, 2);
            INSERT INTO transaction_items (transaction_id, item_id, quantity, unit_price, subtotal)
            VALUES (@last_txn_id, item_id, qty, unit_prc, subtotal);
            SET @i = @i + 1;
        END WHILE;

        SET txn_counter = txn_counter + 1;
    END WHILE;

    -- Transactions 26-38: 2 days ago
    SET txn_counter = 1;
    WHILE txn_counter <= 13 DO
        SET order_num = CONCAT('ORD-', DATE_FORMAT(CURDATE() - INTERVAL 2 DAY, '%Y%m%d'), '-', LPAD(txn_counter, 4, '0'));
        SET cust_id = FLOOR(1 + RAND() * 20);
        SET staff_id = IF(RAND() > 0.5, 2, 3);
        SET branch_id = IF(RAND() > 0.5, 1, 2);
        SET channel_id = FLOOR(1 + RAND() * 3);
        SET payment_id = FLOOR(1 + RAND() * 3);
        SET hour_val = FLOOR(9 + RAND() * 9);
        SET minute_val = FLOOR(RAND() * 60);
        SET txn_date = CONCAT(CURDATE() - INTERVAL 2 DAY, ' ', LPAD(hour_val, 2, '0'), ':', LPAD(minute_val, 2, '0'), ':00');
        SET total = ROUND(55 + RAND() * 200, 2);

        INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, total_amount, transaction_date)
        VALUES (order_num, cust_id, staff_id, branch_id, channel_id, payment_id, total, txn_date);

        SET @last_txn_id = LAST_INSERT_ID();

        SET @item_count = FLOOR(1 + RAND() * 3);
        SET @i = 0;
        WHILE @i < @item_count DO
            SET item_id = FLOOR(1 + RAND() * 9);
            SET qty = FLOOR(1 + RAND() * 3);
            SELECT base_price INTO unit_prc FROM items WHERE item_id = item_id LIMIT 1;
            SET subtotal = ROUND(unit_prc * qty, 2);
            INSERT INTO transaction_items (transaction_id, item_id, quantity, unit_price, subtotal)
            VALUES (@last_txn_id, item_id, qty, unit_prc, subtotal);
            SET @i = @i + 1;
        END WHILE;

        SET txn_counter = txn_counter + 1;
    END WHILE;

    -- Transactions 39-50: 3 days ago
    SET txn_counter = 1;
    WHILE txn_counter <= 12 DO
        SET order_num = CONCAT('ORD-', DATE_FORMAT(CURDATE() - INTERVAL 3 DAY, '%Y%m%d'), '-', LPAD(txn_counter, 4, '0'));
        SET cust_id = FLOOR(1 + RAND() * 20);
        SET staff_id = IF(RAND() > 0.5, 2, 3);
        SET branch_id = IF(RAND() > 0.5, 1, 2);
        SET channel_id = FLOOR(1 + RAND() * 3);
        SET payment_id = FLOOR(1 + RAND() * 3);
        SET hour_val = FLOOR(9 + RAND() * 9);
        SET minute_val = FLOOR(RAND() * 60);
        SET txn_date = CONCAT(CURDATE() - INTERVAL 3 DAY, ' ', LPAD(hour_val, 2, '0'), ':', LPAD(minute_val, 2, '0'), ':00');
        SET total = ROUND(50 + RAND() * 180, 2);

        INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, total_amount, transaction_date)
        VALUES (order_num, cust_id, staff_id, branch_id, channel_id, payment_id, total, txn_date);

        SET @last_txn_id = LAST_INSERT_ID();

        SET @item_count = FLOOR(1 + RAND() * 3);
        SET @i = 0;
        WHILE @i < @item_count DO
            SET item_id = FLOOR(1 + RAND() * 9);
            SET qty = FLOOR(1 + RAND() * 3);
            SELECT base_price INTO unit_prc FROM items WHERE item_id = item_id LIMIT 1;
            SET subtotal = ROUND(unit_prc * qty, 2);
            INSERT INTO transaction_items (transaction_id, item_id, quantity, unit_price, subtotal)
            VALUES (@last_txn_id, item_id, qty, unit_prc, subtotal);
            SET @i = @i + 1;
        END WHILE;

        SET txn_counter = txn_counter + 1;
    END WHILE;

    -- Transactions 51-60: 4 days ago
    SET txn_counter = 1;
    WHILE txn_counter <= 10 DO
        SET order_num = CONCAT('ORD-', DATE_FORMAT(CURDATE() - INTERVAL 4 DAY, '%Y%m%d'), '-', LPAD(txn_counter, 4, '0'));
        SET cust_id = FLOOR(1 + RAND() * 20);
        SET staff_id = IF(RAND() > 0.5, 2, 3);
        SET branch_id = IF(RAND() > 0.5, 1, 2);
        SET channel_id = FLOOR(1 + RAND() * 3);
        SET payment_id = FLOOR(1 + RAND() * 3);
        SET hour_val = FLOOR(9 + RAND() * 9);
        SET minute_val = FLOOR(RAND() * 60);
        SET txn_date = CONCAT(CURDATE() - INTERVAL 4 DAY, ' ', LPAD(hour_val, 2, '0'), ':', LPAD(minute_val, 2, '0'), ':00');
        SET total = ROUND(50 + RAND() * 200, 2);

        INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, total_amount, transaction_date)
        VALUES (order_num, cust_id, staff_id, branch_id, channel_id, payment_id, total, txn_date);

        SET @last_txn_id = LAST_INSERT_ID();

        SET @item_count = FLOOR(1 + RAND() * 3);
        SET @i = 0;
        WHILE @i < @item_count DO
            SET item_id = FLOOR(1 + RAND() * 9);
            SET qty = FLOOR(1 + RAND() * 3);
            SELECT base_price INTO unit_prc FROM items WHERE item_id = item_id LIMIT 1;
            SET subtotal = ROUND(unit_prc * qty, 2);
            INSERT INTO transaction_items (transaction_id, item_id, quantity, unit_price, subtotal)
            VALUES (@last_txn_id, item_id, qty, unit_prc, subtotal);
            SET @i = @i + 1;
        END WHILE;

        SET txn_counter = txn_counter + 1;
    END WHILE;

    -- Transactions 61-68: 5 days ago
    SET txn_counter = 1;
    WHILE txn_counter <= 8 DO
        SET order_num = CONCAT('ORD-', DATE_FORMAT(CURDATE() - INTERVAL 5 DAY, '%Y%m%d'), '-', LPAD(txn_counter, 4, '0'));
        SET cust_id = FLOOR(1 + RAND() * 20);
        SET staff_id = IF(RAND() > 0.5, 2, 3);
        SET branch_id = IF(RAND() > 0.5, 1, 2);
        SET channel_id = FLOOR(1 + RAND() * 3);
        SET payment_id = FLOOR(1 + RAND() * 3);
        SET hour_val = FLOOR(9 + RAND() * 9);
        SET minute_val = FLOOR(RAND() * 60);
        SET txn_date = CONCAT(CURDATE() - INTERVAL 5 DAY, ' ', LPAD(hour_val, 2, '0'), ':', LPAD(minute_val, 2, '0'), ':00');
        SET total = ROUND(55 + RAND() * 180, 2);

        INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, total_amount, transaction_date)
        VALUES (order_num, cust_id, staff_id, branch_id, channel_id, payment_id, total, txn_date);

        SET @last_txn_id = LAST_INSERT_ID();

        SET @item_count = FLOOR(1 + RAND() * 3);
        SET @i = 0;
        WHILE @i < @item_count DO
            SET item_id = FLOOR(1 + RAND() * 9);
            SET qty = FLOOR(1 + RAND() * 3);
            SELECT base_price INTO unit_prc FROM items WHERE item_id = item_id LIMIT 1;
            SET subtotal = ROUND(unit_prc * qty, 2);
            INSERT INTO transaction_items (transaction_id, item_id, quantity, unit_price, subtotal)
            VALUES (@last_txn_id, item_id, qty, unit_prc, subtotal);
            SET @i = @i + 1;
        END WHILE;

        SET txn_counter = txn_counter + 1;
    END WHILE;

    -- Transactions 69-75: 6 days ago
    SET txn_counter = 1;
    WHILE txn_counter <= 7 DO
        SET order_num = CONCAT('ORD-', DATE_FORMAT(CURDATE() - INTERVAL 6 DAY, '%Y%m%d'), '-', LPAD(txn_counter, 4, '0'));
        SET cust_id = FLOOR(1 + RAND() * 20);
        SET staff_id = IF(RAND() > 0.5, 2, 3);
        SET branch_id = IF(RAND() > 0.5, 1, 2);
        SET channel_id = FLOOR(1 + RAND() * 3);
        SET payment_id = FLOOR(1 + RAND() * 3);
        SET hour_val = FLOOR(9 + RAND() * 9);
        SET minute_val = FLOOR(RAND() * 60);
        SET txn_date = CONCAT(CURDATE() - INTERVAL 6 DAY, ' ', LPAD(hour_val, 2, '0'), ':', LPAD(minute_val, 2, '0'), ':00');
        SET total = ROUND(50 + RAND() * 200, 2);

        INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, total_amount, transaction_date)
        VALUES (order_num, cust_id, staff_id, branch_id, channel_id, payment_id, total, txn_date);

        SET @last_txn_id = LAST_INSERT_ID();

        SET @item_count = FLOOR(1 + RAND() * 3);
        SET @i = 0;
        WHILE @i < @item_count DO
            SET item_id = FLOOR(1 + RAND() * 9);
            SET qty = FLOOR(1 + RAND() * 3);
            SELECT base_price INTO unit_prc FROM items WHERE item_id = item_id LIMIT 1;
            SET subtotal = ROUND(unit_prc * qty, 2);
            INSERT INTO transaction_items (transaction_id, item_id, quantity, unit_price, subtotal)
            VALUES (@last_txn_id, item_id, qty, unit_prc, subtotal);
            SET @i = @i + 1;
        END WHILE;

        SET txn_counter = txn_counter + 1;
    END WHILE;
END$$
DELIMITER ;

-- Run the procedure to generate data
CALL generate_seed_data();

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS generate_seed_data;

-- =====================================================
-- SUMMARY
-- =====================================================
SELECT 'Seed data generated successfully!' AS message;
SELECT COUNT(*) AS total_transactions FROM transactions;
SELECT COUNT(*) AS total_transaction_items FROM transaction_items;
SELECT COUNT(*) AS total_customers FROM customers;

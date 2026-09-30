-- Run once, after express_orders.sql.
ALTER TABLE express_orders
    ADD COLUMN order_subtotal   DECIMAL(8,2) NOT NULL DEFAULT 0.00 AFTER order_ref,
    ADD COLUMN delivery_method  ENUM('Curbside','Delivery') NOT NULL DEFAULT 'Curbside' AFTER order_subtotal,
    ADD COLUMN delivery_fee     DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER delivery_method,
    ADD COLUMN delivery_address VARCHAR(255) NULL AFTER delivery_fee;

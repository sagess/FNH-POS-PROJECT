-- Run after express_orders.sql and express_delivery.sql (and sales_delivery_fee.sql for checkout).

-- Phoned-in orders: phone number, a "Packed" step, and a link to the sale created at checkout.
ALTER TABLE express_orders
    MODIFY status ENUM('Received','Packed','Collected','Cancelled') NOT NULL DEFAULT 'Received',
    ADD COLUMN phone     VARCHAR(30) NULL AFTER customer_name,
    ADD COLUMN packed_by VARCHAR(50) NULL,
    ADD COLUMN packed_at DATETIME    NULL,
    ADD COLUMN sale_id   INT         NULL;

-- The grocery list for each order (prices are captured when the order is phoned in).
CREATE TABLE express_order_items (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    express_order_id INT           NOT NULL,
    product_id       INT           NOT NULL,
    quantity         INT           NOT NULL,
    unit_price       DECIMAL(8,2)  NOT NULL,
    line_total       DECIMAL(8,2)  NOT NULL,
    INDEX idx_order (express_order_id),
    FOREIGN KEY (express_order_id) REFERENCES express_orders(id) ON DELETE CASCADE
);

-- Optional: lets your existing sales receipts carry the Express home-delivery fee.
-- Your `sales` table has subtotal, tax and total but nowhere to record the $10.00 fee.
ALTER TABLE sales
    ADD COLUMN delivery_fee DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER tax;

-- From then on, total due = subtotal + tax + delivery_fee.

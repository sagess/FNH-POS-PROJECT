-- Run once. Kept separate from `orders` so the existing Orders pages are untouched.
CREATE TABLE express_orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    order_ref     VARCHAR(50)  NULL,
    status        ENUM('Received','Collected','Cancelled') NOT NULL DEFAULT 'Received',
    received_by   VARCHAR(50)  NOT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    collected_by  VARCHAR(50)  NULL,
    collected_at  DATETIME     NULL,
    INDEX idx_created (created_at),
    INDEX idx_status (status)
);

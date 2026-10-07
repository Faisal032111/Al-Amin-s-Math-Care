-- ============================================================
-- Phase D Migration: Add payment_date & fee_month to fee_records
-- Al Amin's Math Care | alaminmathcare.com
-- Run this once against the live database
-- ============================================================

USE alaminmathcare;

-- Add payment_date column (date the money was physically received)
ALTER TABLE `fee_records`
    ADD COLUMN `payment_date` DATE NOT NULL DEFAULT (CURDATE()) AFTER `student_id`,
    ADD COLUMN `fee_month`    VARCHAR(7) DEFAULT NULL             AFTER `payment_date`;

-- Back-fill existing rows: set payment_date from created_at timestamp
UPDATE `fee_records`
SET    `payment_date` = DATE(`created_at`),
       `fee_month`    = DATE_FORMAT(`created_at`, '%Y-%m')
WHERE  `payment_date` = CURDATE();   -- only un-set rows

-- Add index for fast monthly reporting
ALTER TABLE `fee_records`
    ADD INDEX `idx_fee_month`    (`fee_month`),
    ADD INDEX `idx_payment_date` (`payment_date`);

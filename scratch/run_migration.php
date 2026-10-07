<?php
/**
 * scratch/run_migration.php - Run Phase D database migration
 */
declare(strict_types=1);
require 'includes/db.php';
$pdo = db();

echo "=== Phase D Migration: fee_records table ===" . PHP_EOL;

// Check if columns already exist
$existing = $pdo->query('DESCRIBE fee_records')->fetchAll(PDO::FETCH_COLUMN);
$hasPaymentDate = in_array('payment_date', $existing);
$hasFeeMonth = in_array('fee_month', $existing);

if ($hasPaymentDate && $hasFeeMonth) {
    echo "✓ Columns already exist. Migration skipped." . PHP_EOL;
    exit(0);
}

try {
    if (!$hasPaymentDate) {
        $pdo->exec("ALTER TABLE `fee_records` ADD COLUMN `payment_date` DATE NOT NULL DEFAULT '2026-01-01' AFTER `student_id`");
        echo "✓ Added payment_date column" . PHP_EOL;
    }
    if (!$hasFeeMonth) {
        $pdo->exec("ALTER TABLE `fee_records` ADD COLUMN `fee_month` VARCHAR(7) DEFAULT NULL AFTER `payment_date`");
        echo "✓ Added fee_month column" . PHP_EOL;
    }

    // Back-fill from created_at
    $rows = $pdo->exec("UPDATE `fee_records` SET payment_date = DATE(created_at), fee_month = DATE_FORMAT(created_at, '%Y-%m') WHERE payment_date = '2026-01-01' OR fee_month IS NULL");
    echo "✓ Back-filled {$rows} existing rows from created_at" . PHP_EOL;

    // Add indexes (ignore if they already exist)
    try {
        $pdo->exec("ALTER TABLE `fee_records` ADD INDEX `idx_fee_month` (`fee_month`)");
        echo "✓ Added idx_fee_month index" . PHP_EOL;
    } catch (Throwable $e) { echo "  (idx_fee_month index may already exist)" . PHP_EOL; }

    try {
        $pdo->exec("ALTER TABLE `fee_records` ADD INDEX `idx_payment_date` (`payment_date`)");
        echo "✓ Added idx_payment_date index" . PHP_EOL;
    } catch (Throwable $e) { echo "  (idx_payment_date index may already exist)" . PHP_EOL; }

    echo PHP_EOL . "=== Migration COMPLETE ===" . PHP_EOL;

} catch (Throwable $e) {
    echo "✗ Migration FAILED: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

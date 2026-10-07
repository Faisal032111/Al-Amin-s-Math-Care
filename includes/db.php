<?php

/**
 * includes/db.php — Central PDO Database Connection
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Usage:
 *   $pdo = db();
 *   $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :id');
 *   $stmt->execute([':id' => $id]);
 *   $row = $stmt->fetch();
 */

declare(strict_types=1);

/**
 * Returns a singleton PDO instance.
 * Returns null only if the DB is unreachable (handled gracefully).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = require __DIR__ . '/../config/database.php';

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $cfg['host'],
        $cfg['port'],
        $cfg['dbname'],
        $cfg['charset']
    );

    try {
        $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
    } catch (PDOException $e) {
        // Log securely — never expose credentials or DB errors to the browser
        $logFile = __DIR__ . '/../storage/logs/db_errors.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        error_log(
            '[' . date('Y-m-d H:i:s') . '] DB Connection Error: ' . $e->getMessage() . PHP_EOL,
            3,
            $logFile
        );

        // Show a safe generic message (no stack trace/credentials exposed)
        if (defined('APP_DEBUG') && APP_DEBUG) {
            throw new RuntimeException('Database connection failed. Check config/database.php. ' . $e->getMessage());
        }

        throw new RuntimeException('A database error occurred. Please try again later.');
    }

    return $pdo;
}

/**
 * Saves an admission lead to the `leads` table.
 * Falls back to JSON file storage if DB is unavailable.
 */
function save_lead(array $data): bool
{
    try {
        $pdo  = db();
        $stmt = $pdo->prepare(
            'INSERT INTO leads
                (type, student_name, guardian_phone, whatsapp_number, class_level, course_id, ip_address, status)
             VALUES
                (:type, :student_name, :guardian_phone, :whatsapp_number, :class_level, :course_id, :ip_address, :status)'
        );
        return $stmt->execute([
            ':type'            => $data['type']            ?? 'admission_enquiry',
            ':student_name'    => $data['student_name'],
            ':guardian_phone'  => $data['guardian_phone'],
            ':whatsapp_number' => $data['whatsapp_number'] ?? $data['guardian_phone'],
            ':class_level'     => $data['class_level']     ?? null,
            ':course_id'       => $data['course_id']       ?? null,
            ':ip_address'      => $data['ip_address']      ?? ($_SERVER['REMOTE_ADDR'] ?? null),
            ':status'          => 'new',
        ]);
    } catch (Throwable $e) {
        // Fallback: store in JSON file so no lead is lost
        $dir = __DIR__ . '/../storage/leads';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file  = $dir . '/leads_fallback.json';
        $leads = [];
        if (is_file($file)) {
            $leads = json_decode((string) file_get_contents($file), true) ?: [];
        }
        $data['created_at'] = date('c');
        $leads[] = $data;
        file_put_contents($file, json_encode($leads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return false;
    }
}

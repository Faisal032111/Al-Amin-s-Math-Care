<?php

/**
 * admin/students/export.php — Export Redirector
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$type = $_GET['type'] ?? 'students';
$batchId = $_GET['batch_id'] ?? '';
$classLevel = $_GET['class_level'] ?? '';

$url = base_url("api/export-students.php?type={$type}");
if ($batchId) $url .= "&batch_id=" . urlencode((string)$batchId);
if ($classLevel) $url .= "&class_level=" . urlencode((string)$classLevel);

header("Location: {$url}");
exit;

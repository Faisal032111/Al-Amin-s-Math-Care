<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = db();
$cols = $pdo->query('DESCRIBE attendances')->fetchAll();
echo json_encode($cols, JSON_PRETTY_PRINT) . "\n";

<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = db();
$allStu = $pdo->query('SELECT id, student_id_code, name, guardian_phone FROM students')->fetchAll();
echo "Total students: " . count($allStu) . "\n";
echo json_encode($allStu, JSON_PRETTY_PRINT) . "\n";

$batches = $pdo->query('SELECT id, batch_name FROM batches')->fetchAll();
echo "Batches: " . json_encode($batches, JSON_PRETTY_PRINT) . "\n";

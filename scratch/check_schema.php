<?php
require 'includes/db.php';
require 'includes/helpers.php';
$pdo = db();

echo "=== fee_records columns ===" . PHP_EOL;
$cols = $pdo->query('DESCRIBE fee_records')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ')' . PHP_EOL;
}

echo PHP_EOL . "=== student_fees columns ===" . PHP_EOL;
$cols2 = $pdo->query('DESCRIBE student_fees')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols2 as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ')' . PHP_EOL;
}

echo PHP_EOL . "=== attendances columns ===" . PHP_EOL;
$cols3 = $pdo->query('DESCRIBE attendances')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols3 as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ')' . PHP_EOL;
}

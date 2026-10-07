<?php

/**
 * scratch/portal_http_smoke.php — Test Portal Login & Dashboard HTTP 200
 */

declare(strict_types=1);

$ch = curl_init('http://127.0.0.1:8000/portal/login.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "GET /portal/login.php -> HTTP " . $code . " (Length: " . strlen((string)$resp) . ")\n";

if ($code === 200) {
    echo "[PASS] Portal Login is accessible and returns 200 OK\n";
} else {
    echo "[FAIL] Unexpected status code\n";
}

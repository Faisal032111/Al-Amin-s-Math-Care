<?php

/**
 * router.php — Development Server Router for PHP Built-in Server
 * Al Amin's Math Care | Mirrors .htaccess rewrite rules locally
 */

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

// Serve static files and physical scripts directly
if ($uri !== '/' && (is_file($file) || is_dir($file))) {
    return false;
}

// Clean URL Rewrites (Mirroring .htaccess)
if (preg_match('#^/courses/([a-zA-Z0-9_-]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/course-details.php';
    exit;
}

if (preg_match('#^/batches/([0-9]+)/?$#', $uri, $m)) {
    $_GET['id'] = $m[1];
    require __DIR__ . '/batch-details.php';
    exit;
}

if (preg_match('#^/teachers/([a-zA-Z0-9_-]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/teacher-profile.php';
    exit;
}

if (preg_match('#^/notices/([0-9]+)/?$#', $uri, $m)) {
    $_GET['id'] = $m[1];
    require __DIR__ . '/notice-details.php';
    exit;
}

if (preg_match('#^/verify-id/([a-zA-Z0-9_-]+)/?$#', $uri, $m)) {
    $_GET['token'] = $m[1];
    require __DIR__ . '/verify-id.php';
    exit;
}

if ($uri === '/results' || $uri === '/results/') {
    require __DIR__ . '/results-lookup.php';
    exit;
}

if ($uri === '/admission' || $uri === '/admission/') {
    require __DIR__ . '/admission.php';
    exit;
}

if ($uri === '/demo' || $uri === '/demo/') {
    require __DIR__ . '/demo.php';
    exit;
}

if ($uri === '/routine' || $uri === '/routine/') {
    require __DIR__ . '/routine.php';
    exit;
}

if (is_file(__DIR__ . $uri . '.php')) {
    require __DIR__ . $uri . '.php';
    exit;
}

// Fallback to index.php
require __DIR__ . '/index.php';

<?php

declare(strict_types=1);

function app_config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/app.php';
    }
    return $config;
}

function current_locale(): string
{
    $config = app_config();
    $locale = $_GET['lang'] ?? $_COOKIE['site_lang'] ?? $_COOKIE['locale'] ?? $config['default_locale'];

    if (!in_array($locale, $config['supported_locales'], true)) {
        $locale = $config['default_locale'];
    }

    if (isset($_GET['lang']) && in_array($_GET['lang'], $config['supported_locales'], true)) {
        setcookie('site_lang', $locale, [
            'expires'  => time() + (86400 * 30),
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_SESSION['site_lang'] = $locale;
    }

    return $locale;
}

function load_translations(string $locale): array
{
    $file = __DIR__ . '/../lang/' . $locale . '.php';
    if (!is_file($file)) {
        $file = __DIR__ . '/../lang/en.php';
    }
    return require $file;
}

function init_i18n(): void
{
    global $locale, $translations, $config;
    $config = app_config();
    $locale = current_locale();
    $translations = load_translations($locale);
}

function __(string $key, array $replace = []): string
{
    global $translations, $locale;

    $text = $translations[$key] ?? $key;

    if ($text === $key && $locale !== 'en') {
        $en = load_translations('en');
        $text = $en[$key] ?? $key;
    }

    foreach ($replace as $search => $value) {
        $text = str_replace(':' . $search, (string) $value, $text);
    }

    return $text;
}

function lang_url(string $targetLocale): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    $path = $parts['path'] ?? '/';
    parse_str($parts['query'] ?? '', $query);
    $query['lang'] = $targetLocale;
    return $path . '?' . http_build_query($query);
}

function base_url(string $path = ''): string
{
    $base = rtrim(app_config()['base_url'] ?? '', '/');
    $path = ltrim($path, '/');
    return $base . ($path !== '' ? '/' . $path : '');
}

function get_current_lang(): string
{
    global $locale;
    return $locale ?? 'en';
}

function content(?array $row, string $field): string
{
    if (!$row) {
        return '';
    }

    $current = get_current_lang();
    $localizedField = "{$field}_{$current}";
    $fallbackField  = "{$field}_en";

    if (!empty($row[$localizedField])) {
        return (string) $row[$localizedField];
    }

    if (!empty($row[$fallbackField])) {
        return (string) $row[$fallbackField];
    }

    return (string) ($row[$field] ?? '');
}

init_i18n();

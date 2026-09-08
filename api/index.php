<?php

declare(strict_types=1);

/**
 * Vercel Serverless Entry Point for Laravel
 *
 * In Vercel serverless environments, the root filesystem is read-only except for /tmp.
 * This adapter prepares the ephemeral storage structure in /tmp/storage and delegates
 * the request to Laravel's standard public/index.php entry point.
 */

$storagePath = '/tmp/storage';
$requiredDirectories = [
    $storagePath . '/app',
    $storagePath . '/bootstrap/cache',
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
];

foreach ($requiredDirectories as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

// Redirect storage and compiled views to the writable /tmp directory
putenv('LARAVEL_STORAGE_PATH=' . $storagePath);
$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;

$viewPath = $storagePath . '/framework/views';
putenv('VIEW_COMPILED_PATH=' . $viewPath);
$_ENV['VIEW_COMPILED_PATH'] = $viewPath;
$_SERVER['VIEW_COMPILED_PATH'] = $viewPath;

// Redirect bootstrap/cache to writable /tmp directory to avoid read-only filesystem errors
$bootstrapCachePath = $storagePath . '/bootstrap/cache';
putenv('APP_PACKAGES_CACHE=' . $bootstrapCachePath . '/packages.php');
$_ENV['APP_PACKAGES_CACHE'] = $bootstrapCachePath . '/packages.php';
$_SERVER['APP_PACKAGES_CACHE'] = $bootstrapCachePath . '/packages.php';

putenv('APP_SERVICES_CACHE=' . $bootstrapCachePath . '/services.php');
$_ENV['APP_SERVICES_CACHE'] = $bootstrapCachePath . '/services.php';
$_SERVER['APP_SERVICES_CACHE'] = $bootstrapCachePath . '/services.php';

putenv('APP_CONFIG_CACHE=' . $bootstrapCachePath . '/config.php');
$_ENV['APP_CONFIG_CACHE'] = $bootstrapCachePath . '/config.php';
$_SERVER['APP_CONFIG_CACHE'] = $bootstrapCachePath . '/config.php';

putenv('APP_ROUTES_CACHE=' . $bootstrapCachePath . '/routes-v7.php');
$_ENV['APP_ROUTES_CACHE'] = $bootstrapCachePath . '/routes-v7.php';
$_SERVER['APP_ROUTES_CACHE'] = $bootstrapCachePath . '/routes-v7.php';

putenv('APP_EVENTS_CACHE=' . $bootstrapCachePath . '/events.php');
$_ENV['APP_EVENTS_CACHE'] = $bootstrapCachePath . '/events.php';
$_SERVER['APP_EVENTS_CACHE'] = $bootstrapCachePath . '/events.php';

// Ensure robust defaults if environment variables are unset or empty string
$sessionDriver = getenv('SESSION_DRIVER');
if (! $sessionDriver || $sessionDriver === '""') {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = 'cookie';
    $_SERVER['SESSION_DRIVER'] = 'cookie';
}

$cacheStore = getenv('CACHE_STORE');
if (! $cacheStore || $cacheStore === '""') {
    putenv('CACHE_STORE=database');
    $_ENV['CACHE_STORE'] = 'database';
    $_SERVER['CACHE_STORE'] = 'database';
}

$queueConnection = getenv('QUEUE_CONNECTION');
if (! $queueConnection || $queueConnection === '""') {
    putenv('QUEUE_CONNECTION=sync');
    $_ENV['QUEUE_CONNECTION'] = 'sync';
    $_SERVER['QUEUE_CONNECTION'] = 'sync';
}

try {
    // Forward execution to Laravel's public entrypoint
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    error_log((string) $e);
    http_response_code(500);

    echo '<h1>Server Error (500)</h1>';
    echo '<p style="color: #b91c1c; font-family: monospace; font-size: 16px;"><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>';
    echo '<p style="color: #4b5563; font-family: monospace; font-size: 14px;">In ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<pre style="background: #f3f4f6; padding: 16px; border-radius: 8px; overflow-x: auto; font-size: 12px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';

    $prev = $e->getPrevious();
    if ($prev) {
        echo '<h2 style="color: #b91c1c; margin-top: 24px;">Underlying Exception:</h2>';
        echo '<p style="color: #b91c1c; font-family: monospace; font-size: 16px;"><strong>' . htmlspecialchars($prev->getMessage()) . '</strong></p>';
        echo '<p style="color: #4b5563; font-family: monospace; font-size: 14px;">In ' . htmlspecialchars($prev->getFile()) . ':' . $prev->getLine() . '</p>';
        echo '<pre style="background: #f3f4f6; padding: 16px; border-radius: 8px; overflow-x: auto; font-size: 12px;">' . htmlspecialchars($prev->getTraceAsString()) . '</pre>';
    }
}

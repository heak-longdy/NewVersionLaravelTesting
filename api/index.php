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

// Normalize HTTPS behind Vercel edge reverse proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}

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

$maintDriver = getenv('APP_MAINTENANCE_DRIVER');
if (! $maintDriver || $maintDriver === '""') {
    putenv('APP_MAINTENANCE_DRIVER=file');
    $_ENV['APP_MAINTENANCE_DRIVER'] = 'file';
    $_SERVER['APP_MAINTENANCE_DRIVER'] = 'file';
}

putenv('DB_SSLMODE=require');
$_ENV['DB_SSLMODE'] = 'require';
$_SERVER['DB_SSLMODE'] = 'require';
putenv('PGSSLMODE=require');
$_ENV['PGSSLMODE'] = 'require';
$_SERVER['PGSSLMODE'] = 'require';
putenv('DB_URL=');
unset($_ENV['DB_URL'], $_SERVER['DB_URL'], $_ENV['DATABASE_URL'], $_SERVER['DATABASE_URL']);

// Check for missing APP_KEY
$appKey = getenv('APP_KEY');
if (! $appKey || $appKey === '""') {
    http_response_code(500);
    echo '<div style="font-family: system-ui, sans-serif; padding: 30px; max-width: 700px; margin: 40px auto; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">';
    echo '<h2 style="color: #9f1239; margin-top: 0;">Missing APP_KEY Environment Variable</h2>';
    echo '<p style="color: #4c0519; font-size: 15px;">Your Vercel deployment does not have an <code>APP_KEY</code> configured. Laravel cannot start without an encryption key.</p>';
    echo '<p style="color: #4c0519; font-size: 14px;">Go to <strong>Vercel Dashboard &rarr; Project Settings &rarr; Environment Variables</strong> and add:</p>';
    echo '<pre style="background: #ffffff; padding: 14px; border-radius: 6px; border: 1px solid #fda4af; font-size: 13px; color: #111827;">APP_KEY=base64:7/szogsa/WYTajwWVe44UbUEakCi89QwPXZJ73jduxo=</pre>';
    echo '</div>';
    exit;
}

// Force APP_DEBUG=true so Laravel displays the full error screen instead of a generic "500 Server Error"
putenv('APP_DEBUG=true');
$_ENV['APP_DEBUG'] = 'true';
$_SERVER['APP_DEBUG'] = 'true';

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

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

// Forward execution to Laravel's public entrypoint
require __DIR__ . '/../public/index.php';

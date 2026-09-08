<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

$app->booting(function () use ($app): void {
    $config = $app->make('config');

    if (empty($config->get('session.driver'))) {
        $config->set('session.driver', 'cookie');
    }

    if (empty($config->get('app.maintenance.driver'))) {
        $config->set('app.maintenance.driver', 'file');
    }

    if (empty($config->get('cache.default'))) {
        $config->set('cache.default', 'database');
    }

    if (empty($config->get('hashing.driver'))) {
        $config->set('hashing.driver', 'bcrypt');
    }

    $rounds = (int) $config->get('hashing.bcrypt.rounds', 10);
    if ($rounds < 4 || $rounds > 31) {
        $rounds = 10;
    }
    $config->set('hashing.bcrypt.rounds', $rounds);

    if (empty($config->get('database.default'))) {
        $config->set('database.default', 'pgsql');
    }

    $config->set('database.connections.pgsql.sslmode', 'require');
    $config->set('database.connections.pgsql.url', null);

    $sessionDomain = $config->get('session.domain');
    if (in_array($sessionDomain, [null, '', 'null', 'none'], true)) {
        $config->set('session.domain', null);
    }
    $config->set('session.secure', true);

    if (empty($config->get('mail.default')) || in_array($config->get('mail.mailers.smtp.host'), ['127.0.0.1', 'localhost'], true)) {
        $config->set('mail.default', 'log');
    }

    if ($app->environment('production') || ! empty($_ENV['VERCEL'])) {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
});

return $app;

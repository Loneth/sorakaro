<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // ✅ AUTH MIDDLEWARE (PAKAI ILLUMINATE)
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            // ✅ SPATIE
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // ✅ CUSTOM
            'level.unlocked'  => \App\Http\Middleware\EnsureLevelUnlocked::class,
            'learning.step'   => \App\Http\Middleware\EnsureLearningStep::class,
        ]);
    })

    // ->withMiddleware(function (Middleware $middleware): void {
    //     $middleware->alias([
    //         'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    //         'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    //         'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    //         'level.unlocked' => \App\Http\Middleware\EnsureLevelUnlocked::class,
    //     ]);
    // })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

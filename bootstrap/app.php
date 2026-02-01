<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )

    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('queue:work --stop-when-empty')->everyTwoMinutes(); 
    })

    ->withMiddleware(function (Middleware $middleware) {

        $middleware->statefulApi();
        $middleware->trustProxies(at: '*'); 
        $middleware->validateCsrfTokens(except: [
            'broadcasting/auth',
            'api/broadcasting/auth',
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (AccessDeniedHttpException $e, $request) {
            return response()->json([
                'message' => "Evento não autorizado, favor contatar administração.",
            ], 403);
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'message' => 'Rota não encontrada, favor, validar a requisição.',
            ], 404);
        });

    })
    ->create();

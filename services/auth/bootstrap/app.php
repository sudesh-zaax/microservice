<?php

use App\Http\Middleware\PreventXss;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        // commands: __DIR__.'/../routes/console.php',
        // health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(PreventXss::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response) {
            $statusCode = $response->getStatusCode();
            $messageString= $response->getContent() ?? 'An error occurred. Please try again later.';
            // dd($messageString);

            $messages = [
                404 => 'Page not found.',
                401 => 'Invalid or missing API key.',
            ];
            $message = $messages[$statusCode] ?? $messageString;
            return response()->json(['message' => $message], $statusCode);
        });
    })->create();

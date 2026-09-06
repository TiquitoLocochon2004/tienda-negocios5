<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Interceptamos el fallo genérico de autenticación de Laravel
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                try {
                    // Si el usuario envió una cabecera Authorization, evaluamos qué le pasa al token
                    if ($request->bearerToken() || $request->header('Authorization')) {
                        JWTAuth::parseToken()->authenticate();
                    }
                } catch (TokenExpiredException $ex) {
                    return response()->json([
                        'message' => 'El token ha expirado. Por favor, inicia sesión nuevamente.',
                        'status' => 401,
                        'error' => 'Token Expired'
                    ], 401);
                } catch (TokenInvalidException $ex) {
                    return response()->json([
                        'message' => 'El token proporcionado es inválido o está mal formado.',
                        'status' => 401,
                        'error' => 'Token Invalid'
                    ], 401);
                } catch (\Exception $ex) {
                    return response()->json([
                        'message' => 'El token proporcionado es inválido o está mal formado.',
                        'status' => 401,
                        'error' => 'Token Invalid'
                    ], 401);
                }

                // Si no envió ningún token en absoluto
                return response()->json([
                    'message' => 'Token no proporcionado. Se requiere autenticación.',
                    'status' => 401,
                    'error' => 'Token Not Provided'
                ], 401);
            }
        });

    })->create();
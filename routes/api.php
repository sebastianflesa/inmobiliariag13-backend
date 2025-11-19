<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProyectoController;
use App\Http\Controllers\Api\UnidadController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas con JWT
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('proyectos', ProyectoController::class);
    Route::apiResource('unidades', UnidadController::class);
    Route::apiResource('clientes', ClienteController::class);
    Route::get('/clientes/buscar-por-rut/{rut}', 'ClienteController@buscarPorRut');
});

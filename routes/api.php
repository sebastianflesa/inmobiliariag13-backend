<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProyectoController;
use App\Http\Controllers\Api\UnidadController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContratoController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\CalificacionController;

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación Pública
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
/*
|--------------------------------------------------------------------------
| Rutas protegidas con token de AWS Cognito
|--------------------------------------------------------------------------
*/

Route::middleware('cognito')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Autenticación
    |--------------------------------------------------------------------------
    */
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    /*
    |--------------------------------------------------------------------------
    | CRUD Base del Sistema
    |--------------------------------------------------------------------------
    */
    Route::apiResource('proyectos', ProyectoController::class);
    Route::apiResource('unidades', UnidadController::class);
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('contratos', ContratoController::class);
    Route::apiResource('pagos', PagoController::class);
    Route::apiResource('calificaciones', CalificacionController::class);
    /*
    |--------------------------------------------------------------------------
    | Alias opcionales (no rompen nada)
    |--------------------------------------------------------------------------
    */
    Route::get('/contratos', [ContratoController::class, 'index']);
    Route::get('/contratos/{id}', [ContratoController::class, 'show']);
    Route::put('/contratos/{id}', [ContratoController::class, 'update']);
});

// 0. Buscar contrato por RUT (público, para Pago Cliente)
Route::get(
    '/contratos/buscar-rut/{rut}',
    [\App\Http\Controllers\Api\ConsultaContratoController::class, 'buscarPorRut']
);
// 1. Iniciar pago (Crea pago en estado pendiente)
Route::post('/pagos/{id}/init', [PagoController::class, 'iniciarPago']);
// 2. Validar PIN
Route::post('/pagos/{id}/pin', [PagoController::class, 'validarPin']);
// 3. Generar OTP
Route::post('/pagos/{id}/otp', [PagoController::class, 'generarOtp']);
// 4. Validar OTP y autorizar pago
Route::post('/pagos/{id}/otp/validar', [PagoController::class, 'validarOtp']);

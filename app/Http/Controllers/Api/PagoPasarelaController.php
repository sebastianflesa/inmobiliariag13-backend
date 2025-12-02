<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Contrato;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class PagoPasarelaController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Solicitar OTP (genera y cachea)
     * POST /api/pagos/solicitar-otp
     * body: { contrato_id }
     */
    public function solicitarOtp(Request $request)
    {
        $v = Validator::make($request->all(), [
            'contrato_id' => 'required|exists:contratos,id'
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $contratoId = (int) $request->contrato_id;

        $data = $this->otpService->generateForContract($contratoId, 20);

        return response()->json([
            'message' => 'OTP generado.',
            'otp' => $data['otp_formatted'],
            'expires_in' => $data['expires_in']
        ]);
    }

    /**
     * Simular pago: valida prefix, pin y otp; guarda pago y retorna resultado.
     * POST /api/pagos/simular
     * body: { contrato_id, card_prefix, pin, otp, monto }
     */
    public function simularPago(Request $request)
    {
        $v = Validator::make($request->all(), [
            'contrato_id' => 'required|exists:contratos,id',
            'card_prefix' => 'required|string|in:5331,4595',
            'pin' => 'required|string',
            'otp' => 'required|string',
            'monto' => 'required|numeric|min:1'
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $contratoId = (int) $request->contrato_id;
        $cardPrefix = $request->card_prefix;
        $pin = $request->pin;
        $otp = $request->otp;
        $monto = $request->monto;

        // 1) Validar PIN (requeriste PIN fijo = "1234")
        if ($pin !== '1234') {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN incorrecto'
            ], 422);
        }

        // 2) Validar OTP con OtpService (cache)
        if (!$this->otpService->validate($contratoId, $otp)) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP inválido o expirado'
            ], 422);
        }

        // 3) Crear registro de pago (pendiente->aprobado simulado)
        $pago = Pago::create([
            'contrato_id' => $contratoId,
            'monto' => $monto,
            'fecha_pago' => now(),
            'metodo_pago' => 'tarjeta_simulada',
            'estado' => 'aprobado',
            'card_first_digits' => $cardPrefix,
            'transaction_id' => Str::upper(Str::random(12)),
            'auth_code' => $otp,
            'gateway_status' => 'aprobado',
            'pin_hash' => Hash::make($pin),
            'detalles' => [
                'note' => 'Pago simulado desde pasarela interna',
                'env' => app()->environment()
            ],
            'validado_en' => now(),
        ]);

        // limpiar OTP cache
        $this->otpService->clear($contratoId);

        // Opcional: actualizar el contrato (ej. cambiar estado unidad) — si quieres lo hacemos aquí.

        return response()->json([
            'status' => 'success',
            'message' => 'Pago aprobado.',
            'pago' => $pago
        ]);
    }
}

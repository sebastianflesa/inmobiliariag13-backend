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

class PagoController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /* ======================================================
     * PASARELA: 1) Iniciar pago (crea un pago en estado pendiente)
     * Ruta recomendada: POST /api/pagos/{id}/init
     * Parámetros body (opcionales): monto
     * ======================================================*/
    public function iniciarPago(Request $request, int $id)
    {
        $contrato = Contrato::findOrFail($id);

        // Puedes usar un campo en contrato con el monto (si existe) o el body
        $monto = $request->input('monto', $request->input('amount', null));
        if ($monto === null) {
            // intenta tomar un campo sugerido del contrato si existe (no obligatorio)
            $monto = $contrato->monto ?? $contrato->monto_total ?? 0;
        }

        $pago = Pago::create([
            'contrato_id' => $contrato->id,
            'monto' => $monto,
            'fecha_pago' => now(),
            'metodo_pago' => 'tarjeta',
            'estado' => 'pendiente',
            // valores seguros para compatibilidad: payload / detalles
            'payload' => ['step' => 'init'],
            'detalles' => ['step' => 'init'],
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Pago iniciado',
            'pago' => $pago
        ], 201);
    }

    /* ======================================================
     * PASARELA: 2) Validar PIN (primer paso: PIN fijo '1234')
     * Ruta recomendada: POST /api/pagos/{id}/pin
     * body: { pin }
     * ======================================================*/
    public function validarPin(Request $request, int $id)
    {
        $v = Validator::make($request->all(), [
            'pin' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $pago = Pago::findOrFail($id);

        // PIN fijo por especificación; cámbialo si quieres.
        if ($request->pin !== '1234') {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN incorrecto'
            ], 422);
        }

        // Guardamos hash y marcamos validado (guardamos en ambos nombres por compatibilidad)
        $pago->update([
            'pin_validado' => true,
            'pin_validado_en' => now(), // <<<<<<<<<<<<<< AQUI ESTA EL FIX REAL
            'pin_hash' => Hash::make($request->pin),
            'payload' => array_merge($pago->payload ?? [], ['pin_validado_en' => now()->toDateTimeString()]),
            'detalles' => array_merge($pago->detalles ?? [], ['pin_validado_en' => now()->toDateTimeString()]),
        ]);


        return response()->json([
            'status' => 'ok',
            'message' => 'PIN validado correctamente.',
            'pago' => $pago->fresh()
        ]);
    }

    /* ======================================================
     * PASARELA: 3) Generar OTP (requiere que PIN esté validado)
     * Ruta recomendada: POST /api/pagos/{id}/otp
     * body: none
     * ======================================================*/
    public function generarOtp(Request $request, int $id)
    {
        $pago = Pago::findOrFail($id);

        if (!($pago->pin_validado ?? $pago->pin_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'El PIN debe validarse primero.'
            ], 422);
        }

        // Generar OTP con el servicio (TTL 20s)
        $data = $this->otpService->generateForContract((int) $pago->contrato_id, 20);

        // Guardar OTP raw en pago (campo otp_codigo / auth_code) y expiración
        $pago->update([
            'otp_codigo' => $data['otp_raw'] ?? preg_replace('/\s+/', '', $data['otp_formatted']),
            'auth_code' => $data['otp_raw'] ?? preg_replace('/\s+/', '', $data['otp_formatted']),
            'otp_expira_en' => now()->addSeconds($data['expires_in']),
            'payload' => array_merge($pago->payload ?? [], ['otp_generado_en' => now()->toDateTimeString()]),
            'detalles' => array_merge($pago->detalles ?? [], ['otp_generado_en' => now()->toDateTimeString()]),
        ]);

        return response()->json([
            'status' => 'ok',
            'otp' => $data['otp_formatted'],
            'expires_in' => $data['expires_in']
        ]);
    }

    /* ======================================================
     * PASARELA: 4) Validar OTP y finalizar (autorización)
     * Ruta recomendada: POST /api/pagos/{id}/otp/validar
     * body: { otp }
     * ======================================================*/
    public function validarOtp(Request $request, int $id)
    {
        $v = Validator::make($request->all(), [
            'otp' => 'required|string'
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $pago = Pago::findOrFail($id);

        // Si en el pago hay expiración, verificamos
        if (!empty($pago->otp_expira_en) && now()->greaterThan($pago->otp_expira_en)) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP expirado'
            ], 422);
        }

        // Comprobamos con OtpService (usa cache por contrato)
        $valid = $this->otpService->validate((int) $pago->contrato_id, $request->otp);

        // Si OtpService falla, intentamos comparar con lo guardado en el pago (compatibilidad)
        if (!$valid) {
            $stored = $pago->otp_codigo ?? $pago->auth_code ?? null;
            $cleanRequestOtp = preg_replace('/\s+/', '', $request->otp);
            if ($stored && ((string) $stored) === $cleanRequestOtp) {
                $valid = true;
            }
        }

        if (!$valid) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP inválido o expirado'
            ], 422);
        }

        // Autorizar el pago
        $pago->update([
            'estado' => 'autorizado',
            'gateway_status' => 'aprobado',
            'transaction_id' => strtoupper(Str::random(12)),
            'validado_en' => now(),
            'payload' => array_merge($pago->payload ?? [], ['otp_validado_en' => now()->toDateTimeString()]),
            'detalles' => array_merge($pago->detalles ?? [], ['otp_validado_en' => now()->toDateTimeString()]),
        ]);

        // Limpiar cache
        $this->otpService->clear((int) $pago->contrato_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Pago autorizado',
            'pago' => $pago->fresh()
        ]);
    }

    /* ======================================================
     * CRUD existente (no tocar) — se mantienen las funciones
     * ======================================================*/

    /**
     * Listar todos los pagos
     */
    public function index()
    {
        $pagos = Pago::with('contrato')->get();
        return response()->json($pagos, 200);
    }

    /**
     * Registrar un nuevo pago (mantuvimos validaciones originales)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contrato_id' => 'required|exists:contratos,id',
            'monto' => 'required|integer|min:0',
            'metodo_pago' => 'required|string|in:efectivo,transferencia,tarjeta',
            'estado' => 'sometimes|string|in:pendiente,completado,fallido,autorizado',
            'fecha_pago' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pago = Pago::create($validator->validated());

        return response()->json([
            'message' => 'Pago registrado correctamente.',
            'pago' => $pago
        ], 201);
    }

    /**
     * Mostrar un pago en detalle
     */
    public function show($id)
    {
        $pago = Pago::with('contrato')->find($id);

        if (!$pago) {
            return response()->json(['error' => 'Pago no encontrado'], 404);
        }

        return response()->json($pago, 200);
    }

    /**
     * Actualizar un pago
     */
    public function update(Request $request, $id)
    {
        $pago = Pago::find($id);

        if (!$pago) {
            return response()->json(['error' => 'Pago no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'monto' => 'sometimes|integer|min:0',
            'metodo_pago' => 'sometimes|string|in:efectivo,transferencia,tarjeta',
            'estado' => 'sometimes|string|in:pendiente,completado,fallido,autorizado,rechazado',
            'fecha_pago' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pago->update($validator->validated());

        return response()->json([
            'message' => 'Pago actualizado correctamente.',
            'pago' => $pago->fresh()->load('contrato')
        ], 200);
    }

    /**
     * Eliminar un pago
     */
    public function destroy($id)
    {
        $pago = Pago::find($id);

        if (!$pago) {
            return response()->json(['error' => 'Pago no encontrado'], 404);
        }

        $pago->delete();

        return response()->json([
            'message' => 'Pago eliminado correctamente.'
        ], 200);
    }
}

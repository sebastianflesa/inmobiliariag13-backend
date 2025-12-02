<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ConsultaContratoController extends Controller
{
    /**
     * Buscar contratos por RUT real
     * GET /api/contratos/buscar-rut/{rut}
     */
    public function buscarPorRut($rut)
    {
        // Normalizar RUT
        $rut = strtoupper(str_replace('.', '', $rut));

        // Buscar cliente
        $cliente = Cliente::where('rut', $rut)->first();
        if (!$cliente) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No existe un cliente con este RUT.'
            ], 404);
        }

        // Obtener TODOS los contratos del cliente con relaciones
        $contratos = $cliente->contratos()
            ->with(['unidad.proyecto', 'pagos', 'calificaciones'])
            ->get();

        if ($contratos->isEmpty()) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'El cliente existe, pero no tiene contratos asociados.'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'cliente' => $cliente,
            'contratos' => $contratos
        ], 200);
    }

}

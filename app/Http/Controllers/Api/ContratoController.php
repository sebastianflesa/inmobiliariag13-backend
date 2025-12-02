<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Unidad;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContratoController extends Controller
{
    /**
     * Listar todos los contratos
     */
    public function index()
    {
        $contratos = Contrato::with(['cliente', 'unidad', 'pagos', 'calificaciones'])->get();

        return response()->json($contratos, 200);
    }

    /**
     * Crear un nuevo contrato
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id'   => 'required|uuid|exists:clientes,id',
            'unidad_id'    => 'required|uuid|exists:unidades,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            'monto_total'  => 'required|numeric|min:0',
            'tipo_contrato'=> 'required|string|in:arriendo,venta',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Validar que la unidad no tenga contrato activo
        $unidad = Unidad::find($request->unidad_id);
        if ($unidad->estado !== 'Disponible') {
            return response()->json([
                'error' => 'La unidad seleccionada no está disponible.'
            ], 400);
        }

        // Crear contrato
        $contrato = Contrato::create([
            'cliente_id'   => $request->cliente_id,
            'unidad_id'    => $request->unidad_id,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin'    => $request->fecha_fin,
            'monto_total'  => $request->monto_total,
            'estado'       => 'activo',
            'tipo_contrato'=> $request->tipo_contrato,
        ]);

        // Actualizar unidad a “Reservado”
        $unidad->update([
            'estado' => 'Reservado',
        ]);

        return response()->json([
            'message' => 'Contrato creado exitosamente.',
            'contrato' => $contrato,
        ], 201);
    }

    /**
     * Mostrar un contrato en detalle
     */
    public function show($id)
    {
        $contrato = Contrato::with(['cliente', 'unidad', 'pagos', 'calificaciones'])
                            ->find($id);

        if (!$contrato) {
            return response()->json(['error' => 'Contrato no encontrado'], 404);
        }

        return response()->json($contrato, 200);
    }

    /**
     * Actualizar un contrato
     */
    public function update(Request $request, $id)
    {
        $contrato = Contrato::find($id);

        if (!$contrato) {
            return response()->json(['error' => 'Contrato no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin'    => 'sometimes|date|after_or_equal:fecha_inicio',
            'monto_total'  => 'sometimes|numeric|min:0',
            'estado'       => 'sometimes|string|in:activo,finalizado,cancelado',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $contrato->update($request->all());

        return response()->json([
            'message' => 'Contrato actualizado correctamente',
            'contrato' => $contrato
        ], 200);
    }

    /**
     * Finalizar o cancelar un contrato
     */
    public function destroy($id)
    {
        $contrato = Contrato::find($id);

        if (!$contrato) {
            return response()->json(['error' => 'Contrato no encontrado'], 404);
        }

        // Cambiar estado de unidad a Disponible
        $unidad = $contrato->unidad;
        if ($unidad) {
            $unidad->update(['estado' => 'Disponible']);
        }

        $contrato->delete();

        return response()->json([
            'message' => 'Contrato eliminado correctamente'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CalificacionController extends Controller
{
    /**
     * Listar todas las calificaciones
     */
    public function index()
    {
        $calificaciones = Calificacion::all();

        return response()->json($calificaciones, 200);
    }

    /**
     * Registrar una calificación
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contrato_id' => 'required|exists:contratos,id',
            'cliente_id' => 'required|exists:clientes,id',
            'unidad_id' => 'nullable|exists:unidades,id',
            'proyecto_id' => 'nullable|exists:proyectos,id',
            'puntaje' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $calificacion = Calificacion::create($validator->validated());

        return response()->json([
            'message' => 'Calificación registrada correctamente.',
            'calificacion' => $calificacion
        ], 201);
    }

    /**
     * Mostrar una calificación por ID
     */
    public function show($id)
    {
        $calificacion = Calificacion::find($id);

        if (!$calificacion) {
            return response()->json(['error' => 'Calificación no encontrada'], 404);
        }

        return response()->json($calificacion, 200);
    }

    /**
     * Actualizar una calificación
     */
    public function update(Request $request, $id)
    {
        $calificacion = Calificacion::find($id);

        if (!$calificacion) {
            return response()->json(['error' => 'Calificación no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'contrato_id' => 'sometimes|exists:contratos,id',
            'cliente_id' => 'sometimes|exists:clientes,id',
            'unidad_id' => 'nullable|exists:unidades,id',
            'proyecto_id' => 'nullable|exists:proyectos,id',
            'puntaje' => 'sometimes|integer|min:1|max:5',
            'comentario' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $calificacion->update($validator->validated());

        return response()->json([
            'message' => 'Calificación actualizada correctamente.',
            'calificacion' => $calificacion
        ], 200);
    }

    /**
     * Eliminar una calificación
     */
    public function destroy($id)
    {
        $calificacion = Calificacion::find($id);

        if (!$calificacion) {
            return response()->json(['error' => 'Calificación no encontrada'], 404);
        }

        $calificacion->delete();

        return response()->json([
            'message' => 'Calificación eliminada correctamente.'
        ], 200);
    }
}

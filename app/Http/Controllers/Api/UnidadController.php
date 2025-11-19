<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unidad;


class UnidadController extends Controller
{
    /**
     * @OA\Get(
     *     path="/unidades",
     *     summary="Listar unidades",
     *     @OA\Response(response="200", description="Lista de unidades")
     * )
     */
    public function index()
    {
        return Unidad::with(['proyecto', 'cliente'])->paginate(10);
    }

    /**
     * @OA\Post(
     *     path="/unidades",
     *     summary="Crear unidad",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="numero_unidad",
     *                     type="string",
     *                     description="Número de la unidad"
     *                 ),
     *                 @OA\Property(
     *                     property="tipo_unidad",
     *                     type="string",
     *                     description="Tipo de la unidad"
     *                 ),
     *                 @OA\Property(
     *                     property="metraje",
     *                     type="number",
     *                     description="Metraje de la unidad"
     *                 ),
     *                 @OA\Property(
     *                     property="precio_venta",
     *                     type="number",
     *                     description="Precio de venta de la unidad"
     *                 ),
     *                 @OA\Property(
     *                     property="estado",
     *                     type="string",
     *                     description="Estado de la unidad"
     *                 ),
     *                 @OA\Property(
     *                     property="proyecto_id",
     *                     type="string",
     *                     format="uuid",
     *                     description="ID del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="cliente_id",
     *                     type="string",
     *                     format="uuid",
     *                     description="ID del cliente"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Unidad creada")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'numero_unidad' => 'required|string',
            'tipo_unidad' => 'required|string',
            'metraje' => 'required|numeric',
            'precio_venta' => 'required|numeric',
            'estado' => 'required|string',
            'proyecto_id' => 'required|uuid|exists:proyectos,id',
            'cliente_id' => 'nullable|uuid|exists:clientes,id'
        ]);

        $unidad = Unidad::create($data);
        return response()->json($unidad, 201);
    }

    /**
     * @OA\Get(
     *     path="/unidades/{id}",
     *     summary="Mostrar unidad",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response="200", description="Unidad encontrada")
     * )
     */
    public function show($id)
    {
        return Unidad::with(['proyecto', 'cliente'])->findOrFail($id);
    }

    /**
     * @OA\Put(
     *     path="/unidades/{id}",
     *     summary="Actualizar unidad",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="numero_unidad",
     *                     type="string",
     *                     description="Número de la unidad"
     *                 ),
     *                 @OA\Property(
     *                     property="tipo_unidad",
     *                     type="string",
     *                     description="Tipo de la unidad"
     *                 ),
     *                 @OA\Property(
     *                     property="metraje",
     *                     type="number",
     *                     description="Metraje de la unidad"
     *                 ),
     *                 @OA\Property(
     *                     property="precio_venta",
     *                     type="number",
     *                     description="Precio de venta de la unidad"
     *                 ),
     *                 @OA\Property(
     *                     property="estado",
     *                     type="string",
     *                     description="Estado de la unidad"
     *                 ),
     *                 @OA\Property(
     *                     property="proyecto_id",
     *                     type="string",
     *                     format="uuid",
     *                     description="ID del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="cliente_id",
     *                     type="string",
     *                     format="uuid",
     *                     description="ID del cliente"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Unidad actualizada")
     * )
     */
    public function update(Request $request, $id)
    {
        $unidad = Unidad::findOrFail($id);

        $data = $request->validate([
            'numero_unidad' => 'string',
            'tipo_unidad' => 'string',
            'metraje' => 'numeric',
            'precio_venta' => 'numeric',
            'estado' => 'string',
            'proyecto_id' => 'uuid|exists:proyectos,id',
            'cliente_id' => 'nullable|uuid|exists:clientes,id'
        ]);

        $unidad->update($data);
        return response()->json($unidad);
    }

    /**
     * @OA\Delete(
     *     path="/unidades/{id}",
     *     summary="Eliminar unidad",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response="204", description="Unidad eliminada")
     * )
     */
    public function destroy($id)
    {
        Unidad::destroy($id);
        return response()->json(null, 204);
    }
}

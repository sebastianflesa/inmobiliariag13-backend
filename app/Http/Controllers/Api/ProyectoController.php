<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proyecto;
use App\Models\Cliente;

/**
 * @OA\Info(title="API", version="1.0")
 */
class ProyectoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/proyectos",
     *     summary="Listar proyectos",
     *     @OA\Response(response="200", description="Lista de proyectos")
     * )
     */
    public function index()
    {
        return Proyecto::with('unidades')->paginate(10);
    }

    /**
     * @OA\Post(
     *     path="/proyectos",
     *     summary="Crear proyecto",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="nombre",
     *                     type="string",
     *                     description="Nombre del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="descripcion",
     *                     type="string",
     *                     description="Descripción del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="ubicacion",
     *                     type="string",
     *                     description="Ubicación del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="fecha_inicio",
     *                     type="string",
     *                     format="date",
     *                     description="Fecha de inicio del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="fecha_fin",
     *                     type="string",
     *                     format="date",
     *                     description="Fecha de fin del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="estado",
     *                     type="string",
     *                     description="Estado del proyecto"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Proyecto creado")
     * )
     */

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'ubicacion' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'estado' => 'required|string'
        ]);

        $proyecto = Proyecto::create($data);

        return response()->json($proyecto, 201);
    }

    /**
     * @OA\Get(
     *     path="/proyectos/{id}",
     *     summary="Mostrar proyecto",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Proyecto encontrado")
     * )
     */

    public function show($id)
    {
        return Proyecto::with('unidades')->findOrFail($id);
    }

    /**
     * @OA\Put(
     *     path="/proyectos/{id}",
     *     summary="Actualizar proyecto",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="nombre",
     *                     type="string",
     *                     description="Nombre del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="descripcion",
     *                     type="string",
     *                     description="Descripción del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="ubicacion",
     *                     type="string",
     *                     description="Ubicación del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="fecha_inicio",
     *                     type="string",
     *                     format="date",
     *                     description="Fecha de inicio del proyecto"
     *                 *                 ),
     *                 @OA\Property(
     *                     property="fecha_fin",
     *                     type="string",
     *                     format="date",
     *                     description="Fecha de fin del proyecto"
     *                 ),
     *                 @OA\Property(
     *                     property="estado",
     *                     type="string",
     *                     description="Estado del proyecto"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Proyecto actualizado")
     * )
     */
    public function update(Request $request, $id)
    {
        $proyecto = Proyecto::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'string',
            'descripcion' => 'string',
            'ubicacion' => 'string',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'estado' => 'string'
        ]);

        $proyecto->update($data);

        return response()->json($proyecto);
    }

    /**
     * @OA\Delete(
     *     path="/proyectos/{id}",
     *     summary="Eliminar proyecto",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Proyecto eliminado")
     * )
     */
    public function destroy($id)
    {
        Proyecto::destroy($id);
        return response()->json(null, 204);
    }

    public function buscarPorRut($rut)
    {
        $cliente = Cliente::where('rut', $rut)->first();
        return response()->json($cliente);
    }
}

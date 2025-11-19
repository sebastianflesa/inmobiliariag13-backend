<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/clientes",
     *     summary="Listar clientes",
     *     @OA\Response(response="200", description="Lista de clientes")
     * )
     */
    public function index()
    {
        return Cliente::with('unidades')->paginate(10);
    }

    /**
     * @OA\Post(
     *     path="/clientes",
     *     summary="Crear cliente",
     *     @OA\RequestBody(
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             @OA\Property(
     *                 property="rut",
     *                 type="string",
     *                 description="RUT del cliente"
     *             ),
     *             @OA\Property(
     *                 property="nombre",
     *                 type="string",
     *                 description="Nombre del cliente"
     *             ),
     *             @OA\Property(
     *                 property="apellido",
     *                 type="string",
     *                 description="Apellido del cliente"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 description="Correo electrónico del cliente"
     *             ),
     *             @OA\Property(
     *                 property="telefono",
     *                 type="string",
     *                 description="Teléfono del cliente"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Cliente creado"),
     *     @OA\Response(response="500", description="Error interno del servidor")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'rut' => 'required|string|unique:clientes,rut||regex:/^[0-9]{7,8}-[0-9kK]{1}$/',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:clientes,email',
            'telefono' => 'required|string|max:20'
        ]);

        try {
            $cliente = Cliente::create($data);
            return response()->json($cliente, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/clientes/{id}",
     *     summary="Mostrar cliente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response="200", description="Cliente encontrado")
     * )
     */
    public function show($id)
    {
        return Cliente::with('unidades')->findOrFail($id);
    }

    /**
     * @OA\Put(
     *     path="/clientes/{id}",
     *     summary="Actualizar cliente",
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
     *                     property="rut",
     *                     type="string",
     *                     description="RUT del cliente"
     *                 ),
     *                 @OA\Property(
     *                     property="nombre",
     *                     type="string",
     *                     description="Nombre del cliente"
     *                 ),
     *                 @OA\Property(
     *                     property="apellido",
     *                     type="string",
     *                     description="Apellido del cliente"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="Correo electrónico del cliente"
     *                 ),
     *                 @OA\Property(
     *                     property="telefono",
     *                     type="string",
     *                     description="Teléfono del cliente"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Cliente actualizado")
     * )
     */
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $data = $request->validate([
            'rut' => 'string|unique:clientes,rut,' . $id,
            'nombre' => 'string|max:255',
            'apellido' => 'string|max:255',
            'email' => 'email|unique:clientes,email,' . $id,
            'telefono' => 'string'
        ]);

        $cliente->update($data);
        return response()->json($cliente);
    }

    /**
     * @OA\Delete(
     *     path="/clientes/{id}",
     *     summary="Eliminar cliente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response="204", description="Cliente eliminado")
     * )
     */
    public function destroy($id)
    {
        Cliente::destroy($id);
        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Registrar usuario",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="Nombre del usuario"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="Correo electrónico del usuario"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="Contraseña del usuario"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Usuario registrado")
     * )
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        try {
            $data['password'] = bcrypt($data['password']);
            $user = User::create($data);
            $token = JWTAuth::fromUser($user);

            return response()->json(['user' => $user, 'token' => $token], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Iniciar sesión",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="Correo electrónico del usuario"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="Contraseña del usuario"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Sesión iniciada")
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json(['token' => $token]);
    }

    /**
     * @OA\Get(
     *     path="/me",
     *     summary="Obtener información del usuario autenticado",
     *     @OA\Response(response="200", description="Información del usuario")
     * )
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Cerrar sesión",
     *     @OA\Response(response="200", description="Sesión cerrada")
     * )
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}

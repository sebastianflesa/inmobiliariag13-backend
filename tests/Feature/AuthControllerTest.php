<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_user()
    {
        $data = [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token']);
    }


    public function test_login_user()
    {
        User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
        ]);

        $credentials = [
            'email' => 'juan@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    public function test_get_user_data()
    {
        $user = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson(['name' => $user->name, 'email' => $user->email]);
    }

    public function test_logout()
    {
        $user = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Sesión cerrada']);
    }
}
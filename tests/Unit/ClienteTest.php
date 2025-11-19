<?php

namespace Tests\Unit;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;
    public function test_cliente_can_be_created()
    {
        $cliente = Cliente::create([
            'rut' => '12345678-9',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
        ]);

        $this->assertDatabaseHas('clientes', [
            'rut' => '12345678-9',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
        ]);

        $this->assertInstanceOf(Cliente::class, $cliente);
    }

    public function test_cliente_can_be_updated()
    {
        $cliente = Cliente::create([
            'rut' => '12345678-9',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
        ]);

        $cliente->update([
            'nombre' => 'Pedro',
        ]);

        $this->assertDatabaseHas('clientes', [
            'rut' => '12345678-9',
            'nombre' => 'Pedro',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
        ]);
    }

    public function test_cliente_can_be_deleted()
    {
        $cliente = Cliente::create([
            'rut' => '12345678-9',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
        ]);

        $cliente->delete();

        $this->assertDatabaseMissing('clientes', [
            'rut' => '12345678-9',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
        ]);
    }
}
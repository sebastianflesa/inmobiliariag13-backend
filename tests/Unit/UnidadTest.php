<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\Unidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_unidad_can_be_created()
    {
        $proyecto = Proyecto::create([
            'nombre' => 'Proyecto de prueba',
            'descripcion' => 'Descripción del proyecto de prueba',
            'ubicacion' => 'Ubicación del proyecto de prueba',
            'fecha_inicio' => '2022-01-01',
            'fecha_fin' => '2022-12-31',
            'estado' => 'En progreso',
        ]);

        $cliente = Cliente::create([
            'rut' => '12345678-9',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
        ]);

        $unidad = Unidad::create([
            'numero_unidad' => 'Unidad 1',
            'tipo_unidad' => 'Departamento',
            'metraje' => 100,
            'precio_venta' => 100000,
            'estado' => 'Disponible',
            'proyecto_id' => $proyecto->id,
            'cliente_id' => $cliente->id,
        ]);

        $this->assertDatabaseHas('unidades', [
            'numero_unidad' => 'Unidad 1',
            'tipo_unidad' => 'Departamento',
            'metraje' => 100,
            'precio_venta' => 100000,
            'estado' => 'Disponible',
            'proyecto_id' => $proyecto->id,
            'cliente_id' => $cliente->id,
        ]);

        $this->assertInstanceOf(Unidad::class, $unidad);
    }

    public function test_unidad_can_be_updated()
    {
        $proyecto = Proyecto::create([
            'nombre' => 'Proyecto de prueba',
            'descripcion' => 'Descripción del proyecto de prueba',
            'ubicacion' => 'Ubicación del proyecto de prueba',
            'fecha_inicio' => '2022-01-01',
            'fecha_fin' => '2022-12-31',
            'estado' => 'En progreso',
        ]);

        $cliente = Cliente::create([
            'rut' => '12345678-9',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
        ]);

        $unidad = Unidad::create([
            'numero_unidad' => 'Unidad 1',
            'tipo_unidad' => 'Departamento',
            'metraje' => 100,
            'precio_venta' => 100000,
            'estado' => 'Disponible',
            'proyecto_id' => $proyecto->id,
            'cliente_id' => $cliente->id,
        ]);

        $unidad->update([
            'numero_unidad' => 'Unidad 2',
        ]);

        $this->assertDatabaseHas('unidades', [
            'numero_unidad' => 'Unidad 2',
            'tipo_unidad' => 'Departamento',
            'metraje' => 100,
            'precio_venta' => 100000,
            'estado' => 'Disponible',
            'proyecto_id' => $proyecto->id,
            'cliente_id' => $cliente->id,
        ]);
    }

    public function test_unidad_can_be_deleted()
    {
        $proyecto = Proyecto::create([
            'nombre' => 'Proyecto de prueba',
            'descripcion' => 'Descripción del proyecto de prueba',
            'ubicacion' => 'Ubicación del proyecto de prueba',
            'fecha_inicio' => '2022-01-01',
            'fecha_fin' => '2022-12-31',
            'estado' => 'En progreso',
        ]);

        $cliente = Cliente::create([
            'rut' => '12345678-9',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
        ]);

        $unidad = Unidad::create([
            'numero_unidad' => 'Unidad 1',
            'tipo_unidad' => 'Departamento',
            'metraje' => 100,
            'precio_venta' => 100000,
            'estado' => 'Disponible',
            'proyecto_id' => $proyecto->id,
            'cliente_id' => $cliente->id,
        ]);

        $unidad->delete();

        $this->assertDatabaseMissing('unidades', [
            'numero_unidad' => 'Unidad 1',
            'tipo_unidad' => 'Departamento',
            'metraje' => 100,
            'precio_venta' => 100000,
            'estado' => 'Disponible',
            'proyecto_id' => $proyecto->id,
            'cliente_id' => $cliente->id,
        ]);
    }
}
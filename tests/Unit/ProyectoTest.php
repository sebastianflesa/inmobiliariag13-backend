<?php

namespace Tests\Unit;

use App\Models\Proyecto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProyectoTest extends TestCase
{
    use RefreshDatabase;

    public function test_proyecto_can_be_created()
    {
        $proyecto = Proyecto::create([
            'nombre' => 'Proyecto de prueba',
            'descripcion' => 'Descripción del proyecto de prueba',
            'ubicacion' => 'Ubicación del proyecto de prueba',
            'fecha_inicio' => '2022-01-01',
            'fecha_fin' => '2022-12-31',
            'estado' => 'En progreso',
        ]);

        $this->assertDatabaseHas('proyectos', [
            'nombre' => 'Proyecto de prueba',
            'descripcion' => 'Descripción del proyecto de prueba',
            'ubicacion' => 'Ubicación del proyecto de prueba',
            'fecha_inicio' => '2022-01-01',
            'fecha_fin' => '2022-12-31',
            'estado' => 'En progreso',
        ]);

        $this->assertInstanceOf(Proyecto::class, $proyecto);
    }

    public function test_proyecto_can_be_updated()
    {
        $proyecto = Proyecto::create([
            'nombre' => 'Proyecto de prueba',
            'descripcion' => 'Descripción del proyecto de prueba',
            'ubicacion' => 'Ubicación del proyecto de prueba',
            'fecha_inicio' => '2022-01-01',
            'fecha_fin' => '2022-12-31',
            'estado' => 'En progreso',
        ]);

        $proyecto->update([
            'nombre' => 'Proyecto de prueba actualizado',
        ]);

        $this->assertDatabaseHas('proyectos', [
            'nombre' => 'Proyecto de prueba actualizado',
            'descripcion' => 'Descripción del proyecto de prueba',
            'ubicacion' => 'Ubicación del proyecto de prueba',
            'fecha_inicio' => '2022-01-01',
            'fecha_fin' => '2022-12-31',
            'estado' => 'En progreso',
        ]);
    }

    public function test_proyecto_can_be_deleted()
    {
        $proyecto = Proyecto::create([
            'nombre' => 'Proyecto de prueba',
            'descripcion' => 'Descripción del proyecto de prueba',
            'ubicacion' => 'Ubicación del proyecto de prueba',
            'fecha_inicio' => '2022-01-01',
            'fecha_fin' => '2022-12-31',
            'estado' => 'En progreso',
        ]);

        $proyecto->delete();

        $this->assertDatabaseMissing('proyectos', [
            'nombre' => 'Proyecto de prueba',
            'descripcion' => 'Descripción del proyecto de prueba',
            'ubicacion' => 'Ubicación del proyecto de prueba',
            'fecha_inicio' => '2022-01-01',
            'fecha_fin' => '2022-12-31',
            'estado' => 'En progreso',
        ]);
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            $table->text('descripcion');
            $table->string('ubicacion');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado'); // Ejemplo: En construcción, Terminado, etc.
            $table->timestamps(); // Esto incluye: created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};

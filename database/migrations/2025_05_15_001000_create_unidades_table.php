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
        Schema::create('unidades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_unidad');
            $table->string('tipo_unidad'); // Departamento, Casa, Oficina...
            $table->float('metraje');
            $table->decimal('precio_venta', 12, 2);
            $table->string('estado'); // Disponible, Vendido, Reservado...
            $table->uuid('proyecto_id');
            $table->uuid('cliente_id')->nullable();
            $table->timestamps();

            $table->foreign('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};

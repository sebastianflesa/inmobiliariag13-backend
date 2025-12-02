<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->uuid('cliente_id');
            $table->uuid('unidad_id');

            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            $table->decimal('monto_total', 12, 2)->default(0);
            $table->string('tipo_contrato'); // arriendo / venta

            $table->string('estado')->default('activo');
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('unidad_id')->references('id')->on('unidades')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};

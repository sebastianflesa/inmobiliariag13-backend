<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('contrato_id');
            $table->uuid('cliente_id');
            $table->uuid('unidad_id')->nullable();
            $table->uuid('proyecto_id')->nullable();

            $table->tinyInteger('puntaje');
            $table->text('comentario')->nullable();

            $table->timestamps();

            $table->foreign('contrato_id')
                ->references('id')->on('contratos')
                ->onDelete('cascade');

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('unidad_id')->references('id')->on('unidades')->nullOnDelete();
            $table->foreign('proyecto_id')->references('id')->on('proyectos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};

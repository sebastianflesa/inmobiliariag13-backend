<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contrato_id');
            $table->integer('monto');
            $table->date('fecha_pago');
            $table->string('metodo_pago');
            $table->string('estado')->default('pendiente');

            $table->timestamps();

            $table->foreign('contrato_id')
                ->references('id')
                ->on('contratos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};

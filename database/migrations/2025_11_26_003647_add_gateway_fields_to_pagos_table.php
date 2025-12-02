<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // primeros 4 dígitos de tarjeta (5331 / 4595)
            $table->string('card_first_digits', 4)->nullable()->after('estado');

            // id de transacción simulado
            $table->string('transaction_id')->nullable()->after('card_first_digits');

            // auth code estilo "123 456" (opcional almacenar el último usado)
            $table->string('auth_code')->nullable()->after('transaction_id');

            // estado realista de gateway: pendiente_validacion, aprobado, rechazado
            $table->string('gateway_status')->nullable()->after('auth_code');

            // PIN hashed (opcional, guardamos hash si quieres auditar)
            $table->string('pin_hash')->nullable()->after('gateway_status');

            // detalles / payload extra (json)
            $table->json('detalles')->nullable()->after('pin_hash');

            // fecha de validación / aprobación
            $table->timestamp('validado_en')->nullable()->after('detalles');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'card_first_digits',
                'transaction_id',
                'auth_code',
                'gateway_status',
                'pin_hash',
                'detalles',
                'validado_en'
            ]);
        });
    }
};

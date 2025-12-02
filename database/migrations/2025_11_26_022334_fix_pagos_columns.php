<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        Schema::table('pagos', function (Blueprint $table) {

            if (!Schema::hasColumn('pagos', 'pin_validado')) {
                $table->boolean('pin_validado')->nullable();
            }

            if (!Schema::hasColumn('pagos', 'pin_validado_en')) {
                $table->timestamp('pin_validado_en')->nullable();
            }

            if (!Schema::hasColumn('pagos', 'otp_codigo')) {
                $table->string('otp_codigo')->nullable();
            }

            if (!Schema::hasColumn('pagos', 'otp_expira_en')) {
                $table->timestamp('otp_expira_en')->nullable();
            }

            if (!Schema::hasColumn('pagos', 'validado_en')) {
                $table->timestamp('validado_en')->nullable();
            }
        });
    }

    public function down()
    {
        // No borrar columnas, solo agregamos si faltan
    }
};

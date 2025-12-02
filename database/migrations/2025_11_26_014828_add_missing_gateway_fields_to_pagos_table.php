<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {

            // OTP
            $table->string('otp_codigo')->nullable()->after('pin_hash');
            $table->timestamp('otp_expira_en')->nullable()->after('otp_codigo');

            // payload general
            $table->json('payload')->nullable()->after('otp_expira_en');

            // últimos 4 dígitos
            $table->string('card_last4', 4)->nullable()->after('card_first_digits');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'otp_codigo',
                'otp_expira_en',
                'payload',
                'card_last4'
            ]);
        });
    }

};

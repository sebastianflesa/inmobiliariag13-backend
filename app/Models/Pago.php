<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Pago extends Model
{
    protected $fillable = [
        'contrato_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'estado',
        'transaction_id',
        'payment_url',
        'payload',           // 🔥 Nuevo: información completa de la interacción
        'card_last4',        // 🔥 Nuevo: últimos 4 dígitos validados
        'pin_validado',      // 🔥 Nuevo: booleano
        'otp_codigo',        // 🔥 Nuevo: OTP generado
        'otp_expira_en',     // 🔥 Nuevo: timestamp expiración OTP
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'payload' => 'array',  // 🔥 Nuevo: JSON → array automáticamente
        'pin_validado' => 'boolean',
        'otp_expira_en' => 'datetime',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    /** 
     * Retorna true si el pago fue autorizado por la pasarela.
     */
    public function esPagado(): bool
    {
        return $this->estado === 'autorizado';
    }

    /**
     * Formatear el monto como entero.
     */
    protected function monto(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (int) $value,
            set: fn($value) => (int) $value,
        );
    }

    /**
     * Estado legible.
     */
    public function getEstadoLegibleAttribute()
    {
        return match ($this->estado) {
            'pendiente' => 'Pendiente de pago',
            'autorizado' => 'Pagado',
            'rechazado' => 'Pago rechazado',
            default => ucfirst($this->estado),
        };
    }

    /* =======================================================
     * 🔥 NUEVAS FEATURES PARA TARJETA, PIN Y OTP
     * =======================================================*/

    /** Últimos 4 dígitos visibles */
    protected function cardLast4(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? str_pad($value, 4, '*', STR_PAD_LEFT) : null
        );
    }

    /** Retorna true si el PIN ya fue validado */
    public function pinCorrecto(): bool
    {
        return $this->pin_validado === true;
    }

    /** Retorna true si el OTP está vigente y coincide */
    public function verificarOtp(string $otp): bool
    {
        return (
            $this->otp_codigo &&
            $this->otp_expira_en &&
            now()->lessThanOrEqualTo($this->otp_expira_en) &&
            $otp === $this->otp_codigo
        );
    }

    /** Retorna true si el OTP está expirado */
    public function otpExpirado(): bool
    {
        return !$this->otp_expira_en || now()->greaterThan($this->otp_expira_en);
    }
}

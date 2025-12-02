<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contrato extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'unidad_id',
        'fecha_inicio',
        'fecha_fin',
        'monto_total',
        'estado',
        'tipo_contrato',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function calificaciones()
    {
        return $this->hasOne(Calificacion::class, 'contrato_id', 'id');
    }
}

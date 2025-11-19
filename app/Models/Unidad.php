<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Unidad extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'unidades';
    protected $fillable = [
        'numero_unidad',
        'tipo_unidad',
        'metraje',
        'precio_venta',
        'estado',
        'proyecto_id',
        'cliente_id'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}

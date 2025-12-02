<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    use HasFactory;

    protected $table = 'calificaciones';

    protected $fillable = [
        'contrato_id',
        'cliente_id',
        'unidad_id',
        'proyecto_id',
        'puntaje',
        'comentario',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }
}
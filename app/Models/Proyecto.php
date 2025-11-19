<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Proyecto extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nombre',
        'descripcion',
        'ubicacion',
        'fecha_inicio',
        'fecha_fin',
        'estado'
    ];

    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }
}

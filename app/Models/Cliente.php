<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Cliente extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'rut',
        'nombre',
        'apellido',
        'email',
        'telefono'
    ];

    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre', 'descripcion', 'color', 'activo', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificador()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'model_id')
                    ->where('model_type', self::class)
                    ->orderBy('created_at', 'desc');
    }
}

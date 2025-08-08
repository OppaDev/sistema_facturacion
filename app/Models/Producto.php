<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Categoria;
use App\Models\FacturaDetalle;
use App\Models\User;
use App\Traits\HasObfuscatedId;

class Producto extends Model
{
    use SoftDeletes, HasObfuscatedId;

    protected $fillable = [
        'nombre', 'descripcion', 'imagen', 'categoria_id', 'stock', 'precio', 'created_by', 'updated_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function facturaDetalles()
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificador()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

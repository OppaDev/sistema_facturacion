<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasObfuscatedId;

class Pago extends Model
{
    use HasObfuscatedId;
    
    protected $fillable = [
        'factura_id',
        'tipo_pago',
        'monto',
        'numero_transaccion',
        'observacion',
        'estado',
        'pagado_por',
        'validado_por',
        'validated_at'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'validated_at' => 'datetime'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'numero_transaccion',
        'observacion',
        'validado_por',
        'validated_at',
    ];

    // Relaciones
    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function pagadoPor()
    {
        return $this->belongsTo(User::class, 'pagado_por');
    }

    public function validadoPor()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    // Métodos de estado
    public function isPendiente()
    {
        return $this->estado === 'pendiente';
    }

    public function isAprobado()
    {
        return $this->estado === 'aprobado';
    }

    public function isRechazado()
    {
        return $this->estado === 'rechazado';
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', 'aprobado');
    }
}

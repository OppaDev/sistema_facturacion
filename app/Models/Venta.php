<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero_venta',
        'fecha_venta',
        'usuario_id',
        'tipo_pago',
        'monto_efectivo',
        'monto_tarjeta',
        'monto_transferencia',
        'monto_deuna',
        'subtotal',
        'iva',
        'descuento',
        'total',
        'cliente_nombre',
        'cliente_identificacion',
        'notas',
        'estado',
        'turno_id',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'monto_efectivo' => 'decimal:2',
        'monto_tarjeta' => 'decimal:2',
        'monto_transferencia' => 'decimal:2',
        'monto_deuna' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relaciones
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoCaja::class, 'turno_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
    }

    // Métodos auxiliares
    public function getNumeroFormateado(): string
    {
        return $this->numero_venta;
    }

    public function isCompletada(): bool
    {
        return $this->estado === 'completada';
    }

    public function isAnulada(): bool
    {
        return $this->estado === 'anulada';
    }

    public function getTipoPagoLabel(): string
    {
        $labels = [
            'efectivo' => 'Efectivo',
            'tarjeta' => 'Tarjeta',
            'transferencia' => 'Transferencia',
            'deuna' => 'Deuna App',
            'mixto' => 'Mixto',
        ];
        
        return $labels[$this->tipo_pago] ?? $this->tipo_pago;
    }

    // Generar número de venta automático
    public static function generarNumeroVenta(): string
    {
        $ultimaVenta = self::withTrashed()->latest('id')->first();
        $numero = $ultimaVenta ? (int) substr($ultimaVenta->numero_venta, 2) + 1 : 1;
        return 'V-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}

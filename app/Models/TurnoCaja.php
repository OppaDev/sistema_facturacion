<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TurnoCaja extends Model
{
    protected $table = 'turnos_caja';

    protected $fillable = [
        'usuario_id',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'monto_final',
        'total_ventas',
        'total_efectivo',
        'total_tarjeta',
        'total_transferencia',
        'total_deuna',
        'diferencia',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_inicial' => 'decimal:2',
        'monto_final' => 'decimal:2',
        'total_ventas' => 'decimal:2',
        'total_efectivo' => 'decimal:2',
        'total_tarjeta' => 'decimal:2',
        'total_transferencia' => 'decimal:2',
        'total_deuna' => 'decimal:2',
        'diferencia' => 'decimal:2',
    ];

    // Relaciones
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'turno_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'turno_id');
    }

    // Métodos auxiliares
    public function isAbierto(): bool
    {
        return $this->estado === 'abierto';
    }

    public function isCerrado(): bool
    {
        return $this->estado === 'cerrado';
    }

    public function calcularTotales(): void
    {
        $ventas = $this->ventas()->where('estado', 'completada')->get();
        
        $this->total_ventas = $ventas->sum('total');
        $this->total_efectivo = $ventas->sum('monto_efectivo');
        $this->total_tarjeta = $ventas->sum('monto_tarjeta');
        $this->total_transferencia = $ventas->sum('monto_transferencia');
        $this->total_deuna = $ventas->sum('monto_deuna');
        
        $this->save();
    }

    public function calcularDiferencia(float $montoReal): float
    {
        $montoEsperado = $this->monto_inicial + $this->total_efectivo;
        return $montoReal - $montoEsperado;
    }

    // Verificar si hay un turno abierto para el usuario
    public static function turnoAbiertoParaUsuario(int $usuarioId): ?self
    {
        return self::where('usuario_id', $usuarioId)
            ->where('estado', 'abierto')
            ->latest('fecha_apertura')
            ->first();
    }
}

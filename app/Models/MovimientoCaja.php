<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoCaja extends Model
{
    protected $table = 'movimientos_caja';

    protected $fillable = [
        'turno_id',
        'tipo',
        'concepto',
        'monto',
        'usuario_id',
        'notas',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    // Relaciones
    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoCaja::class, 'turno_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Métodos auxiliares
    public function isIngreso(): bool
    {
        return $this->tipo === 'ingreso';
    }

    public function isEgreso(): bool
    {
        return $this->tipo === 'egreso';
    }

    public function isAjuste(): bool
    {
        return $this->tipo === 'ajuste';
    }

    public function getTipoLabel(): string
    {
        $labels = [
            'ingreso' => 'Ingreso',
            'egreso' => 'Egreso',
            'ajuste' => 'Ajuste',
        ];
        
        return $labels[$this->tipo] ?? $this->tipo;
    }
}

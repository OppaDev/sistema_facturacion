<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Factura;
use App\Models\Producto;

/**
 * @property int $id
 * @property int $factura_id
 * @property int $producto_id
 * @property int $cantidad
 * @property string $precio_unitario
 * @property string $subtotal
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Factura $factura
 * @property-read Producto $producto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle whereCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle whereFacturaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle wherePrecioUnitario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle whereProductoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FacturaDetalle withoutTrashed()
 * @mixin \Eloquent
 */
class FacturaDetalle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'factura_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal', 'created_by', 'updated_by'
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}

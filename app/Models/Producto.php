<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Categoria;
use App\Models\FacturaDetalle;
use App\Models\User;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property string $precio
 * @property int $stock
 * @property string|null $imagen
 * @property string $estado
 * @property int|null $categoria_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Categoria|null $categoria
 * @property-read User|null $creador
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FacturaDetalle> $facturaDetalles
 * @property-read int|null $factura_detalles_count
 * @property-read User|null $modificador
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereCategoriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereImagen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto wherePrecio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto withoutTrashed()
 * @mixin \Eloquent
 */
class Producto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre', 'descripcion', 'imagen', 'categoria_id', 'stock', 'precio', 'created_by', 'updated_by'
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;
use App\Models\User;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string $model_type
 * @property int $model_id
 * @property string|null $old_values
 * @property string|null $new_values
 * @property string|null $description
 * @property string|null $observacion
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereObservacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereUserId($value)
 * @mixin \Eloquent
 */
class Auditoria extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id', 'old_values', 'new_values', 'description', 'observacion', 'ip_address', 'user_agent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getClienteNombre()
    {
        if ($this->model_type === Cliente::class) {
            $cliente = Cliente::withTrashed()->find($this->model_id);
            return $cliente ? $cliente->nombre : 'Cliente no encontrado';
        }
        return 'N/A';
    }

    public function getAfectado()
    {
        if ($this->model_type === \App\Models\Cliente::class) {
            $cliente = \App\Models\Cliente::withTrashed()->find($this->model_id);
            return $cliente ? $cliente->nombre : 'Cliente no encontrado';
        }
        if ($this->model_type === \App\Models\Producto::class) {
            $producto = \App\Models\Producto::withTrashed()->find($this->model_id);
            return $producto ? $producto->nombre : 'Producto no encontrado';
        }
        if ($this->model_type === \App\Models\User::class) {
            $user = \App\Models\User::withTrashed()->find($this->model_id);
            return $user ? $user->name : 'Usuario no encontrado';
        }
        if ($this->model_type === \App\Models\Factura::class) {
            $factura = \App\Models\Factura::withTrashed()->find($this->model_id);
            return $factura ? 'Factura #' . $factura->id : 'Factura no encontrada';
        }
        return 'ID: ' . $this->model_id;
    }
}

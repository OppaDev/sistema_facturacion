<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Factura;
use App\Models\User;

/**
 * @property int $id
 * @property string $nombre
 * @property string $email
 * @property string|null $telefono
 * @property string|null $direccion
 * @property string $password
 * @property string $estado
 * @property int|null $user_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Factura> $facturas
 * @property-read int|null $facturas_count
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTelefono($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente withoutTrashed()
 * @mixin \Eloquent
 */
class Cliente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre', 'email', 'password', 'telefono', 'direccion', 'estado', 'created_by', 'updated_by', 'user_id'
    ];

    protected $hidden = [
        'password',
    ];

    protected $dates = ['deleted_at'];

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Método para verificar si el cliente está eliminado
    public function isDeleted()
    {
        return $this->trashed();
    }
}

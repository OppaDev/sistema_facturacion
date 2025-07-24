<?php

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'direccion',
        'estado',
        'pending_delete_at',
        'observacion',
        'motivo_suspension',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pending_delete_at' => 'datetime',
        ];
    }

    protected $dates = [
        'email_verified_at',
        'deleted_at',
        'pending_delete_at',
    ];

    /**
     * Relación con las facturas donde este usuario es el cliente
     */
    public function facturasComoCliente()
    {
        return $this->hasMany(Factura::class, 'cliente_id');
    }

    /**
     * Relación con las facturas creadas por este usuario
     */
    public function facturasCreadas()
    {
        return $this->hasMany(Factura::class, 'usuario_id');
    }

    /**
     * Verificar si el usuario tiene rol de cliente
     */
    public function esCliente()
    {
        return $this->hasRole('Cliente');
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive()
    {
        return $this->estado === 'activo';
    }

    /**
     * Verificar si el usuario está pendiente de eliminación
     */
    public function isPendingDelete()
    {
        return $this->pending_delete_at !== null;
    }

    /**
     * Verificar si el usuario está eliminado
     */
    public function isDeleted()
    {
        return $this->trashed();
    }
}
class Role extends SpatieRole
{
    use SoftDeletes;
        // ...

}
    
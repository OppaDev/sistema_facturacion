<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Factura;
use App\Models\User;
use Laravel\Sanctum\HasApiTokens;

class Cliente extends Authenticatable
{
    use SoftDeletes, HasApiTokens;

    protected $fillable = [
        'nombre', 'email', 'password', 'telefono', 'direccion', 'estado', 'created_by', 'updated_by', 'user_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
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

    // Métodos requeridos para la autenticación
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        // Este método ahora busca usuarios con rol Cliente
        if ($this->model_type === 'App\Models\Cliente') {
            // Buscar usuario correspondiente por ID (para registros históricos)
            $user = User::withTrashed()->find($this->model_id);
            return $user ? $user->name : 'Cliente no encontrado';
        }
        return 'N/A';
    }

    public function getAfectado()
    {
        // Manejar referencias históricas a Cliente como referencias a User
        if ($this->model_type === 'App\Models\Cliente' || $this->model_type === \App\Models\User::class) {
            $user = \App\Models\User::withTrashed()->find($this->model_id);
            return $user ? $user->name : 'Usuario/Cliente no encontrado';
        }
        if ($this->model_type === \App\Models\Producto::class) {
            $producto = \App\Models\Producto::withTrashed()->find($this->model_id);
            return $producto ? $producto->nombre : 'Producto no encontrado';
        }
        if ($this->model_type === \App\Models\Factura::class) {
            $factura = \App\Models\Factura::withTrashed()->find($this->model_id);
            return $factura ? 'Factura #' . $factura->id : 'Factura no encontrada';
        }
        return 'ID: ' . $this->model_id;
    }
}

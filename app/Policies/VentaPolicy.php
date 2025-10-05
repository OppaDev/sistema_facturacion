<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venta;
use Illuminate\Auth\Access\Response;

class VentaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Solo Administrador y Ventas pueden ver ventas
        return $user->hasAnyRole(['Administrador', 'Ventas']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Venta $venta): bool
    {
        // Administrador puede ver todas, Ventas solo las suyas
        if ($user->hasRole('Administrador')) {
            return true;
        }
        
        return $user->hasRole('Ventas') && $venta->usuario_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo Administrador y Ventas pueden crear ventas
        return $user->hasAnyRole(['Administrador', 'Ventas']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Venta $venta): bool
    {
        // Las ventas no se pueden editar, solo anular
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Venta $venta): bool
    {
        // Administrador puede anular cualquier venta, Ventas solo las suyas del mismo día
        if ($user->hasRole('Administrador')) {
            return true;
        }
        
        if ($user->hasRole('Ventas') && $venta->usuario_id === $user->id) {
            // Solo puede anular ventas del mismo día
            return $venta->fecha_venta->isToday();
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Venta $venta): bool
    {
        // Solo Administrador puede restaurar ventas anuladas
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Venta $venta): bool
    {
        // No se permite eliminación permanente
        return false;
    }
}

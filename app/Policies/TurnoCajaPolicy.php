<?php

namespace App\Policies;

use App\Models\TurnoCaja;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TurnoCajaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Solo Administrador y Ventas pueden ver turnos
        return $user->hasAnyRole(['Administrador', 'Ventas']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TurnoCaja $turnoCaja): bool
    {
        // Administrador puede ver todos, Ventas solo los suyos
        if ($user->hasRole('Administrador')) {
            return true;
        }
        
        return $user->hasRole('Ventas') && $turnoCaja->usuario_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo Administrador y Ventas pueden abrir turnos
        return $user->hasAnyRole(['Administrador', 'Ventas']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TurnoCaja $turnoCaja): bool
    {
        // Administrador puede cerrar cualquier turno, Ventas solo el suyo
        if ($user->hasRole('Administrador')) {
            return true;
        }
        
        return $user->hasRole('Ventas') && $turnoCaja->usuario_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TurnoCaja $turnoCaja): bool
    {
        // Solo Administrador puede eliminar turnos
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TurnoCaja $turnoCaja): bool
    {
        // Solo Administrador puede restaurar turnos
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TurnoCaja $turnoCaja): bool
    {
        // No se permite eliminación permanente
        return false;
    }
}

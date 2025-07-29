<?php

namespace App\Observers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Si el usuario se registra desde el frontend público y no tiene rol asignado,
        // asignar automáticamente el rol de Cliente
        if (!$user->roles()->exists()) {
            $clienteRole = Role::where('name', 'Cliente')->first();
            if ($clienteRole) {
                $user->assignRole($clienteRole);
            }
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Lógica adicional cuando se actualiza un usuario si es necesaria
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Lógica adicional cuando se elimina un usuario si es necesaria
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // Lógica adicional cuando se restaura un usuario si es necesaria
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Lógica adicional cuando se elimina permanentemente un usuario si es necesaria
    }
}

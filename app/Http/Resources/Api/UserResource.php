<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'obfuscated_id' => $this->obfuscated_id,
            'name' => $this->name,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'estado' => $this->estado,
            'roles' => $this->getRoleNames(),
            'is_active' => $this->isActive(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Información adicional basada en rol del usuario autenticado
            $this->mergeWhen($request->user()?->hasAnyRole(['Administrador', 'Secretario']), [
                'email_verified_at' => $this->email_verified_at?->toISOString(),
                'observacion' => $this->observacion,
                'is_pending_delete' => $this->isPendingDelete(),
                'is_deleted' => $this->isDeleted(),
            ]),
            
            // Estadísticas para clientes (solo si el usuario autenticado puede verlas)
            $this->mergeWhen($this->esCliente() && $this->canViewStatistics($request), [
                'statistics' => [
                    'total_facturas' => $this->facturasComoCliente()->count(),
                    'total_gastado' => $this->facturasComoCliente()->sum('total'),
                    'facturas_pendientes' => $this->facturasComoCliente()->where('estado', 'pendiente')->count(),
                    'facturas_pagadas' => $this->facturasComoCliente()->where('estado', 'pagada')->count(),
                ],
            ]),
        ];
    }

    /**
     * Determinar si se pueden ver las estadísticas del usuario
     */
    private function canViewStatistics(Request $request): bool
    {
        $currentUser = $request->user();
        
        if (!$currentUser) {
            return false;
        }
        
        // Admins y secretarios pueden ver estadísticas de cualquier cliente
        if ($currentUser->hasAnyRole(['Administrador', 'Secretario'])) {
            return true;
        }
        
        // Los clientes solo pueden ver sus propias estadísticas
        return $currentUser->id === $this->id;
    }
}
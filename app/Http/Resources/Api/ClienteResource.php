<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'estado' => $this->estado,
            'roles' => $this->getRoleNames(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'is_deleted' => $this->deleted_at ? true : false,
            'is_pending_delete' => $this->pending_delete_at ? true : false,
        ];

        // Agregar estadísticas si el usuario es Admin/Secretario y se cargaron las relaciones
        if ($request->user()->hasAnyRole(['Administrador', 'Secretario'])) {
            $data['estadisticas'] = [
                'total_facturas' => $this->whenLoaded('facturasComoCliente', function() {
                    return $this->facturasComoCliente->count();
                }),
                'facturas_pendientes' => $this->whenLoaded('facturasComoCliente', function() {
                    return $this->facturasComoCliente->where('estado', 'pendiente')->count();
                }),
                'facturas_pagadas' => $this->whenLoaded('facturasComoCliente', function() {
                    return $this->facturasComoCliente->where('estado', 'pagada')->count();
                }),
                'total_pagos' => $this->whenLoaded('pagos', function() {
                    return $this->pagos->count();
                }),
                'monto_total_facturas' => $this->whenLoaded('facturasComoCliente', function() {
                    return number_format($this->facturasComoCliente->sum('total'), 2);
                }),
            ];
        }

        return $data;
    }
}
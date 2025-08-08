<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaDetalleResource extends JsonResource
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
            'cantidad' => $this->cantidad,
            'precio_unitario' => number_format((float) $this->precio_unitario, 2, '.', ''),
            'subtotal' => number_format((float) $this->subtotal, 2, '.', ''),
            'precio_unitario_formatted' => '$' . number_format((float) $this->precio_unitario, 2),
            'subtotal_formatted' => '$' . number_format((float) $this->subtotal, 2),
            
            // Información del producto
            'producto' => $this->whenLoaded('producto', function () {
                return [
                    'id' => $this->producto->id,
                    'obfuscated_id' => $this->producto->obfuscated_id,
                    'nombre' => $this->producto->nombre,
                    'descripcion' => $this->producto->descripcion,
                    'imagen_url' => $this->producto->imagen 
                        ? asset('storage/' . $this->producto->imagen) 
                        : null,
                ];
            }),
            
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
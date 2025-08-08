<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
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
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => number_format((float) $this->precio, 2, '.', ''),
            'precio_formatted' => '$' . number_format((float) $this->precio, 2),
            'stock' => $this->stock,
            'stock_status' => $this->getStockStatus(),
            'categoria' => $this->whenLoaded('categoria', function () {
                return [
                    'id' => $this->categoria->id,
                    'nombre' => $this->categoria->nombre ?? 'Sin categoría',
                ];
            }),
            'imagen_url' => $this->imagen ? asset('storage/' . $this->imagen) : null,
            'has_imagen' => !empty($this->imagen),
            'is_available' => $this->stock > 0,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Información adicional para usuarios autorizados
            $this->mergeWhen($request->user()?->hasAnyRole(['Administrador', 'Secretario']), [
                'created_by_name' => $this->whenLoaded('creador', $this->creador?->name),
                'updated_by_name' => $this->whenLoaded('modificador', $this->modificador?->name),
                'statistics' => [
                    'total_vendido' => $this->getTotalVendido(),
                    'veces_vendido' => $this->getVecesVendido(),
                    'revenue_total' => $this->getRevenueTotal(),
                ],
            ]),
        ];
    }

    /**
     * Obtener el estado del stock
     */
    private function getStockStatus(): string
    {
        if ($this->stock <= 0) {
            return 'sin_stock';
        } elseif ($this->stock <= 10) {
            return 'stock_bajo';
        } else {
            return 'disponible';
        }
    }

    /**
     * Obtener total de unidades vendidas
     */
    private function getTotalVendido(): int
    {
        return $this->facturaDetalles()
            ->whereHas('factura', function ($query) {
                $query->whereIn('estado', ['pagada', 'pendiente']);
            })
            ->sum('cantidad');
    }

    /**
     * Obtener número de veces que se ha vendido el producto
     */
    private function getVecesVendido(): int
    {
        return $this->facturaDetalles()
            ->whereHas('factura', function ($query) {
                $query->whereIn('estado', ['pagada', 'pendiente']);
            })
            ->distinct('factura_id')
            ->count();
    }

    /**
     * Obtener revenue total del producto
     */
    private function getRevenueTotal(): string
    {
        $total = $this->facturaDetalles()
            ->whereHas('factura', function ($query) {
                $query->where('estado', 'pagada');
            })
            ->selectRaw('SUM(cantidad * precio_unitario) as revenue')
            ->first()
            ->revenue ?? 0;

        return number_format((float) $total, 2, '.', '');
    }
}
<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
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
            'numero_factura' => $this->getNumeroFormateado(),
            'numero_secuencial' => $this->numero_secuencial,
            'estado' => $this->estado,
            'estado_visual' => $this->getEstadoVisual(),
            'subtotal' => number_format((float) $this->subtotal, 2, '.', ''),
            'iva' => number_format((float) $this->iva, 2, '.', ''),
            'total' => number_format((float) $this->total, 2, '.', ''),
            'subtotal_formatted' => '$' . number_format((float) $this->subtotal, 2),
            'iva_formatted' => '$' . number_format((float) $this->iva, 2),
            'total_formatted' => '$' . number_format((float) $this->total, 2),
            'fecha_emision' => $this->fecha_emision?->toDateString(),
            'fecha_emision_formatted' => $this->fecha_emision?->format('d/m/Y'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Cliente información
            'cliente' => $this->whenLoaded('cliente', function () {
                return [
                    'id' => $this->cliente->id,
                    'obfuscated_id' => $this->cliente->obfuscated_id,
                    'name' => $this->cliente->name,
                    'email' => $this->cliente->email,
                ];
            }),
            
            // Vendedor información
            'vendedor' => $this->whenLoaded('usuario', function () {
                return [
                    'id' => $this->usuario->id,
                    'name' => $this->usuario->name,
                ];
            }),
            
            // Detalles de la factura
            'detalles' => FacturaDetalleResource::collection($this->whenLoaded('detalles')),
            
            // Información de pagos
            'pagos' => PagoResource::collection($this->whenLoaded('pagos')),
            'total_pagado' => $this->getTotalPagado(),
            'saldo_pendiente' => $this->getSaldoPendiente(),
            'is_fully_paid' => $this->isFullyPaid(),
            
            // Estados SRI
            'sri_status' => [
                'tiene_datos_sri' => $this->tieneDatosSRI(),
                'is_firmada' => $this->isFirmada(),
                'is_emitida' => $this->isEmitida(),
                'estado_autorizacion' => $this->getEstadoAutorizacion(),
                'fecha_firma' => $this->fecha_firma?->toISOString(),
                'fecha_emision_email' => $this->fecha_emision_email?->toISOString(),
            ],
            
            // Información adicional para usuarios autorizados
            $this->mergeWhen($request->user()?->hasAnyRole(['Administrador', 'Secretario']), [
                'ambiente' => $this->ambiente,
                'tipo_emision' => $this->tipo_emision,
                'mensaje_autorizacion' => $this->mensaje_autorizacion,
                'imagen_qr' => $this->imagen_qr ? asset('storage/' . $this->imagen_qr) : null,
            ]),
        ];
    }

    /**
     * Calcular total pagado
     */
    private function getTotalPagado(): string
    {
        $total = $this->pagos()
            ->where('estado', 'aprobado')
            ->sum('monto');
            
        return number_format((float) $total, 2, '.', '');
    }

    /**
     * Calcular saldo pendiente
     */
    private function getSaldoPendiente(): string
    {
        $totalPagado = (float) $this->getTotalPagado();
        $totalFactura = (float) $this->total;
        $saldo = max(0, $totalFactura - $totalPagado);
        
        return number_format($saldo, 2, '.', '');
    }

    /**
     * Verificar si está completamente pagada
     */
    private function isFullyPaid(): bool
    {
        return (float) $this->getSaldoPendiente() <= 0.01; // Tolerancia de 1 centavo
    }
}
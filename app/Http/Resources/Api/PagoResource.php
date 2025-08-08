<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PagoResource extends JsonResource
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
            'tipo_pago' => $this->tipo_pago,
            'tipo_pago_formatted' => ucfirst($this->tipo_pago),
            'monto' => number_format((float) $this->monto, 2, '.', ''),
            'monto_formatted' => '$' . number_format((float) $this->monto, 2),
            'estado' => $this->estado,
            'estado_formatted' => ucfirst($this->estado),
            'estado_visual' => $this->getEstadoVisual(),
            'is_pendiente' => $this->isPendiente(),
            'is_aprobado' => $this->isAprobado(),
            'is_rechazado' => $this->isRechazado(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Información de la factura
            'factura' => $this->whenLoaded('factura', function () {
                return [
                    'id' => $this->factura->id,
                    'obfuscated_id' => $this->factura->obfuscated_id,
                    'numero_factura' => $this->factura->getNumeroFormateado(),
                    'total' => number_format((float) $this->factura->total, 2, '.', ''),
                    'estado' => $this->factura->estado,
                ];
            }),
            
            // Información del pagador
            'pagador' => $this->whenLoaded('pagadoPor', function () {
                return [
                    'id' => $this->pagadoPor->id,
                    'name' => $this->pagadoPor->name,
                    'email' => $this->pagadoPor->email,
                ];
            }),
            
            // Información adicional para usuarios autorizados
            $this->mergeWhen($request->user()?->hasAnyRole(['Administrador', 'Secretario']), [
                'validated_at' => $this->validated_at?->toISOString(),
                'validador' => $this->whenLoaded('validadoPor', function () {
                    return [
                        'id' => $this->validadoPor->id,
                        'name' => $this->validadoPor->name,
                    ];
                }),
            ]),
        ];
    }

    /**
     * Obtener información visual del estado
     */
    private function getEstadoVisual(): array
    {
        switch ($this->estado) {
            case 'pendiente':
                return [
                    'texto' => 'PENDIENTE',
                    'clase' => 'warning',
                    'icono' => 'fas fa-clock',
                    'color' => '#ffc107'
                ];
            case 'aprobado':
                return [
                    'texto' => 'APROBADO',
                    'clase' => 'success',
                    'icono' => 'fas fa-check-circle',
                    'color' => '#28a745'
                ];
            case 'rechazado':
                return [
                    'texto' => 'RECHAZADO',
                    'clase' => 'danger',
                    'icono' => 'fas fa-times-circle',
                    'color' => '#dc3545'
                ];
            default:
                return [
                    'texto' => 'DESCONOCIDO',
                    'clase' => 'secondary',
                    'icono' => 'fas fa-question-circle',
                    'color' => '#6c757d'
                ];
        }
    }
}
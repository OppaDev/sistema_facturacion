<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Factura;

class StorePagoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo usuarios con rol Cliente pueden crear pagos
        return $this->user()->hasRole('Cliente');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'factura_id' => [
                'required',
                'integer',
                'exists:facturas,id',
                function ($attribute, $value, $fail) {
                    // Validar que la factura pertenezca al cliente autenticado
                    $factura = Factura::find($value);
                    if ($factura && $factura->cliente_id !== $this->user()->id) {
                        $fail('No tienes permiso para pagar esta factura.');
                    }
                    
                    // Validar que la factura esté pendiente
                    if ($factura && !$factura->isPendiente()) {
                        $fail('Esta factura ya no está pendiente de pago.');
                    }
                }
            ],
            'tipo_pago' => 'required|in:efectivo,tarjeta,transferencia,cheque',
            'monto' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    // Validar que el monto coincida con el total de la factura
                    $factura = Factura::find($this->factura_id);
                    if ($factura && abs((float)$value - (float)$factura->total) > 0.01) {
                        $fail('El monto debe coincidir exactamente con el total de la factura: $' . number_format($factura->total, 2));
                    }
                }
            ],
            'numero_transaccion' => 'nullable|string|max:255',
            'observacion' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'factura_id.required' => 'El ID de la factura es obligatorio.',
            'factura_id.exists' => 'La factura especificada no existe.',
            'tipo_pago.required' => 'El tipo de pago es obligatorio.',
            'tipo_pago.in' => 'El tipo de pago debe ser: efectivo, tarjeta, transferencia o cheque.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'numero_transaccion.max' => 'El número de transacción no puede exceder 255 caracteres.',
            'observacion.max' => 'La observación no puede exceder 1000 caracteres.'
        ];
    }
}

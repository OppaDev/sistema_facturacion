<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePagoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Clientes pueden crear pagos para sus propias facturas
        // Admins y Secretarios pueden crear pagos para cualquier factura
        return true; // La autorización específica se maneja en el controlador
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'factura_id' => [
                'required',
                'integer',
                'exists:facturas,id',
                'min:1',
            ],
            'tipo_pago' => [
                'required',
                'string',
                Rule::in(['efectivo', 'transferencia', 'tarjeta', 'cheque']),
            ],
            'monto' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'decimal:0,2',
            ],
            'numero_transaccion' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\-\_]+$/', // Solo alfanumérico, guiones y guiones bajos
            ],
            'observacion' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\-\!\?\(\)]+$/',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'factura_id.exists' => 'La factura seleccionada no existe.',
            'tipo_pago.in' => 'El tipo de pago debe ser: efectivo, transferencia, tarjeta o cheque.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'monto.decimal' => 'El monto debe tener máximo 2 decimales.',
            'numero_transaccion.regex' => 'El número de transacción solo puede contener letras, números, guiones y guiones bajos.',
            'observacion.regex' => 'La observación contiene caracteres no permitidos.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'error' => 'VALIDATION_ERROR',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $data = [];
        
        if ($this->has('factura_id')) {
            $data['factura_id'] = $this->sanitizeInteger($this->factura_id);
        }
        
        if ($this->has('tipo_pago')) {
            $data['tipo_pago'] = $this->sanitizeString($this->tipo_pago);
        }
        
        if ($this->has('monto')) {
            $data['monto'] = $this->sanitizeDecimal($this->monto);
        }
        
        if ($this->has('numero_transaccion')) {
            $data['numero_transaccion'] = $this->sanitizeAlphanumeric($this->numero_transaccion);
        }
        
        if ($this->has('observacion')) {
            $data['observacion'] = $this->sanitizeString($this->observacion);
        }
        
        $this->merge($data);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que la factura existe y está activa
            if ($this->factura_id) {
                $factura = \App\Models\Factura::find($this->factura_id);
                if ($factura) {
                    if ($factura->isAnulada()) {
                        $validator->errors()->add('factura_id', 'No se pueden crear pagos para facturas anuladas.');
                    }
                    
                    // Validar que el monto no exceda el total de la factura
                    if ($this->monto && $this->monto > $factura->total) {
                        $validator->errors()->add('monto', 'El monto no puede ser mayor al total de la factura.');
                    }
                    
                    // Si es cliente, validar que sea el dueño de la factura
                    $user = $this->user();
                    if ($user && $user->hasRole('Cliente') && $factura->cliente_id !== $user->id) {
                        $validator->errors()->add('factura_id', 'No tiene permisos para crear pagos en esta factura.');
                    }
                }
            }
            
            // Validar número de transacción según tipo de pago
            if ($this->tipo_pago && in_array($this->tipo_pago, ['transferencia', 'tarjeta'])) {
                if (empty($this->numero_transaccion)) {
                    $validator->errors()->add('numero_transaccion', 'El número de transacción es requerido para este tipo de pago.');
                }
            }
        });
    }

    /**
     * Sanitizar string general
     */
    private function sanitizeString(?string $value): ?string
    {
        if ($value === null) return null;
        return trim(strip_tags($value));
    }

    /**
     * Sanitizar alfanumérico
     */
    private function sanitizeAlphanumeric(?string $value): ?string
    {
        if ($value === null) return null;
        return preg_replace('/[^a-zA-Z0-9\-\_]/', '', trim($value));
    }

    /**
     * Sanitizar decimal
     */
    private function sanitizeDecimal($value)
    {
        if ($value === null) return null;
        $value = preg_replace('/[^0-9\.\,]/', '', (string) $value);
        $value = str_replace(',', '.', $value);
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Sanitizar integer
     */
    private function sanitizeInteger($value)
    {
        if ($value === null) return null;
        $value = preg_replace('/[^0-9]/', '', (string) $value);
        return is_numeric($value) ? (int) $value : null;
    }
}
<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFacturaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['Administrador', 'Secretario']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'cliente_id' => [
                'required',
                'integer',
                'exists:users,id',
                'min:1',
            ],
            'detalles' => [
                'required',
                'array',
                'min:1',
                'max:50', // Máximo 50 productos por factura
            ],
            'detalles.*.producto_id' => [
                'required',
                'integer',
                'exists:productos,id',
                'min:1',
            ],
            'detalles.*.cantidad' => [
                'required',
                'integer',
                'min:1',
                'max:9999',
            ],
            'detalles.*.precio_unitario' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'decimal:0,2',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'detalles.required' => 'Debe agregar al menos un producto.',
            'detalles.min' => 'Debe agregar al menos un producto.',
            'detalles.max' => 'No puede agregar más de 50 productos por factura.',
            'detalles.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mínimo 1.',
            'detalles.*.cantidad.max' => 'La cantidad no puede exceder 9,999 unidades.',
            'detalles.*.precio_unitario.min' => 'El precio unitario debe ser mayor a 0.',
            'detalles.*.precio_unitario.decimal' => 'El precio unitario debe tener máximo 2 decimales.',
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
        
        if ($this->has('cliente_id')) {
            $data['cliente_id'] = $this->sanitizeInteger($this->cliente_id);
        }
        
        if ($this->has('detalles') && is_array($this->detalles)) {
            $detalles = [];
            foreach ($this->detalles as $detalle) {
                $detalles[] = [
                    'producto_id' => $this->sanitizeInteger($detalle['producto_id'] ?? null),
                    'cantidad' => $this->sanitizeInteger($detalle['cantidad'] ?? null),
                    'precio_unitario' => $this->sanitizeDecimal($detalle['precio_unitario'] ?? null),
                ];
            }
            $data['detalles'] = $detalles;
        }
        
        $this->merge($data);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que el cliente tenga rol de Cliente
            if ($this->cliente_id) {
                $cliente = \App\Models\User::find($this->cliente_id);
                if ($cliente && !$cliente->hasRole('Cliente')) {
                    $validator->errors()->add('cliente_id', 'El usuario seleccionado no es un cliente válido.');
                }
            }
            
            // Validar stock disponible para cada producto
            if ($this->has('detalles') && is_array($this->detalles)) {
                foreach ($this->detalles as $index => $detalle) {
                    if (isset($detalle['producto_id']) && isset($detalle['cantidad'])) {
                        $producto = \App\Models\Producto::find($detalle['producto_id']);
                        if ($producto && $producto->stock < $detalle['cantidad']) {
                            $validator->errors()->add(
                                "detalles.{$index}.cantidad",
                                "Stock insuficiente para {$producto->nombre}. Stock disponible: {$producto->stock}"
                            );
                        }
                    }
                }
            }
        });
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
}
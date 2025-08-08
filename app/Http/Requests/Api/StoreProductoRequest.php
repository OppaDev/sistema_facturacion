<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductoRequest extends FormRequest
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
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'unique:productos,nombre',
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\-]+$/', // Letras, números, espacios, puntos, comas, guiones
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\-\!\?\(\)]+$/', // Descripción con puntuación básica
            ],
            'categoria_id' => [
                'required',
                'integer',
                'exists:categorias,id',
                'min:1',
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],
            'precio' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'decimal:0,2', // Hasta 2 decimales
            ],
            'imagen' => [
                'nullable',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048', // 2MB máximo
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un producto con este nombre.',
            'nombre.regex' => 'El nombre contiene caracteres no permitidos.',
            'descripcion.regex' => 'La descripción contiene caracteres no permitidos.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'precio.decimal' => 'El precio debe tener máximo 2 decimales.',
            'precio.min' => 'El precio debe ser mayor a 0.',
            'stock.min' => 'El stock no puede ser negativo.',
            'stock.max' => 'El stock no puede exceder 999,999 unidades.',
            'precio.max' => 'El precio no puede exceder $999,999.99.',
            'imagen.mimes' => 'La imagen debe ser formato: jpeg, jpg, png o webp.',
            'imagen.max' => 'La imagen no puede exceder 2MB.',
            'imagen.dimensions' => 'La imagen debe ser mínimo 100x100px y máximo 2000x2000px.',
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
        
        if ($this->has('nombre')) {
            $data['nombre'] = $this->sanitizeString($this->nombre);
        }
        
        if ($this->has('descripcion')) {
            $data['descripcion'] = $this->sanitizeString($this->descripcion);
        }
        
        if ($this->has('precio')) {
            $data['precio'] = $this->sanitizeDecimal($this->precio);
        }
        
        if ($this->has('stock')) {
            $data['stock'] = $this->sanitizeInteger($this->stock);
        }
        
        $this->merge($data);
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
     * Sanitizar decimal
     */
    private function sanitizeDecimal($value)
    {
        if ($value === null) return null;
        
        // Convertir a string, limpiar y convertir a float
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
        
        // Limpiar caracteres no numéricos
        $value = preg_replace('/[^0-9]/', '', (string) $value);
        
        return is_numeric($value) ? (int) $value : null;
    }
}
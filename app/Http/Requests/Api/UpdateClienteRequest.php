<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
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
        $clienteId = $this->route('cliente')?->id ?? null;

        return [
            'name' => [
                'sometimes',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            ],
            'email' => [
                'sometimes',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')->ignore($clienteId),
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'telefono' => [
                'nullable',
                'string',
                'regex:/^[\d\s\-\+\(\)]+$/',
                'min:7',
                'max:20',
            ],
            'direccion' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\-\#]+$/',
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            'estado' => [
                'sometimes',
                'string',
                Rule::in(['activo', 'inactivo', 'suspendido']),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'email.regex' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'email.dns' => 'El dominio del email no es válido.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, guiones y paréntesis.',
            'direccion.regex' => 'La dirección contiene caracteres no permitidos.',
            'password.regex' => 'La contraseña debe contener al menos: 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial.',
            'estado.in' => 'El estado debe ser: activo, inactivo o suspendido.',
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
        
        if ($this->has('name')) {
            $data['name'] = $this->sanitizeString($this->name);
        }
        
        if ($this->has('email')) {
            $data['email'] = $this->sanitizeEmail($this->email);
        }
        
        if ($this->has('telefono')) {
            $data['telefono'] = $this->sanitizePhone($this->telefono);
        }
        
        if ($this->has('direccion')) {
            $data['direccion'] = $this->sanitizeString($this->direccion);
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
     * Sanitizar email
     */
    private function sanitizeEmail(?string $email): ?string
    {
        if ($email === null) return null;
        return strtolower(trim(strip_tags($email)));
    }

    /**
     * Sanitizar teléfono
     */
    private function sanitizePhone(?string $phone): ?string
    {
        if ($phone === null) return null;
        return preg_replace('/[^0-9\s\-\+\(\)]/', '', trim($phone));
    }
}
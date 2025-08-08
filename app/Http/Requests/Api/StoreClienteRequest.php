<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\HasDataSanitization;

class StoreClienteRequest extends FormRequest
{
    use HasDataSanitization;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['Administrador', 'Secretario']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', // Solo letras y espacios
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', // Formato email estricto
            ],
            'telefono' => [
                'nullable',
                'string',
                'regex:/^[\d\s\-\+\(\)]+$/', // Solo números, espacios, guiones, paréntesis, +
                'min:7',
                'max:20',
            ],
            'direccion' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\-\#]+$/', // Letras, números, espacios, puntos, comas, guiones, #
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', // Contraseña fuerte
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
            $name = $this->sanitizeString($this->name);
            // Detectar intentos de injection
            if ($this->detectInjectionAttempt($name)) {
                $this->logInjectionAttempt($name, 'cliente_name');
                $name = '';
            }
            $data['name'] = $name;
        }
        
        if ($this->has('email')) {
            $data['email'] = $this->sanitizeEmail($this->email);
        }
        
        if ($this->has('telefono')) {
            $data['telefono'] = $this->sanitizePhone($this->telefono);
        }
        
        if ($this->has('direccion')) {
            $direccion = $this->sanitizeFreeText($this->direccion);
            if ($this->detectInjectionAttempt($direccion)) {
                $this->logInjectionAttempt($direccion, 'cliente_direccion');
                $direccion = '';
            }
            $data['direccion'] = $direccion;
        }
        
        $this->merge($data);
    }
}
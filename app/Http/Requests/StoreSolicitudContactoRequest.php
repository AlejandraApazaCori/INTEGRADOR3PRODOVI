<?php

namespace App\Http\Requests;

use App\Rules\ValidTurnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSolicitudContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email:rfc', 'max:190'],
            'telefono' => ['nullable', 'regex:/^[0-9]{7,15}$/'],
            'servicio' => [
                'required',
                Rule::in([
                    'publicidad',
                    'social',
                    'audiovisual',
                    'eventos',
                    'bodas',
                    'influencers',
                    'other',
                ]),
            ],
            'mensaje' => ['required', 'string', 'min:10', 'max:2000'],
            'cf-turnstile-response' => ['bail', 'required', 'string', 'max:2048', new ValidTurnstile],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Ingresa tu nombre completo.',
            'correo.required' => 'Ingresa tu correo electrónico.',
            'correo.email' => 'Ingresa un correo electrónico válido.',
            'telefono.regex' => 'El teléfono debe contener entre 7 y 15 números.',
            'servicio.required' => 'Selecciona un servicio de interés.',
            'servicio.in' => 'Selecciona un servicio válido.',
            'mensaje.required' => 'Cuéntanos brevemente sobre tu proyecto.',
            'mensaje.min' => 'El mensaje debe contener al menos 10 caracteres.',
            'mensaje.max' => 'El mensaje no puede superar los 2000 caracteres.',
            'cf-turnstile-response.required' => 'Completa la verificación de seguridad antes de enviar.',
            'cf-turnstile-response.max' => 'La verificación de seguridad no es válida.',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return url('/').'#contact';
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DetailingRequest extends FormRequest
{
    public const STATES = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

    private const PHONE = '/^\(?\d{2}\)?\s?9?\d{4}-?\d{4}$/';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'regex:'.self::PHONE],
            'whatsapp' => ['required', 'string', 'regex:'.self::PHONE],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'postal_code' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', Rule::in(self::STATES)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome da estética',
            'description' => 'descrição',
            'phone' => 'telefone',
            'whatsapp' => 'WhatsApp',
            'email' => 'e-mail comercial',
            'postal_code' => 'CEP',
            'street' => 'rua',
            'number' => 'número',
            'complement' => 'complemento',
            'neighborhood' => 'bairro',
            'city' => 'cidade',
            'state' => 'estado',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'state' => $this->filled('state') ? strtoupper($this->string('state')) : null,
        ]);
    }
}

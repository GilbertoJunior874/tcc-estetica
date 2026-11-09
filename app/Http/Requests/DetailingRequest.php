<?php

namespace App\Http\Requests;

use App\Rules\Cnpj;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DetailingRequest extends FormRequest
{
    public const STATES = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

    private const PHONE = '/^\(?\d{2}\)?\s?9?\d{4}-?\d{4}$/';

    public function rules(): array
    {
        return [
            'cnpj' => ['bail', 'required', new Cnpj, Rule::unique('automotive_detailings', 'cnpj')->ignore($this->isMethod('put') ? $this->user()->automotiveDetailing : null)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'regex:'.self::PHONE],
            'whatsapp' => ['required', 'string', 'regex:'.self::PHONE],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'postal_code' => ['required', 'string', 'regex:/^[0-9]{8}$/'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', Rule::in(self::STATES)],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome da estética',
            'cnpj' => 'CNPJ',
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
            'latitude' => 'latitude',
            'longitude' => 'longitude',
        ];
    }

    public function messages(): array
    {
        return ['cnpj.unique' => 'Este CNPJ já está cadastrado em outra estética.'];
    }

    protected function prepareForValidation(): void
    {
        foreach (['postal_code' => '/^(?:[0-9]{8}|[0-9]{5}-[0-9]{3})$/', 'cnpj' => '/^(?:[0-9]{14}|[0-9]{2}\.[0-9]{3}\.[0-9]{3}\/[0-9]{4}-[0-9]{2})$/'] as $field => $pattern) {
            $value = $this->input($field);

            if (is_string($value) && preg_match($pattern, $value)) {
                $this->merge([$field => preg_replace('/[^0-9]/', '', $value)]);
            }
        }

        $this->merge([
            'state' => $this->filled('state') ? strtoupper($this->string('state')) : null,
        ]);
    }
}

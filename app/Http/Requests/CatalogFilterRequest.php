<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CatalogFilterRequest extends FormRequest
{
    protected $redirectRoute = 'detailings.index';

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'servico' => ['nullable', 'string', 'max:100'],
            'preco_min' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'preco_max' => array_filter(['nullable', 'integer', 'min:0', 'max:100000', $this->filled('preco_min') ? 'gte:preco_min' : null]),
        ];
    }

    public function attributes(): array
    {
        return [
            'q' => 'busca',
            'cidade' => 'cidade',
            'servico' => 'serviço',
            'preco_min' => 'preço mínimo',
            'preco_max' => 'preço máximo',
        ];
    }

    public function messages(): array
    {
        return [
            'preco_max.gte' => 'O preço máximo deve ser maior ou igual ao preço mínimo.',
        ];
    }
}

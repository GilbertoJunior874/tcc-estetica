<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogFilterRequest extends FormRequest
{
    public const RADII = [2, 5, 10, 25, 50];

    protected $redirectRoute = 'detailings.index';

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'servico' => ['nullable', 'string', 'max:100'],
            'preco_min' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng,raio'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat,raio'],
            'raio' => ['nullable', 'integer', Rule::in(self::RADII)],
            'ordem' => ['nullable', Rule::in(['nome', 'preco', 'distancia'])],
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
            'lat' => 'latitude',
            'lng' => 'longitude',
            'raio' => 'raio',
            'ordem' => 'ordenação',
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required_with' => 'Ative sua localização para filtrar por distância.',
            'lng.required_with' => 'Ative sua localização para filtrar por distância.',
            'preco_max.gte' => 'O preço máximo deve ser maior ou igual ao preço mínimo.',
        ];
    }
}

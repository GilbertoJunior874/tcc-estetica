<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'string', 'regex:/^(\d{1,5}([.,]\d{1,2})?|\d{1,2}\.\d{3}(,\d{1,2})?)$/', 'not_regex:/^0+([.,]0+)?$/'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome do serviço',
            'description' => 'descrição',
            'price' => 'preço',
            'duration_minutes' => 'duração',
        ];
    }

    public function serviceData(): array
    {
        return [
            'name' => $this->validated('name'),
            'description' => $this->validated('description'),
            'price_cents' => $this->priceInCents(),
            'duration_minutes' => $this->validated('duration_minutes'),
            'active' => $this->boolean('active'),
        ];
    }

    private function priceInCents(): int
    {
        $price = $this->validated('price');

        $price = str_contains($price, ',') || preg_match('/^\d{1,2}\.\d{3}$/', $price)
            ? str_replace(['.', ','], ['', '.'], $price)
            : $price;

        [$reais, $cents] = array_pad(explode('.', $price), 2, '0');

        return (int) $reais * 100 + (int) str_pad($cents, 2, '0');
    }
}

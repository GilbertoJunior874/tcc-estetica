<?php

namespace App\Http\Requests;

use App\Models\BusinessHour;
use Illuminate\Foundation\Http\FormRequest;

class BusinessHoursRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = ['hours' => ['required', 'array']];

        foreach (array_keys(BusinessHour::DAYS) as $day) {
            $rules["hours.{$day}.closed"] = ['boolean'];
            $rules["hours.{$day}.opens_at"] = ["exclude_if:hours.{$day}.closed,1", 'required', 'date_format:H:i'];
            $rules["hours.{$day}.closes_at"] = ["exclude_if:hours.{$day}.closed,1", 'required', 'date_format:H:i', "after:hours.{$day}.opens_at"];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return collect(BusinessHour::DAYS)->flatMap(fn (string $name, int $day) => [
            "hours.{$day}.opens_at" => "abertura de {$name}",
            "hours.{$day}.closes_at" => "fechamento de {$name}",
        ])->all();
    }

    public function businessHours(): array
    {
        return collect(BusinessHour::DAYS)->keys()->map(function (int $day) {
            $closed = $this->boolean("hours.{$day}.closed");

            return [
                'day_of_week' => $day,
                'closed' => $closed,
                'opens_at' => $closed ? null : $this->input("hours.{$day}.opens_at"),
                'closes_at' => $closed ? null : $this->input("hours.{$day}.closes_at"),
            ];
        })->all();
    }
}

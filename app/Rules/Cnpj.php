<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^[0-9]{14}$/', $value)
            || preg_match('/^([0-9])\1{13}$/', $value)
            || $value !== substr($value, 0, 12).self::checkDigits(substr($value, 0, 12))) {
            $fail('Informe um CNPJ válido.');
        }
    }

    public static function checkDigits(string $base): string
    {
        $digits = '';

        foreach ([[5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2], [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]] as $weights) {
            $sum = 0;

            foreach ($weights as $index => $weight) {
                $sum += (int) ($base.$digits)[$index] * $weight;
            }

            $remainder = $sum % 11;
            $digits .= $remainder < 2 ? '0' : (string) (11 - $remainder);
        }

        return $digits;
    }
}

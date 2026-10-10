<?php

namespace App\Models;

use Database\Factories\BusinessHourFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessHour extends Model
{
    /** @use HasFactory<BusinessHourFactory> */
    use HasFactory;

    public const DAYS = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    protected $fillable = [
        'day_of_week',
        'opens_at',
        'closes_at',
        'closed',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'closed' => 'boolean',
        ];
    }

    public function automotiveDetailing(): BelongsTo
    {
        return $this->belongsTo(AutomotiveDetailing::class);
    }

    protected function dayName(): Attribute
    {
        return Attribute::get(fn () => self::DAYS[$this->day_of_week]);
    }

    protected function opensAt(): Attribute
    {
        return Attribute::get(fn (?string $value) => $value ? substr($value, 0, 5) : null);
    }

    protected function closesAt(): Attribute
    {
        return Attribute::get(fn (?string $value) => $value ? substr($value, 0, 5) : null);
    }
}

<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_cents',
        'duration_minutes',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'duration_minutes' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function automotiveDetailing(): BelongsTo
    {
        return $this->belongsTo(AutomotiveDetailing::class);
    }

    protected function durationLabel(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->duration_minutes) {
                return null;
            }

            $hours = intdiv($this->duration_minutes, 60);
            $minutes = $this->duration_minutes % 60;

            return match (true) {
                $hours === 0 => "{$minutes} min",
                $minutes === 0 => "{$hours}h",
                default => sprintf('%dh%02d', $hours, $minutes),
            };
        });
    }
}

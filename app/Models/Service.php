<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
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
}

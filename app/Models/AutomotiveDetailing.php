<?php

namespace App\Models;

use App\Enums\DetailingStatus;
use Database\Factories\AutomotiveDetailingFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomotiveDetailing extends Model
{
    /** @use HasFactory<AutomotiveDetailingFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'phone',
        'whatsapp',
        'email',
        'postal_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'latitude',
        'longitude',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => DetailingStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function activeServices(): HasMany
    {
        return $this->services()->where('active', true);
    }

    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('status', DetailingStatus::Approved);
    }

    #[Scope]
    protected function withCatalogSummary(Builder $query): void
    {
        $query->withCount('activeServices')
            ->withMin('activeServices', 'price_cents');
    }

    public function isApproved(): bool
    {
        return $this->status === DetailingStatus::Approved;
    }

    protected function streetAddress(): Attribute
    {
        return Attribute::get(fn () => collect([
            "{$this->street}, {$this->number}",
            $this->complement,
            $this->neighborhood,
        ])->filter()->implode(' – '));
    }

    protected function location(): Attribute
    {
        return Attribute::get(fn () => "{$this->neighborhood}, {$this->city} – {$this->state}");
    }

    protected function whatsappUrl(): Attribute
    {
        return Attribute::get(fn () => $this->whatsapp
            ? 'https://wa.me/55'.preg_replace('/\D/', '', $this->whatsapp)
            : null);
    }
}

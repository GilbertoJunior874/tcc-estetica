<?php

namespace App\Models;

use App\Enums\DetailingStatus;
use Database\Factories\AutomotiveDetailingFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

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
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status' => DetailingStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $detailing) {
            $detailing->slug ??= static::uniqueSlug($detailing->name);
        });
    }

    private static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'estetica';
        $slug = $base;

        for ($suffix = 2; static::where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class)->orderBy('day_of_week');
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

    public function transitionTo(DetailingStatus $status, User $reviewer, ?string $reason = null): void
    {
        if (! $this->status->canTransitionTo($status)) {
            throw new LogicException("Transição de {$this->status->value} para {$status->value} não permitida.");
        }

        $this->forceFill([
            'status' => $status,
            'rejection_reason' => $status === DetailingStatus::Rejected ? $reason : null,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
        ])->save();
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

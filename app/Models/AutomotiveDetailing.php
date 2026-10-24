<?php

namespace App\Models;

use App\Enums\DetailingStatus;
use Carbon\CarbonInterface;
use Database\Factories\AutomotiveDetailingFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;
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
    protected function search(Builder $query, ?string $term): void
    {
        if (blank($term)) {
            return;
        }

        $pattern = static::likePattern($term);

        $query->where(fn (Builder $query) => $query
            ->where('name', 'ilike', $pattern)
            ->orWhere('city', 'ilike', $pattern)
            ->orWhere('neighborhood', 'ilike', $pattern)
            ->orWhereHas('activeServices', fn (Builder $query) => $query->where('name', 'ilike', $pattern)));
    }

    #[Scope]
    protected function offering(Builder $query, ?string $service, ?int $minPriceCents, ?int $maxPriceCents): void
    {
        if (blank($service) && $minPriceCents === null && $maxPriceCents === null) {
            return;
        }

        $query->whereHas('activeServices', fn (Builder $query) => $query
            ->when(filled($service), fn (Builder $query) => $query->where('name', 'ilike', static::likePattern($service)))
            ->when($minPriceCents !== null, fn (Builder $query) => $query->where('price_cents', '>=', $minPriceCents))
            ->when($maxPriceCents !== null, fn (Builder $query) => $query->where('price_cents', '<=', $maxPriceCents)));
    }

    #[Scope]
    protected function nearTo(Builder $query, float $latitude, float $longitude, ?int $radiusKm = null): void
    {
        $distance = '6371 * acos(least(1.0, greatest(-1.0, cos(radians(?::double precision)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?::double precision)) + sin(radians(?::double precision)) * sin(radians(latitude)))))';
        $bindings = [$latitude, $longitude, $latitude];

        $query->select($query->getModel()->qualifyColumn('*'))
            ->selectRaw("{$distance} as distance_km", $bindings)
            ->when($radiusKm !== null, fn (Builder $query) => $query
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereRaw("{$distance} <= ?", [...$bindings, $radiusKm]));
    }

    private static function likePattern(string $term): string
    {
        return '%'.addcslashes(trim($term), '\\%_').'%';
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

    public function isOpenAt(CarbonInterface $moment): ?bool
    {
        if ($this->businessHours->isEmpty()) {
            return null;
        }

        $hours = $this->businessHours->firstWhere('day_of_week', $moment->dayOfWeekIso);

        if (! $hours || $hours->closed || ! $hours->opens_at || ! $hours->closes_at) {
            return false;
        }

        $time = $moment->format('H:i');

        return $time >= $hours->opens_at && $time < $hours->closes_at;
    }

    protected function distanceLabel(): Attribute
    {
        return Attribute::get(function () {
            $distance = $this->attributes['distance_km'] ?? null;

            if ($distance === null) {
                return null;
            }

            $distance = (float) $distance;

            return $distance < 1
                ? max(10, (int) round($distance * 1000 / 10) * 10).' m'
                : Number::format($distance, precision: 1, locale: 'pt_BR').' km';
        });
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

    protected function whatsappNumber(): Attribute
    {
        return Attribute::get(function () {
            $digits = preg_replace('/\D/', '', (string) $this->whatsapp);

            return match (true) {
                in_array(strlen($digits), [10, 11], true) => "55{$digits}",
                str_starts_with($digits, '55') && in_array(strlen($digits), [12, 13], true) => $digits,
                default => null,
            };
        });
    }

    protected function whatsappUrl(): Attribute
    {
        return Attribute::get(fn () => $this->whatsappUrlFor(new Collection));
    }

    public function whatsappUrlFor(Collection $services): ?string
    {
        if (! $this->whatsapp_number) {
            return null;
        }

        return "https://wa.me/{$this->whatsapp_number}?".http_build_query(
            ['text' => $this->whatsappMessage($services)],
            encoding_type: PHP_QUERY_RFC3986,
        );
    }

    public function whatsappMessage(Collection $services): string
    {
        $greeting = 'Olá! Encontrei vocês pelo '.config('app.name');

        if ($services->isEmpty()) {
            return "{$greeting} e gostaria de mais informações sobre os serviços.";
        }

        $lines = $services->map(fn (Service $service) => "- {$service->name}: ".static::money($service->price_cents));

        return implode("\n", [
            "{$greeting} e gostaria de saber mais sobre os seguintes serviços:",
            '',
            ...$lines,
            '',
            'Total estimado: '.static::money($services->sum('price_cents')),
            '',
            'Podem confirmar valores e disponibilidade?',
        ]);
    }

    private static function money(int $cents): string
    {
        return str_replace("\u{00A0}", ' ', Number::currency($cents / 100, in: 'BRL', locale: 'pt_BR'));
    }
}

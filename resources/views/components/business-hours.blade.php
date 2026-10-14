@props(['hours'])

<dl {{ $attributes->class('space-y-1 text-sm') }}>
    @foreach ($hours as $hour)
        <div class="flex justify-between gap-4">
            <dt class="text-zinc-600">{{ $hour->day_name }}</dt>
            <dd @class(['text-zinc-500' => $hour->closed])>{{ $hour->closed ? 'Fechado' : "{$hour->opens_at} – {$hour->closes_at}" }}</dd>
        </div>
    @endforeach
</dl>

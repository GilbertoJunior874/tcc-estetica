@props(['detailing'])

<article class="flex h-full flex-col rounded-lg border border-zinc-200 bg-white p-5 transition-shadow hover:shadow-md">
    <div class="flex-1">
        <h3 class="text-lg font-semibold leading-snug">
            {{ $detailing->name }}
        </h3>
        <p class="mt-1 text-sm text-zinc-500">{{ $detailing->location }}</p>

        @if ($detailing->description)
            <p class="mt-3 line-clamp-2 text-sm text-zinc-600">{{ $detailing->description }}</p>
        @endif

        @if ($detailing->activeServices->isNotEmpty())
            <ul class="mt-4 flex flex-wrap gap-1.5">
                @foreach ($detailing->activeServices->take(3) as $service)
                    <li class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs text-zinc-700">{{ $service->name }}</li>
                @endforeach
                @if ($detailing->active_services_count > 3)
                    <li class="rounded-full px-1 py-1 text-xs text-zinc-500">+{{ $detailing->active_services_count - 3 }}</li>
                @endif
            </ul>
        @endif
    </div>

    <div class="mt-5 flex items-end justify-between gap-3 border-t border-zinc-100 pt-4">
        <div class="text-sm">
            @if ($detailing->active_services_min_price_cents !== null)
                <span class="block text-xs text-zinc-500">A partir de</span>
                <x-money :cents="$detailing->active_services_min_price_cents" class="font-semibold text-zinc-900" />
            @else
                <span class="text-zinc-500">Sem serviços cadastrados</span>
            @endif
        </div>
    </div>
</article>

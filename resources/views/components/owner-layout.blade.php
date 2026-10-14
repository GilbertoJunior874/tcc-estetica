@props(['title'])

@php
    $links = [['owner.dashboard', 'owner.dashboard', 'Visão geral']];

    if (auth()->user()->automotiveDetailing) {
        $links[] = ['owner.detailing.edit', 'owner.detailing.edit', 'Dados da estética'];
        $links[] = ['owner.hours.edit', 'owner.hours.*', 'Horários'];
        $links[] = ['owner.services.index', 'owner.services.*', 'Serviços'];
    }
@endphp

<x-layout :title="$title">
    <div class="border-b border-zinc-200 bg-white">
        <nav class="mx-auto flex max-w-6xl items-center gap-1 overflow-x-auto px-4 text-sm font-medium sm:px-6">
            @foreach ($links as [$route, $pattern, $label])
                <a href="{{ route($route) }}" @class([
                    'shrink-0 border-b-2 px-3 py-3 transition-colors',
                    'border-brand-700 text-brand-700' => request()->routeIs($pattern),
                    'border-transparent text-zinc-600 hover:text-zinc-900' => ! request()->routeIs($pattern),
                ])>{{ $label }}</a>
            @endforeach
        </nav>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10">
        @if (session('status'))
            <p role="status" class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        {{ $slot }}
    </div>
</x-layout>

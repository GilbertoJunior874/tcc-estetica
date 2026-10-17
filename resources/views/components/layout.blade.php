@props(['title' => null, 'description' => 'Encontre estéticas automotivas, compare serviços e preços e fale direto com o estabelecimento.'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $description }}">

        <title>{{ $title ? "{$title} · ".config('app.name') : config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-zinc-50 font-sans text-zinc-900 antialiased">
        <header class="border-b border-zinc-200 bg-white">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold tracking-tight">
                    <span class="grid size-8 place-items-center rounded-md bg-brand-700 text-sm font-bold text-white">EP</span>
                    <span class="hidden min-[400px]:inline">{{ config('app.name') }}</span>
                </a>

                <nav class="flex items-center gap-1 text-sm font-medium">
                    <a href="{{ route('detailings.index') }}" @class([
                        'rounded-md px-2 py-2 transition-colors sm:px-3',
                        'bg-brand-50 text-brand-700' => request()->routeIs('detailings.*'),
                        'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' => ! request()->routeIs('detailings.*'),
                    ])>Estéticas</a>

                    @auth
                        @can('access-admin')
                            <a href="{{ route('admin.dashboard') }}" @class([
                                'rounded-md px-2 py-2 transition-colors sm:px-3',
                                'bg-brand-50 text-brand-700' => request()->routeIs('admin.*'),
                                'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' => ! request()->routeIs('admin.*'),
                            ])>Admin</a>
                        @else
                            <a href="{{ route('owner.dashboard') }}" @class([
                                'rounded-md px-2 py-2 transition-colors sm:px-3',
                                'bg-brand-50 text-brand-700' => request()->routeIs('owner.*'),
                                'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' => ! request()->routeIs('owner.*'),
                            ])>Painel</a>
                        @endcan
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-md px-2 py-2 text-zinc-600 sm:px-3 transition-colors hover:bg-zinc-100 hover:text-zinc-900">Sair</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md px-2 py-2 text-zinc-600 sm:px-3 transition-colors hover:bg-zinc-100 hover:text-zinc-900">Para estéticas</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-8 text-sm text-zinc-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p>&copy; {{ now()->year }} {{ config('app.name') }}</p>
                <p>Os serviços e preços são informados por cada estética.</p>
            </div>
        </footer>
    </body>
</html>

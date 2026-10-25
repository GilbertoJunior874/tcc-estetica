@props(['code', 'title', 'message'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">

        <title>{{ $title }} · {{ config('app.name') }}</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="flex min-h-screen flex-col bg-zinc-50 font-sans text-zinc-900 antialiased">
        <main class="mx-auto flex w-full max-w-lg flex-1 flex-col justify-center px-4 py-16">
            <p class="text-sm font-semibold text-brand-700">Erro {{ $code }}</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h1>
            <p class="mt-3 text-zinc-600">{{ $message }}</p>
            <div class="mt-8 flex flex-wrap gap-2 text-sm font-medium">
                <a href="{{ route('home') }}" class="rounded-md bg-brand-700 px-4 py-2 text-white transition-colors hover:bg-brand-900">Ir para a página inicial</a>
                <a href="{{ route('detailings.index') }}" class="rounded-md border border-zinc-300 bg-white px-4 py-2 text-zinc-700 hover:bg-zinc-50">Ver estéticas</a>
            </div>
        </main>
    </body>
</html>

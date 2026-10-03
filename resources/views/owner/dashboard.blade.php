<x-owner-layout title="Painel">
    <h1 class="text-2xl font-bold tracking-tight">Olá, {{ auth()->user()->name }}</h1>

    @unless ($detailing)
        <div class="mt-6 rounded-lg border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">
            Você ainda não cadastrou sua estética.
        </div>
    @endunless
</x-owner-layout>

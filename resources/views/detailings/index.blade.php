<x-layout title="Estéticas automotivas">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <header>
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Estéticas automotivas</h1>
            <p class="mt-2 text-sm text-zinc-600">{{ trans_choice('{0} Nenhuma estética encontrada.|{1} 1 estética encontrada.|[2,*] :count estéticas encontradas.', $detailings->total()) }}</p>
        </header>

        @if ($detailings->isEmpty())
            <div class="mt-8 rounded-lg border border-dashed border-zinc-300 bg-white p-10 text-center text-sm text-zinc-600">
                Ainda não há estéticas disponíveis. Volte em breve.
            </div>
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($detailings as $detailing)
                    <x-detailing-card :detailing="$detailing" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $detailings->links() }}
            </div>
        @endif
    </div>
</x-layout>

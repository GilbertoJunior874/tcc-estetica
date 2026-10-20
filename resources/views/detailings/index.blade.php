@php
    $hasFilters = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();
    $inputClass = 'mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100';
@endphp

<x-layout title="Estéticas automotivas">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <header>
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Estéticas automotivas</h1>
            <p class="mt-2 text-sm text-zinc-600" role="status">{{ trans_choice('{0} Nenhuma estética encontrada.|{1} 1 estética encontrada.|[2,*] :count estéticas encontradas.', $detailings->total()) }}</p>
        </header>

        <form method="get" action="{{ route('detailings.index') }}" role="search" aria-label="Buscar estéticas" class="mt-6 rounded-lg border border-zinc-200 bg-white p-4 sm:p-5">
            @if ($errors->any())
                <div role="alert" class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="q" class="block text-sm font-medium text-zinc-800">Buscar</label>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Nome, bairro, cidade ou serviço" class="{{ $inputClass }}">
                </div>
                <div class="lg:col-span-3">
                    <label for="cidade" class="block text-sm font-medium text-zinc-800">Cidade</label>
                    <select id="cidade" name="cidade" class="{{ $inputClass }}">
                        <option value="">Todas as cidades</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city }}" @selected(($filters['cidade'] ?? null) === $city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="servico" class="block text-sm font-medium text-zinc-800">Serviço</label>
                    <input id="servico" name="servico" type="text" value="{{ $filters['servico'] ?? '' }}" maxlength="100" placeholder="Ex.: polimento" class="{{ $inputClass }}">
                </div>
                <div class="lg:col-span-2">
                    <label for="preco_min" class="block text-sm font-medium text-zinc-800">Preço mínimo (R$)</label>
                    <input id="preco_min" name="preco_min" type="number" min="0" max="100000" step="1" inputmode="numeric" value="{{ $filters['preco_min'] ?? '' }}" class="{{ $inputClass }}">
                </div>
                <div class="lg:col-span-2">
                    <label for="preco_max" class="block text-sm font-medium text-zinc-800">Preço máximo (R$)</label>
                    <input id="preco_max" name="preco_max" type="number" min="0" max="100000" step="1" inputmode="numeric" value="{{ $filters['preco_max'] ?? '' }}" class="{{ $inputClass }}">
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand-900">Buscar</button>
                @if ($hasFilters)
                    <a href="{{ route('detailings.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900">Limpar filtros</a>
                @endif
            </div>
        </form>

        @if ($detailings->isEmpty())
            <div class="mt-8 rounded-lg border border-dashed border-zinc-300 bg-white p-10 text-center text-sm text-zinc-600">
                @if ($hasFilters)
                    Nenhuma estética corresponde aos filtros informados. Tente ampliar a busca.
                @else
                    Ainda não há estéticas disponíveis. Volte em breve.
                @endif
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

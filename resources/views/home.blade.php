<x-layout>
    <section class="border-b border-zinc-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                    Encontre uma estética automotiva perto de você
                </h1>
                <p class="mt-4 text-base text-zinc-600 sm:text-lg">
                    Veja os serviços, o que está incluído e quanto custa antes de entrar em contato. Sem cadastro.
                </p>

                <form action="{{ route('detailings.index') }}" method="get" class="mt-8 flex flex-col gap-2 sm:flex-row">
                    <label for="location" class="sr-only">Bairro ou cidade</label>
                    <input
                        id="location"
                        type="text"
                        disabled
                        placeholder="Busca por bairro ou cidade em breve"
                        class="w-full cursor-not-allowed rounded-md border border-zinc-300 bg-zinc-50 px-4 py-3 text-sm placeholder:text-zinc-400"
                    >
                    <button type="submit" class="shrink-0 rounded-md bg-brand-700 px-5 py-3 text-sm font-medium text-white transition-colors hover:bg-brand-900">
                        Ver todas as estéticas
                    </button>
                </form>
                <p class="mt-2 text-xs text-zinc-500">{{ trans_choice('{0} Nenhuma estética disponível ainda.|{1} 1 estética disponível.|[2,*] :count estéticas disponíveis.', $totalApproved) }}</p>
            </div>
        </div>
    </section>

    @if ($featured->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <div class="flex items-end justify-between gap-4">
                <h2 class="text-xl font-semibold tracking-tight">Algumas estéticas</h2>
                <a href="{{ route('detailings.index') }}" class="text-sm font-medium text-brand-700 hover:text-brand-900">Ver todas</a>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $detailing)
                    <x-detailing-card :detailing="$detailing" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="border-t border-zinc-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <h2 class="text-xl font-semibold tracking-tight">Como funciona</h2>

            <ol class="mt-6 grid gap-6 sm:grid-cols-3">
                @foreach ([
                    ['Escolha a estética', 'Veja as estéticas disponíveis, onde ficam e a partir de quanto cobram.'],
                    ['Compare os serviços', 'Cada serviço mostra o preço, o tempo estimado e o que está incluído.'],
                    ['Fale direto com ela', 'O contato é feito com a própria estética, pelo WhatsApp ou telefone.'],
                ] as [$step, $text])
                    <li class="flex gap-4">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-50 text-sm font-semibold text-brand-700">{{ $loop->iteration }}</span>
                        <div>
                            <h3 class="font-medium">{{ $step }}</h3>
                            <p class="mt-1 text-sm text-zinc-600">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
</x-layout>

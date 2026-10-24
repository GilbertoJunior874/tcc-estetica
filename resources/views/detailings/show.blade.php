<x-layout :title="$detailing->name" :description="Str::limit($detailing->description, 155)">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10">
        <nav class="text-sm text-zinc-500">
            <a href="{{ route('detailings.index') }}" class="hover:text-zinc-900">Estéticas</a>
            <span class="mx-1">/</span>
            <span class="text-zinc-700">{{ $detailing->name }}</span>
        </nav>

        <div class="mt-4 grid gap-8 lg:grid-cols-[1fr_20rem]">
            <div class="min-w-0">
                <header>
                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $detailing->name }}</h1>
                    <p class="mt-1 text-sm text-zinc-500">{{ $detailing->location }}</p>
                    @if ($detailing->description)
                        <p class="mt-4 max-w-2xl text-zinc-700">{{ $detailing->description }}</p>
                    @endif
                </header>

                <section class="mt-10">
                    <h2 class="text-lg font-semibold">Serviços</h2>

                    @if ($detailing->activeServices->isEmpty())
                        <p class="mt-4 rounded-lg border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">
                            Esta estética ainda não cadastrou serviços.
                        </p>
                    @else
                        <p class="mt-1 text-sm text-zinc-600">Selecione os serviços de interesse para calcular o valor estimado e enviar a lista pelo WhatsApp.</p>

                        <form id="service-selection" method="get" action="{{ route('detailings.whatsapp', $detailing) }}" target="_blank" rel="noopener noreferrer" data-service-selection>
                            <fieldset>
                                <legend class="sr-only">Serviços oferecidos</legend>
                                <ul class="mt-4 divide-y divide-zinc-200 rounded-lg border border-zinc-200 bg-white">
                                    @foreach ($detailing->activeServices as $service)
                                        <li>
                                            <label class="flex cursor-pointer gap-3 p-4 transition-colors hover:bg-zinc-50 has-[:checked]:bg-brand-50 sm:p-5">
                                                <input type="checkbox" name="servicos[]" value="{{ $service->id }}" data-name="{{ $service->name }}" data-price="{{ $service->price_cents }}" class="mt-1 size-4 shrink-0 rounded border-zinc-300 text-brand-700 focus:ring-2 focus:ring-brand-600">
                                                <span class="min-w-0 flex-1">
                                                    <span class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                                                        <span class="font-medium">{{ $service->name }}</span>
                                                        <x-money :cents="$service->price_cents" class="shrink-0 font-semibold text-zinc-900" />
                                                    </span>
                                                    @if ($service->description)
                                                        <span class="mt-2 block text-sm text-zinc-600">{{ $service->description }}</span>
                                                    @endif
                                                    @if ($service->duration_label)
                                                        <span class="mt-2 block text-xs text-zinc-500">Tempo estimado: {{ $service->duration_label }}</span>
                                                    @endif
                                                </span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </fieldset>
                        </form>
                    @endif
                </section>
            </div>

            <aside class="lg:sticky lg:top-6 lg:self-start">
                <div class="rounded-lg border border-zinc-200 bg-white p-5">
                    <h2 class="font-semibold">Contato e endereço</h2>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="text-zinc-500">Endereço</dt>
                            <dd class="mt-0.5">{{ $detailing->street_address }}</dd>
                            <dd>{{ $detailing->city }} – {{ $detailing->state }}, {{ $detailing->postal_code }}</dd>
                        </div>
                        @if ($detailing->phone)
                            <div>
                                <dt class="text-zinc-500">Telefone</dt>
                                <dd class="mt-0.5"><a href="tel:+55{{ preg_replace('/\D/', '', $detailing->phone) }}" class="hover:text-brand-700">{{ $detailing->phone }}</a></dd>
                            </div>
                        @endif
                        @if ($detailing->email)
                            <div>
                                <dt class="text-zinc-500">E-mail</dt>
                                <dd class="mt-0.5 break-all"><a href="mailto:{{ $detailing->email }}" class="hover:text-brand-700">{{ $detailing->email }}</a></dd>
                            </div>
                        @endif
                    </dl>

                    @if ($detailing->businessHours->isNotEmpty())
                        <div class="mt-5 border-t border-zinc-100 pt-4">
                            <h3 class="text-sm text-zinc-500">Horário de funcionamento</h3>
                            @php($openNow = $detailing->isOpenAt(now()))
                            <p @class(['mt-1 text-sm font-medium', 'text-emerald-700' => $openNow, 'text-zinc-600' => ! $openNow])>{{ $openNow ? 'Aberta agora' : 'Fechada agora' }}</p>
                            <x-business-hours :hours="$detailing->businessHours" class="mt-2" />
                        </div>
                    @endif

                    @if ($detailing->whatsapp_url)
                        @if ($detailing->activeServices->isNotEmpty())
                            <div class="mt-5 border-t border-zinc-100 pt-4" aria-live="polite">
                                <h3 class="text-sm text-zinc-500">Serviços selecionados</h3>
                                <p data-selection-empty class="mt-2 text-sm text-zinc-600">Nenhum serviço selecionado.</p>
                                <div data-selection-summary hidden>
                                    <ul data-selection-list class="mt-2 space-y-1 text-sm"></ul>
                                    <p class="mt-3 flex items-baseline justify-between gap-4 border-t border-zinc-100 pt-3 text-sm">
                                        <span class="font-medium">Total estimado</span>
                                        <span data-selection-total class="font-semibold"></span>
                                    </p>
                                </div>
                            </div>

                            <button type="submit" form="service-selection" class="mt-5 flex w-full items-center justify-center rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-emerald-700">
                                Conversar no WhatsApp
                            </button>
                        @else
                            <a href="{{ $detailing->whatsapp_url }}" target="_blank" rel="noopener noreferrer" class="mt-5 flex w-full items-center justify-center rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-emerald-700">
                                Conversar no WhatsApp
                            </a>
                        @endif
                        <p class="mt-2 text-center text-xs text-zinc-500">{{ $detailing->whatsapp }}</p>
                        <p class="mt-3 text-xs text-zinc-500">O valor é uma estimativa com base nos preços informados pela estética. Confirme valores e disponibilidade diretamente com o estabelecimento.</p>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-layout>

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
                        <ul class="mt-4 divide-y divide-zinc-200 rounded-lg border border-zinc-200 bg-white">
                            @foreach ($detailing->activeServices as $service)
                                <li class="p-4 sm:p-5">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                                        <h3 class="font-medium">{{ $service->name }}</h3>
                                        <x-money :cents="$service->price_cents" class="shrink-0 font-semibold text-zinc-900" />
                                    </div>
                                    @if ($service->description)
                                        <p class="mt-2 text-sm text-zinc-600">{{ $service->description }}</p>
                                    @endif
                                    @if ($service->duration_label)
                                        <p class="mt-2 text-xs text-zinc-500">Tempo estimado: {{ $service->duration_label }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
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

                    @if ($detailing->whatsapp_url)
                        <a href="{{ $detailing->whatsapp_url }}" target="_blank" rel="noopener" class="mt-5 flex w-full items-center justify-center rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-emerald-700">
                            Conversar no WhatsApp
                        </a>
                        <p class="mt-2 text-center text-xs text-zinc-500">{{ $detailing->whatsapp }}</p>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-layout>

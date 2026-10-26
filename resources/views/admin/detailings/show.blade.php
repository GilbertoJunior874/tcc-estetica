@php($status = $detailing->status)

<x-admin-layout :title="$detailing->name">
    <nav aria-label="Trilha" class="text-sm text-zinc-500">
        <a href="{{ route('admin.detailings.index') }}" class="hover:text-zinc-900">Estéticas</a>
        <span class="mx-1" aria-hidden="true">/</span>
        <span class="text-zinc-700">{{ $detailing->name }}</span>
    </nav>

    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold tracking-tight">{{ $detailing->name }}</h1>
        <x-status-badge :status="$status" class="self-start sm:self-auto" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem]">
        <div class="min-w-0 space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5">
                <h2 class="font-semibold">Empresa</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-zinc-500">Descrição</dt>
                        <dd class="mt-0.5 whitespace-pre-line">{{ $detailing->description ?: 'Não informada.' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Responsável</dt>
                        @if ($detailing->owner)
                            <dd class="mt-0.5">{{ $detailing->owner->name }}</dd>
                            <dd class="break-all text-zinc-600">{{ $detailing->owner->email }}</dd>
                        @else
                            <dd class="mt-0.5 text-zinc-600">Sem responsável vinculado</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-zinc-500">Solicitação enviada em</dt>
                        <dd class="mt-0.5 tabular-nums">{{ $detailing->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5">
                <h2 class="font-semibold">Contato e endereço</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-zinc-500">WhatsApp</dt>
                        <dd class="mt-0.5">{{ $detailing->whatsapp ?: 'Não informado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Telefone</dt>
                        <dd class="mt-0.5">{{ $detailing->phone ?: 'Não informado' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-zinc-500">E-mail comercial</dt>
                        <dd class="mt-0.5 break-all">{{ $detailing->email ?: 'Não informado' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-zinc-500">Endereço</dt>
                        <dd class="mt-0.5">{{ $detailing->street_address }}</dd>
                        <dd>{{ $detailing->city }} – {{ $detailing->state }}, {{ $detailing->postal_code }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5">
                <h2 class="font-semibold">Horários de funcionamento</h2>
                @if ($detailing->businessHours->isEmpty())
                    <p class="mt-3 text-sm text-zinc-600">Os horários ainda não foram informados.</p>
                @else
                    <x-business-hours :hours="$detailing->businessHours" class="mt-4 max-w-sm" />
                @endif
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5">
                <h2 class="font-semibold">Serviços</h2>
                @if ($detailing->services->isEmpty())
                    <p class="mt-3 text-sm text-zinc-600">Nenhum serviço cadastrado.</p>
                @else
                    <ul class="mt-4 divide-y divide-zinc-100 text-sm">
                        @foreach ($detailing->services as $service)
                            <li class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                                <div class="min-w-0">
                                    <p class="font-medium">
                                        {{ $service->name }}
                                        @unless ($service->active)
                                            <span class="ml-1 rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-normal text-zinc-600">Inativo</span>
                                        @endunless
                                    </p>
                                    @if ($service->description)
                                        <p class="mt-1 text-zinc-600">{{ $service->description }}</p>
                                    @endif
                                </div>
                                <x-money :cents="$service->price_cents" class="shrink-0 font-semibold" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <aside class="space-y-6 lg:sticky lg:top-6 lg:self-start">
            <section class="rounded-lg border border-zinc-200 bg-white p-5">
                <h2 class="font-semibold">Situação</h2>
                <p class="mt-2 text-sm text-zinc-600">{{ $status->label() }}</p>

                @if ($status === \App\Enums\DetailingStatus::Rejected && $detailing->rejection_reason)
                    <p class="mt-3 rounded-md bg-red-50 p-3 text-sm text-red-900"><span class="font-medium">Motivo:</span> {{ $detailing->rejection_reason }}</p>
                @endif

                @if ($detailing->reviewed_at)
                    <p class="mt-3 text-xs text-zinc-500">
                        Última análise em {{ $detailing->reviewed_at->format('d/m/Y H:i') }}@if ($detailing->reviewer) por {{ $detailing->reviewer->name }}@endif.
                    </p>
                @endif

                @if ($status->canTransitionTo(\App\Enums\DetailingStatus::Approved))
                    <form method="post" action="{{ route($status === \App\Enums\DetailingStatus::Inactive ? 'admin.detailings.reactivate' : 'admin.detailings.approve', $detailing) }}" class="mt-5">
                        @csrf
                        @method('patch')
                        <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-emerald-700">
                            {{ $status === \App\Enums\DetailingStatus::Inactive ? 'Reativar estética' : 'Aprovar e publicar' }}
                        </button>
                    </form>
                @endif

                @if ($status->canTransitionTo(\App\Enums\DetailingStatus::Inactive))
                    <form method="post" action="{{ route('admin.detailings.deactivate', $detailing) }}" class="mt-5" onsubmit="return confirm('Desativar esta estética? Ela deixará de aparecer no catálogo.')">
                        @csrf
                        @method('patch')
                        <button type="submit" class="w-full rounded-md border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-800 transition-colors hover:bg-zinc-50">Desativar estética</button>
                    </form>
                @endif

                @if ($status->canTransitionTo(\App\Enums\DetailingStatus::Rejected))
                    <form method="post" action="{{ route('admin.detailings.reject', $detailing) }}" class="mt-5 border-t border-zinc-100 pt-5">
                        @csrf
                        @method('patch')
                        <x-field name="rejection_reason" label="Motivo da recusa" type="textarea" maxlength="1000" required hint="O responsável verá este texto no painel. Seja específico sobre o que precisa ser corrigido." />
                        <button type="submit" class="mt-3 w-full rounded-md border border-red-300 px-4 py-2.5 text-sm font-medium text-red-700 transition-colors hover:bg-red-50">Recusar solicitação</button>
                    </form>
                @endif
            </section>
        </aside>
    </div>
</x-admin-layout>

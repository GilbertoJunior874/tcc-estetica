<x-owner-layout title="Painel">
    @if ($detailing)
        <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
            <h1 class="text-2xl font-bold tracking-tight">{{ $detailing->name }}</h1>
            <p class="text-sm text-zinc-500">{{ $detailing->location }}</p>
        </div>

        <section @class([
            'mt-6 rounded-lg border p-5',
            'border-amber-200 bg-amber-50 text-amber-900' => $detailing->status === \App\Enums\DetailingStatus::Pending,
            'border-emerald-200 bg-emerald-50 text-emerald-900' => $detailing->status === \App\Enums\DetailingStatus::Approved,
            'border-red-200 bg-red-50 text-red-900' => $detailing->status === \App\Enums\DetailingStatus::Rejected,
            'border-zinc-300 bg-zinc-100 text-zinc-800' => $detailing->status === \App\Enums\DetailingStatus::Inactive,
        ])>
            <h2 class="text-sm font-semibold">Situação: {{ $detailing->status->label() }}</h2>
            <p class="mt-1 text-sm">{{ $detailing->status->ownerMessage() }}</p>
            @if ($detailing->status === \App\Enums\DetailingStatus::Rejected && $detailing->rejection_reason)
                <p class="mt-3 text-sm"><span class="font-medium">Motivo:</span> {{ $detailing->rejection_reason }}</p>
            @endif
            @if ($detailing->isApproved())
                <a href="{{ route('detailings.show', $detailing) }}" class="mt-3 inline-block text-sm font-medium underline underline-offset-2">Ver página pública</a>
            @endif
        </section>

        <section class="mt-6 rounded-lg border border-zinc-200 bg-white p-5">
            <h2 class="font-semibold">Dados enviados</h2>
            <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-zinc-500">Endereço</dt>
                    <dd class="mt-0.5">{{ $detailing->street_address }}</dd>
                    <dd>{{ $detailing->city }} – {{ $detailing->state }}, {{ $detailing->postal_code }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Contato</dt>
                    <dd class="mt-0.5">WhatsApp {{ $detailing->whatsapp }}</dd>
                    @if ($detailing->phone)
                        <dd>Telefone {{ $detailing->phone }}</dd>
                    @endif
                    @if ($detailing->email)
                        <dd class="break-all">{{ $detailing->email }}</dd>
                    @endif
                </div>
            </dl>
        </section>
    @else
        <h1 class="text-2xl font-bold tracking-tight">Olá, {{ auth()->user()->name }}</h1>

        <div class="mt-6 rounded-lg border border-dashed border-zinc-300 bg-white p-6">
            <h2 class="font-semibold">Cadastre sua estética</h2>
            <p class="mt-1 text-sm text-zinc-600">Informe os dados da empresa, o contato e o endereço. Depois da análise, ela passa a aparecer no catálogo.</p>
            <a href="{{ route('owner.detailing.create') }}" class="mt-4 inline-block rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand-900">Iniciar cadastro</a>
        </div>
    @endif
</x-owner-layout>

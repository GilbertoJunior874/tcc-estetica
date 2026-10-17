<x-admin-layout title="Administração">
    <h1 class="text-2xl font-bold tracking-tight">Administração</h1>
    <p class="mt-2 text-sm text-zinc-600">Acompanhe as solicitações de cadastro e a situação das estéticas da plataforma.</p>

    <ul class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach (\App\Enums\DetailingStatus::cases() as $status)
            <li>
                <a href="{{ route('admin.detailings.index', ['status' => $status->value]) }}" class="block rounded-lg border border-zinc-200 bg-white p-4 transition-shadow hover:shadow-md">
                    <span class="block text-sm text-zinc-500">{{ $status->label() }}</span>
                    <span class="mt-1 block text-2xl font-bold tabular-nums">{{ $counts[$status->value] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <section class="mt-10">
        <div class="flex items-baseline justify-between gap-4">
            <h2 class="text-lg font-semibold">Solicitações pendentes</h2>
            @if ($counts['pending'] > $pending->count())
                <a href="{{ route('admin.detailings.index', ['status' => 'pending']) }}" class="text-sm font-medium text-brand-700 hover:text-brand-900">Ver todas</a>
            @endif
        </div>

        @if ($pending->isEmpty())
            <p class="mt-4 rounded-lg border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">Nenhuma solicitação aguardando análise.</p>
        @else
            <ul class="mt-4 divide-y divide-zinc-200 rounded-lg border border-zinc-200 bg-white">
                @foreach ($pending as $detailing)
                    <li class="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                        <div class="min-w-0">
                            <p class="font-medium">{{ $detailing->name }}</p>
                            <p class="mt-0.5 text-sm text-zinc-500">
                                {{ $detailing->city }} – {{ $detailing->state }}
                                <span class="text-zinc-400">·</span>
                                enviada em {{ $detailing->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <a href="{{ route('admin.detailings.show', $detailing) }}" class="shrink-0 rounded-md bg-brand-700 px-3.5 py-2 text-center text-sm font-medium text-white transition-colors hover:bg-brand-900">Analisar</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-admin-layout>

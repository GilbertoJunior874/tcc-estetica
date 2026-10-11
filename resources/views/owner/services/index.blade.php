<x-owner-layout title="Serviços">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Serviços</h1>
            <p class="mt-2 text-sm text-zinc-600">Somente serviços ativos aparecem na página pública da estética.</p>
        </div>

        @can('create', \App\Models\Service::class)
            <a href="{{ route('owner.services.create') }}" class="shrink-0 rounded-md bg-brand-700 px-4 py-2 text-center text-sm font-medium text-white transition-colors hover:bg-brand-900">
                Novo serviço
            </a>
        @endcan
    </div>

    @if ($services->isEmpty())
        <p class="mt-8 rounded-lg border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">
            Nenhum serviço cadastrado ainda.
        </p>
    @else
        @php($canManage = auth()->user()->can('update', $detailing))

        <ul class="mt-8 divide-y divide-zinc-200 rounded-lg border border-zinc-200 bg-white">
            @foreach ($services as $service)
                <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-6 sm:p-5">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 @class(['font-medium', 'text-zinc-500' => ! $service->active])>{{ $service->name }}</h2>
                            @unless ($service->active)
                                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600">Inativo</span>
                            @endunless
                        </div>
                        <p class="mt-1 text-sm text-zinc-600">
                            <x-money :cents="$service->price_cents" class="font-semibold text-zinc-900" />
                            @if ($service->duration_label)
                                <span class="text-zinc-400">·</span> {{ $service->duration_label }}
                            @endif
                        </p>
                    </div>

                    @if ($canManage)
                        <div class="flex shrink-0 flex-wrap items-center gap-2 text-sm">
                            <a href="{{ route('owner.services.edit', $service) }}" class="rounded-md border border-zinc-300 px-3 py-1.5 font-medium text-zinc-700 hover:bg-zinc-50">Editar</a>

                            <form method="post" action="{{ route('owner.services.toggle', $service) }}">
                                @csrf
                                @method('patch')
                                <button type="submit" class="rounded-md border border-zinc-300 px-3 py-1.5 font-medium text-zinc-700 hover:bg-zinc-50">
                                    {{ $service->active ? 'Desativar' : 'Ativar' }}
                                </button>
                            </form>

                            <form method="post" action="{{ route('owner.services.destroy', $service) }}" onsubmit="return confirm('Remover este serviço?')">
                                @csrf
                                @method('delete')
                                <button type="submit" class="rounded-md px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Remover</button>
                            </form>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</x-owner-layout>

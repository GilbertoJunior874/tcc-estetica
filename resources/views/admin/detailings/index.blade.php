<x-admin-layout title="Estéticas">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Estéticas</h1>
            <p class="mt-2 text-sm text-zinc-600">
                {{ trans_choice('{0} Nenhuma estética encontrada|{1} 1 estética encontrada|[2,*] :count estéticas encontradas', $detailings->total()) }}
            </p>
        </div>

        <form method="get" action="{{ route('admin.detailings.index') }}" class="flex items-end gap-2">
            <div>
                <label for="status" class="block text-sm font-medium text-zinc-800">Situação</label>
                <select id="status" name="status" class="mt-1 block rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value="">Todas</option>
                    @foreach (\App\Enums\DetailingStatus::cases() as $option)
                        <option value="{{ $option->value }}" @selected($status === $option)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-md border border-zinc-300 bg-white px-3.5 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Filtrar</button>
        </form>
    </div>

    @if ($detailings->isEmpty())
        <p class="mt-8 rounded-lg border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">Nenhuma estética nesta situação.</p>
    @else
        <div class="mt-8 overflow-x-auto rounded-lg border border-zinc-200 bg-white">
            <table class="w-full min-w-[48rem] text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Estética</th>
                        <th scope="col" class="px-4 py-3 font-medium">Responsável</th>
                        <th scope="col" class="px-4 py-3 font-medium">Cidade</th>
                        <th scope="col" class="px-4 py-3 font-medium">Situação</th>
                        <th scope="col" class="px-4 py-3 font-medium">Solicitada em</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($detailings as $detailing)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $detailing->name }}</td>
                            <td class="px-4 py-3">
                                @if ($detailing->owner)
                                    <span class="block">{{ $detailing->owner->name }}</span>
                                    <span class="block break-all text-zinc-500">{{ $detailing->owner->email }}</span>
                                @else
                                    <span class="text-zinc-500">Sem responsável vinculado</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $detailing->city }} – {{ $detailing->state }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$detailing->status" /></td>
                            <td class="px-4 py-3 tabular-nums text-zinc-600">{{ $detailing->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $detailings->links() }}
        </div>
    @endif
</x-admin-layout>

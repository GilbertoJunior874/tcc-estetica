<x-owner-layout title="Editar serviço">
    <div class="max-w-2xl">
        <a href="{{ route('owner.services.index') }}" class="text-sm text-zinc-500 hover:text-zinc-900">← Serviços</a>
        <h1 class="mt-2 text-2xl font-bold tracking-tight">Editar serviço</h1>

        <form method="post" action="{{ route('owner.services.update', $service) }}" class="mt-8">
            @csrf
            @method('put')
            @include('owner.services._form', ['service' => $service])

            <div class="mt-6 flex justify-end">
                <button type="submit" class="w-full rounded-md bg-brand-700 px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-brand-900 sm:w-auto">
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
</x-owner-layout>

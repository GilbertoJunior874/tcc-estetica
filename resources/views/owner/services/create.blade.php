<x-owner-layout title="Novo serviço">
    <div class="max-w-2xl">
        <a href="{{ route('owner.services.index') }}" class="text-sm text-zinc-500 hover:text-zinc-900">← Serviços</a>
        <h1 class="mt-2 text-2xl font-bold tracking-tight">Novo serviço</h1>

        <form method="post" action="{{ route('owner.services.store') }}" class="mt-8">
            @csrf
            @include('owner.services._form')

            <div class="mt-6 flex justify-end">
                <button type="submit" class="w-full rounded-md bg-brand-700 px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-brand-900 sm:w-auto">
                    Cadastrar serviço
                </button>
            </div>
        </form>
    </div>
</x-owner-layout>

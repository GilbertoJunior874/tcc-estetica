<x-owner-layout title="Cadastrar estética">
    <div class="max-w-3xl">
        <h1 class="text-2xl font-bold tracking-tight">Cadastrar estética</h1>
        <p class="mt-2 text-sm text-zinc-600">Os dados serão analisados antes de a estética aparecer no catálogo. Você poderá editá-los depois.</p>

        <form method="post" action="{{ route('owner.detailing.store') }}" class="mt-8">
            @csrf
            @include('owner.detailing._form')

            <div class="mt-6 flex justify-end">
                <button type="submit" class="w-full rounded-md bg-brand-700 px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-brand-900 sm:w-auto">
                    Enviar para análise
                </button>
            </div>
        </form>
    </div>
</x-owner-layout>

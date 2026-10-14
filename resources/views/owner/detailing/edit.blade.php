<x-owner-layout title="Dados da estética">
    <div class="max-w-3xl">
        <h1 class="text-2xl font-bold tracking-tight">Dados da estética</h1>
        <p class="mt-2 text-sm text-zinc-600">Essas informações aparecem na página pública da estética.</p>

        <form method="post" action="{{ route('owner.detailing.update') }}" class="mt-8">
            @csrf
            @method('put')
            @include('owner.detailing._form', ['detailing' => $detailing])

            <div class="mt-6 flex justify-end">
                <button type="submit" class="w-full rounded-md bg-brand-700 px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-brand-900 sm:w-auto">
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
</x-owner-layout>

<x-layout title="Cadastre sua estética">
    <div class="mx-auto max-w-md px-4 py-10 sm:py-14">
        <h1 class="text-2xl font-bold tracking-tight">Cadastre sua estética</h1>
        <p class="mt-2 text-sm text-zinc-600">Crie a conta do responsável. Em seguida você informa os dados da estética para análise.</p>

        <form method="post" action="{{ route('register') }}" class="mt-8 space-y-5 rounded-lg border border-zinc-200 bg-white p-5 sm:p-6">
            @csrf
            <x-field name="name" label="Seu nome" autocomplete="name" required autofocus />
            <x-field name="email" label="E-mail" type="email" autocomplete="email" required />
            <x-field name="password" label="Senha" type="password" autocomplete="new-password" hint="Mínimo de 8 caracteres." required />
            <x-field name="password_confirmation" label="Confirme a senha" type="password" autocomplete="new-password" required />

            <button type="submit" class="w-full rounded-md bg-brand-700 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-brand-900">
                Criar conta
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-zinc-600">
            Já tem conta? <a href="{{ route('login') }}" class="font-medium text-brand-700 hover:text-brand-900">Entrar</a>
        </p>
    </div>
</x-layout>

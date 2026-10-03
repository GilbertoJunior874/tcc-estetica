<x-layout title="Entrar">
    <div class="mx-auto max-w-md px-4 py-10 sm:py-14">
        <h1 class="text-2xl font-bold tracking-tight">Entrar</h1>
        <p class="mt-2 text-sm text-zinc-600">Acesso para responsáveis por estéticas cadastradas.</p>

        <form method="post" action="{{ route('login') }}" class="mt-8 space-y-5 rounded-lg border border-zinc-200 bg-white p-5 sm:p-6">
            @csrf
            <x-field name="email" label="E-mail" type="email" autocomplete="email" required autofocus />
            <x-field name="password" label="Senha" type="password" autocomplete="current-password" required />

            <label class="flex items-center gap-2 text-sm text-zinc-700">
                <input type="checkbox" name="remember" value="1" class="size-4 rounded border-zinc-300 accent-brand-700">
                Manter conectado
            </label>

            <button type="submit" class="w-full rounded-md bg-brand-700 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-brand-900">
                Entrar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-zinc-600">
            Ainda não tem conta? <a href="{{ route('register') }}" class="font-medium text-brand-700 hover:text-brand-900">Cadastre sua estética</a>
        </p>
    </div>
</x-layout>

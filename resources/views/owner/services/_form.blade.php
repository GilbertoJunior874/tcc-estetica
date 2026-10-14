@php($service ??= null)

<div class="space-y-5 rounded-lg border border-zinc-200 bg-white p-5 sm:p-6">
    <x-field name="name" label="Nome do serviço" :value="$service?->name" maxlength="120" required />
    <x-field name="description" label="O que está incluído" type="textarea" :value="$service?->description" maxlength="1000" hint="Descreva as etapas e o que o cliente recebe." />

    <div class="grid gap-5 sm:grid-cols-2">
        <x-field name="price" label="Preço (R$)" :value="$service ? sprintf('%d,%02d', intdiv($service->price_cents, 100), $service->price_cents % 100) : null" placeholder="149,90" inputmode="decimal" required />
        <x-field name="duration_minutes" label="Duração estimada (minutos)" type="number" :value="$service?->duration_minutes" min="5" max="1440" step="5" />
    </div>

    <label class="flex items-center gap-2 text-sm text-zinc-700">
        <input type="hidden" name="active" value="0">
        <input type="checkbox" name="active" value="1" @checked(old('active', $service?->active ?? true)) class="size-4 rounded border-zinc-300 accent-brand-700">
        Exibir este serviço na página pública
    </label>
</div>

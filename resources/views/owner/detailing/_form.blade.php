@php($detailing ??= null)

<div class="space-y-8">
    <fieldset class="rounded-lg border border-zinc-200 bg-white p-5 sm:p-6">
        <legend class="px-1 text-sm font-semibold">Empresa</legend>
        <div class="space-y-5">
            <x-field name="name" label="Nome da estética" :value="$detailing?->name" maxlength="120" required />
            <x-field name="description" label="Descrição" type="textarea" :value="$detailing?->description" maxlength="1000" hint="Conte o que a estética faz de melhor. Aparece na página pública." />
        </div>
    </fieldset>

    <fieldset class="rounded-lg border border-zinc-200 bg-white p-5 sm:p-6">
        <legend class="px-1 text-sm font-semibold">Contato</legend>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="whatsapp" label="WhatsApp" type="tel" :value="$detailing?->whatsapp" placeholder="(19) 98765-4321" required />
            <x-field name="phone" label="Telefone" type="tel" :value="$detailing?->phone" placeholder="(19) 3234-5678" />
            <div class="sm:col-span-2">
                <x-field name="email" label="E-mail comercial" type="email" :value="$detailing?->email" />
            </div>
        </div>
    </fieldset>

    <fieldset class="rounded-lg border border-zinc-200 bg-white p-5 sm:p-6">
        <legend class="px-1 text-sm font-semibold">Endereço</legend>
        <div class="grid gap-5 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <x-field name="postal_code" label="CEP" :value="$detailing?->postal_code" placeholder="13025-320" inputmode="numeric" required />
            </div>
            <div class="sm:col-span-4">
                <x-field name="street" label="Rua" :value="$detailing?->street" required />
            </div>
            <div class="sm:col-span-2">
                <x-field name="number" label="Número" :value="$detailing?->number" required />
            </div>
            <div class="sm:col-span-4">
                <x-field name="complement" label="Complemento" :value="$detailing?->complement" />
            </div>
            <div class="sm:col-span-3">
                <x-field name="neighborhood" label="Bairro" :value="$detailing?->neighborhood" required />
            </div>
            <div class="sm:col-span-2">
                <x-field name="city" label="Cidade" :value="$detailing?->city" required />
            </div>
            <div class="sm:col-span-1">
                <label for="state" class="block text-sm font-medium text-zinc-800">UF</label>
                <select id="state" name="state" required class="mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                    <option value=""></option>
                    @foreach (\App\Http\Requests\DetailingRequest::STATES as $state)
                        <option value="{{ $state }}" @selected(old('state', $detailing?->state) === $state)>{{ $state }}</option>
                    @endforeach
                </select>
                @error('state')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </fieldset>

    <fieldset class="rounded-lg border border-zinc-200 bg-white p-5 sm:p-6">
        <legend class="px-1 text-sm font-semibold">Localização no mapa</legend>
        <p class="text-sm text-zinc-600">Opcional. Com as coordenadas, a estética aparece nas buscas por proximidade. Você pode copiá-las do Google Maps ou usar a localização deste dispositivo se estiver no estabelecimento.</p>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <x-field name="latitude" label="Latitude" :value="$detailing?->latitude" inputmode="decimal" placeholder="-22.8962000" />
            <x-field name="longitude" label="Longitude" :value="$detailing?->longitude" inputmode="decimal" placeholder="-47.0521000" />
        </div>
        <button type="button" hidden data-geolocate data-latitude="#latitude" data-longitude="#longitude" class="mt-4 rounded-md border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Usar a localização deste dispositivo</button>
        <p data-geolocate-feedback aria-live="polite" class="mt-2 text-sm text-zinc-600"></p>
    </fieldset>
</div>

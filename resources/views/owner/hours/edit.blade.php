<x-owner-layout title="Horários de funcionamento">
    <div class="max-w-3xl">
        <h1 class="text-2xl font-bold tracking-tight">Horários de funcionamento</h1>
        <p class="mt-2 text-sm text-zinc-600">Informe quando a estética está aberta. Marque como fechado os dias sem atendimento.</p>

        <form method="post" action="{{ route('owner.hours.update') }}" class="mt-8">
            @csrf
            @method('put')

            <ul class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 bg-white">
                @foreach (\App\Models\BusinessHour::DAYS as $day => $dayName)
                    @php($current = $hours->get($day))
                    <li class="grid gap-3 p-4 sm:grid-cols-[10rem_1fr_1fr_auto] sm:items-center sm:gap-4">
                        <span class="font-medium">{{ $dayName }}</span>

                        <label class="flex items-center gap-2 text-sm text-zinc-600">
                            <span class="w-14 shrink-0 sm:sr-only">Abre</span>
                            <input type="time" name="hours[{{ $day }}][opens_at]" value="{{ old("hours.{$day}.opens_at", $current?->opens_at ?? '08:00') }}" aria-label="Abertura de {{ $dayName }}" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        </label>

                        <label class="flex items-center gap-2 text-sm text-zinc-600">
                            <span class="w-14 shrink-0 sm:sr-only">Fecha</span>
                            <input type="time" name="hours[{{ $day }}][closes_at]" value="{{ old("hours.{$day}.closes_at", $current?->closes_at ?? '18:00') }}" aria-label="Fechamento de {{ $dayName }}" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        </label>

                        <label class="flex items-center gap-2 text-sm text-zinc-700">
                            <input type="checkbox" name="hours[{{ $day }}][closed]" value="1" @checked(old("hours.{$day}.closed", $current?->closed ?? $day === 7)) class="size-4 rounded border-zinc-300 accent-brand-700">
                            Fechado
                        </label>

                        @if ($errors->has("hours.{$day}.*"))
                            <p class="text-sm text-red-600 sm:col-span-4">{{ $errors->first("hours.{$day}.*") }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="w-full rounded-md bg-brand-700 px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-brand-900 sm:w-auto">
                    Salvar horários
                </button>
            </div>
        </form>
    </div>
</x-owner-layout>

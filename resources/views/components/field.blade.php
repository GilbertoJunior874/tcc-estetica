@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])

@php
    $describedBy = collect([$hint ? "{$name}-hint" : null, $errors->has($name) ? "{$name}-error" : null])->filter()->implode(' ');
    $inputClass = [
        'mt-1 block w-full rounded-md border bg-white px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2',
        'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has($name),
        'border-zinc-300 focus:border-brand-600 focus:ring-brand-100' => ! $errors->has($name),
    ];
@endphp

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-zinc-800">{{ $label }}</label>

    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="4" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($errors->has($name)) aria-invalid="true" @endif {{ $attributes->class($inputClass) }}>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($errors->has($name)) aria-invalid="true" @endif @if ($type !== 'password') value="{{ old($name, $value) }}" @endif {{ $attributes->class($inputClass) }}>
    @endif

    @if ($hint)
        <p id="{{ $name }}-hint" class="mt-1 text-xs text-zinc-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $name }}-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>

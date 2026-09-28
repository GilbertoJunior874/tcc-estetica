@props(['cents'])

<span {{ $attributes }}>{{ \Illuminate\Support\Number::currency($cents / 100, in: 'BRL', locale: 'pt_BR') }}</span>

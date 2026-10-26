@props(['status'])

<span {{ $attributes->class(['inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium', $status->badgeClass()]) }}>{{ $status->label() }}</span>

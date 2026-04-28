@props(['color', 'label'])
<span {{ $attributes->class(["badge rounded-full", "bg-{$color}-500/10 text-{$color}-500"]) }}>{{ $label }}</span>

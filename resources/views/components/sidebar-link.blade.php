@props(['active', 'icon' => ''])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-3 py-2 text-sm font-medium rounded-md bg-secondary text-secondary-foreground transition-colors group'
            : 'flex items-center px-3 py-2 text-sm font-medium rounded-md text-muted-foreground hover:bg-secondary hover:text-secondary-foreground transition-colors group';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <i data-lucide="{{ $icon }}" class="w-4 h-4 mr-3 {{ ($active ?? false) ? 'text-foreground' : 'text-muted-foreground group-hover:text-foreground' }}"></i>
    @endif
    <span>{{ $slot }}</span>
</a>

@props([
    'variant' => 'primary',
    'size' => 'default',
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $baseClasses = 'inline-flex items-center border border-transparent rounded-md font-semibold uppercase tracking-widest focus:outline-none transition ease-in-out duration-150';
    
    $variantClasses = match($variant) {
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
        'secondary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
        'ghost' => 'bg-white text-blue-700 border-gray-300 hover:bg-blue-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
        'outline' => 'bg-white text-blue-600 border-blue-600 hover:bg-blue-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
        'disabled' => 'bg-gray-300 text-gray-500 cursor-not-allowed',
        default => 'bg-blue-600 text-white hover:bg-blue-700',
    };
    
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'lg' => 'px-6 py-3 text-sm',
        default => 'px-4 py-2 text-xs',
    };
    
    $classes = "{$baseClasses} {$variantClasses} {$sizeClasses}";
    $tag = $href ? 'a' : 'button';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            {!! $icon !!}
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            {!! $icon !!}
        @endif
        {{ $slot }}
    </button>
@endif

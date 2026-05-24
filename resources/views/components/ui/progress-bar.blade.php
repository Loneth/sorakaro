@props([
    'current' => 1,
    'total' => 1,
    'score' => null,
    'answered' => null,
])

@php
    $total = max(1, (int) $total);
    $current = max(1, min($total, (int) $current));

    $percent = (int) round(($current / $total) * 100);
    $percent = max(0, min(100, $percent));
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between mb-3 gap-3">
        <div class="text-sm font-medium text-gray-600">
            Pertanyaan {{ $current }} dari {{ $total }}
        </div>

        <div class="text-sm font-medium text-gray-600 flex items-center gap-4 flex-wrap justify-end">
            @if(!is_null($score))
                <span>Skor: {{ $score }}%</span>
            @endif

            @if(!is_null($answered))
                <span>Dijawab: {{ $answered }} / {{ $total }}</span>
            @endif
        </div>
    </div>

    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
        <div
            class="h-2.5 bg-gray-800 rounded-full transition-all duration-300"
            style="width: {{ $percent }}%"
        ></div>
    </div>

    <div class="mt-2 text-xs text-gray-600">
        {{ $percent }}%
    </div>
</div>

{{--
    Stepper progress bar for the guided learning flow.
    Usage: @include('learning._stepper', ['step' => 1|2|3|4])

    Steps:
      1 = Pre-test
      2 = Panduan (Guidebook)
      3 = Post-test
      4 = Hasil (Result)
--}}
@php
    $steps = [
        1 => 'Pre-test',
        2 => 'Panduan',
        3 => 'Post-test',
        4 => 'Hasil',
    ];
@endphp

<div class="border-b border-gray-100 bg-white">
    <div class="max-w-2xl mx-auto px-4 py-4">
        <nav aria-label="Langkah belajar" class="flex items-center gap-0">
            @foreach($steps as $num => $label)
                @php
                    $isDone    = $num < $step;
                    $isCurrent = $num === $step;
                    $isAhead   = $num > $step;
                    $isLast    = $num === count($steps);
                @endphp

                {{-- Step node --}}
                <div class="flex items-center {{ $isLast ? '' : 'flex-1' }}">
                    <div class="flex flex-col items-center">
                        {{-- Circle --}}
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all
                            {{ $isDone    ? 'bg-blue-600 text-white' : '' }}
                            {{ $isCurrent ? 'bg-blue-600 text-white ring-4 ring-blue-100' : '' }}
                            {{ $isAhead   ? 'bg-gray-100 text-gray-400' : '' }}">
                            @if($isDone)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        {{-- Label --}}
                        <span class="mt-1 text-xs font-medium whitespace-nowrap
                            {{ $isCurrent ? 'text-blue-600' : ($isDone ? 'text-blue-400' : 'text-gray-400') }}">
                            {{ $label }}
                        </span>
                    </div>

                    {{-- Connector line --}}
                    @if(!$isLast)
                        <div class="flex-1 h-0.5 mx-2 mb-4 rounded
                            {{ $isDone ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </nav>
    </div>
</div>

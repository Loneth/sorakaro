<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Hasil - {{ $lesson->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <x-ui.container size="sm">

            <x-ui.progress-bar
                :current="$attempt->total_questions"
                :total="$attempt->total_questions"
                :score="$attempt->score"
                :answered="$attempt->total_questions"
            />

            <x-ui.card>
                @php
                    $total = max(1, (int) $attempt->total_questions);
                    $percentage = (int) $attempt->score;
                    $correctAnswersCount = (int) round(($percentage / 100) * $total);

                    $passed = (bool) $attempt->passed;
                    $passRate = (int) ($lesson->pass_rate ?? 70);
                @endphp


                {{-- Status Icon --}}
                <div class="text-center mb-6">
                    @if($passed)
                        <div class="mx-auto h-20 w-20 rounded-full bg-green-100 flex items-center justify-center mb-4">
                            <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <x-ui.section-title :level="1" class="mb-2 text-green-700">Lulus Kuis!</x-ui.section-title>
                        <p class="text-gray-600">Kerja bagus, kamu lulus pelajaran ini!</p>
                    @else
                        <div class="mx-auto h-20 w-20 rounded-full bg-red-100 flex items-center justify-center mb-4">
                            <svg class="h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <x-ui.section-title :level="1" class="mb-2 text-red-700">Gagal Kuis</x-ui.section-title>
                        <p class="text-gray-600">Jangan menyerah! Tinjau materi dan coba lagi.</p>
                    @endif
                </div>

                {{-- Score Display --}}
                <div class="bg-gray-50 rounded-lg p-6 mb-6 border {{ $passed ? 'border-green-200 apple-success-bg' : 'border-red-200 apple-failure-bg' }}">
                    <div class="text-center">
                        <div class="text-sm text-gray-600 mb-2">Skor Kamu</div>
                        <div class="text-5xl font-bold {{ $passed ? 'text-green-600' : 'text-red-600' }} mb-2">
                            {{ $percentage }}%
                            <span class="text-xl text-gray-400 block mt-1 font-normal text-sm">
                                ({{ $correctAnswersCount }}/{{ $attempt->total_questions }} Benar)
                            </span>
                        </div>
                        <div class="text-sm font-medium {{ $passed ? 'text-green-700' : 'text-red-700' }} mt-2">
                            Syarat Lulus: {{ $passRate }}%
                        </div>
                    </div>
                </div>

                {{-- Attempt Summary --}}
                <div class="grid grid-cols-3 gap-4 mb-6 text-center">
                    <div class="bg-gray-50 rounded-lg p-4 border">
                        <div class="text-xs text-gray-500">Pertanyaan</div>
                        <div class="text-lg font-semibold text-gray-800">{{ $total }}</div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 border">
                        <div class="text-xs text-gray-500">Benar</div>
                        <div class="text-lg font-semibold text-green-700">{{ $correctAnswersCount }}</div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 border">
                        <div class="text-xs text-gray-500">Salah</div>
                        <div class="text-lg font-semibold text-red-700">{{ $total - $correctAnswersCount }}</div>
                    </div>
                </div>


                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">

                    @if(!$passed)
                        <form method="POST" action="{{ route('learn.start', $lesson) }}" class="w-full sm:w-auto">
                            @csrf
                            <x-ui.button variant="primary" type="submit" class="w-full justify-center">
                                Coba Lagi
                            </x-ui.button>
                        </form>
                    @endif

                    <x-ui.button
                        variant="{{ $passed ? 'primary' : 'ghost' }}"
                        :href="route('learn.level', $lesson->level_id)"
                        class="w-full sm:w-auto justify-center"
                    >
                        Kembali ke {{ $lesson->level ? $lesson->level->name : 'Tingkat' }}
                    </x-ui.button>

                    @if($passed)
                        <x-ui.button
                            variant="ghost"
                            :href="route('learn.index')"
                            class="w-full sm:w-auto justify-center"
                        >
                            Semua Tingkat
                        </x-ui.button>
                    @endif

                </div>


            </x-ui.card>
        </x-ui.container>
    </div>
</x-app-layout>

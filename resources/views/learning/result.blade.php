<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumb :items="[
            ['label' => 'Dasbor', 'url' => route('dashboard')],
            ['label' => 'Mulai Belajar'],
            ['label' => 'Hasil'],
        ]" />
    </x-slot>

    {{-- Stepper --}}
    @include('learning._stepper', ['step' => 4])

    <div class="max-w-2xl mx-auto py-8 px-4">

        @php
            $improvement  = $session->improvement;
            $isImproved   = $improvement > 0;
            $isSame       = $improvement === 0;
            
            $pretest = $session->pretestAttempt;
            $preScore = ($pretest && $pretest->total_questions > 0)
                ? (int) round(($pretest->score / $pretest->total_questions) * 100)
                : 0;
                
            $posttest = $session->posttestAttempt;
            $postScore = ($posttest && $posttest->total_questions > 0)
                ? (int) round(($posttest->score / $posttest->total_questions) * 100)
                : 0;
                
            $level = $session->level ? $session->level->name : 'A1';
        @endphp

        {{-- Hero result card --}}
        <div class="rounded-2xl shadow-xl overflow-hidden mb-8">
            {{-- Top gradient banner --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-400 px-8 py-8 text-white text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 mb-4">
                    @if($isImproved)
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    @elseif($isSame)
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                        </svg>
                    @else
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                    @endif
                </div>

                <h1 class="text-2xl font-bold mb-1">
                    @if($isImproved)
                        Kamu Meningkat! 🚀
                    @elseif($isSame)
                        Skor Sama 💪
                    @else
                        Tetap Semangat! ✨
                    @endif
                </h1>
                <p class="text-blue-100 text-sm">
                    @if($isImproved)
                        Kerja bagus — kamu sudah berkembang {{ $improvement }} poin!
                    @elseif($isSame)
                        Skor kamu konsisten. Coba lagi untuk meningkat!
                    @else
                        Jangan menyerah. Setiap percobaan adalah proses belajar.
                    @endif
                </p>
            </div>

            {{-- Score comparison --}}
            <div class="bg-white px-8 py-6">
                <div class="grid grid-cols-3 gap-4 items-center">

                    {{-- Pre-test --}}
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-800">{{ $preScore }}%</div>
                        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wide">Pre-test</div>
                    </div>

                    {{-- Arrow + improvement --}}
                    <div class="flex flex-col items-center">
                        <div class="text-2xl font-bold
                            {{ $isImproved ? 'text-green-500' : ($isSame ? 'text-gray-400' : 'text-red-400') }}">
                            {{ $isImproved ? '+' : '' }}{{ $improvement }}%
                        </div>
                        <svg class="w-6 h-6 mt-1
                            {{ $isImproved ? 'text-green-400' : ($isSame ? 'text-gray-300' : 'text-red-300') }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>

                    {{-- Post-test --}}
                    <div class="text-center">
                        <div class="text-3xl font-bold
                            {{ $isImproved ? 'text-blue-600' : ($isSame ? 'text-gray-800' : 'text-gray-800') }}">
                            {{ $postScore }}%
                        </div>
                        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wide">Post-test</div>
                    </div>
                </div>

                {{-- Level context badge --}}
                <div class="mt-6 flex items-center justify-center gap-2">
                    <span class="text-sm text-gray-500">Materi yang dipelajari:</span>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                        {{ $level }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Progress bar visual --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-8">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-3">Perbandingan Skor</p>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Pre-test</span>
                        <span class="font-medium text-gray-700">{{ $preScore }}%</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gray-400 transition-all duration-700"
                             style="width: {{ $preScore }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Post-test</span>
                        <span class="font-medium text-blue-600">{{ $postScore }}%</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-blue-500 transition-all duration-700"
                             style="width: {{ $postScore }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CTAs --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('learning.start') }}"
               id="retry-assessment-btn"
               class="flex-1 inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50
                      border border-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Ulangi Assessment
            </a>

            <a href="{{ route('dashboard') }}"
               id="continue-learning-btn"
               class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                      text-white font-semibold px-6 py-3 rounded-xl shadow transition active:scale-95">
                Lanjutkan Belajar
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>


    </div>
</x-app-layout>

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
            $pretest = $session->pretestAttempt;
            $preScore = ($pretest && $pretest->total_questions > 0)
                ? (int) round(($pretest->score / $pretest->total_questions) * 100)
                : 0;
                
            $posttest = $session->posttestAttempt;
            $postScore = ($posttest && $posttest->total_questions > 0)
                ? (int) round(($posttest->score / $posttest->total_questions) * 100)
                : 0;
                
            $isPassed = $posttest && $posttest->passed;
            $passRate = $posttest && $posttest->lesson ? $posttest->lesson->pass_rate : 70;
                
            $level = $session->level ? $session->level->name : 'A1';
        @endphp

        {{-- Hero result card --}}
        <div class="rounded-2xl shadow-xl overflow-hidden mb-8">
            {{-- Top gradient banner --}}
            <div class="{{ $isPassed ? 'bg-gradient-to-r from-green-600 to-green-400' : 'bg-gradient-to-r from-blue-600 to-blue-400' }} px-8 pt-10 pb-8 text-white text-center">
                <div class="flex justify-center mb-6">
                    @if($isPassed)
                        <img src="{{ asset('images/result/success.png') }}" alt="Success" class="h-48 md:h-56 w-auto object-contain drop-shadow-xl hover:scale-105 transition-transform duration-500">
                    @else
                        <img src="{{ asset('images/result/fail.png') }}" alt="Keep Trying" class="h-48 md:h-56 w-auto object-contain drop-shadow-xl hover:scale-105 transition-transform duration-500">
                    @endif
                </div>

                <h1 class="text-2xl md:text-3xl font-bold mb-2">
                    @if($isPassed)
                        Selamat! Kamu berhasil menyelesaikan level ini 🎉
                    @else
                        Belum berhasil kali ini 🚀
                    @endif
                </h1>
                <p class="{{ $isPassed ? 'text-green-50' : 'text-blue-50' }} text-sm md:text-base max-w-sm mx-auto">
                    @if($isPassed)
                        Luar biasa! Kamu telah menguasai materi ini dengan sangat baik.
                    @else
                        Belajar bahasa memang butuh proses. Yuk coba pelajari materinya lagi!
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

                    {{-- Arrow --}}
                    <div class="flex flex-col items-center">
                        <div class="text-sm font-bold text-gray-400 mb-1">Target: {{ $passRate }}%</div>
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>

                    {{-- Post-test --}}
                    <div class="text-center">
                        <div class="text-3xl font-bold {{ $isPassed ? 'text-green-600' : 'text-blue-600' }}">
                            {{ $postScore }}%
                        </div>
                        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wide">Post-test</div>
                    </div>
                </div>

                {{-- Level context badge --}}
                <div class="mt-6 flex items-center justify-center gap-2">
                    <span class="text-sm text-gray-500">Materi yang dipelajari:</span>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full {{ $isPassed ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }} font-bold text-sm">
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
            @if($isPassed)
                <a href="{{ route('dashboard') }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700
                          text-white font-semibold px-6 py-3 rounded-xl shadow transition active:scale-95">
                    Lanjut ke Level Berikutnya
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            @else
                <a href="{{ route('learning.start') }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50
                          border border-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Ulangi Belajar
                </a>

                <a href="{{ route('dashboard') }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                          text-white font-semibold px-6 py-3 rounded-xl shadow transition active:scale-95">
                    Kembali ke Dasbor
                </a>
            @endif
        </div>


    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumb :items="[
            ['label' => 'Dasbor', 'url' => route('dashboard')],
            ['label' => 'Mulai Belajar'],
            ['label' => 'Post-test'],
        ]" />
    </x-slot>

    {{-- Stepper --}}
    @include('learning._stepper', ['step' => 3])

    <div class="max-w-2xl mx-auto py-8 px-4">

        {{-- Header card --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-blue-200 text-xs font-bold tracking-wider uppercase mb-1">Langkah 3 dari 4</div>
                    <h1 class="text-xl font-bold">Tes Pemahaman Akhir</h1>
                    <p class="text-blue-100 text-sm">Uji pemahaman kamu setelah membaca panduan 💪</p>
                </div>
            </div>
            @if($session->pretestAttempt)
                @php
                    $pretest = $session->pretestAttempt;
                    $preScore = $pretest ? min(max((int) $pretest->score, 0), 100) : 0;
                @endphp
                <div class="mt-3 flex items-center gap-2 bg-white/10 rounded-lg px-4 py-2 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Skor pre-test kamu: <strong class="text-white bg-blue-800/50 px-1.5 py-0.5 rounded">{{ $preScore }}%</strong></span>
                    <span class="opacity-75">· Sekarang buktikan peningkatanmu!</span>
                </div>
            @endif
        </div>

        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('learning.posttest.submit') }}" id="posttest-form">
            @csrf

            <div class="space-y-6">
                @foreach($questions as $index => $question)
                    @include('learning._question-card', [
                        'question' => $question,
                        'index'    => $index,
                        'formName' => 'posttest-form',
                    ])
                @endforeach
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit"
                        class="inline-flex flex-col items-center justify-center bg-blue-600 hover:bg-blue-700 text-white
                               px-8 py-3 rounded-xl shadow transition active:scale-95 leading-tight">
                    <div class="flex items-center gap-2 font-semibold">
                        Selesai & Lihat Hasil
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </button>
            </div>
        </form>
    </div>

</x-app-layout>


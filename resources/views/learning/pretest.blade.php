<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumb :items="[
            ['label' => 'Dasbor', 'url' => route('dashboard')],
            ['label' => 'Mulai Belajar'],
            ['label' => 'Pre-test'],
        ]" />
    </x-slot>

    {{-- Stepper --}}
    @include('learning._stepper', ['step' => 1])

    <div class="max-w-2xl mx-auto py-8 px-4">

        {{-- Header card --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <div class="text-blue-200 text-xs font-bold tracking-wider uppercase mb-1">Langkah 1 dari 4</div>
                    <h1 class="text-xl font-bold">Tes Pemahaman Awal</h1>
                    <p class="text-blue-100 text-sm">Ukur pemahamanmu tentang Bahasa Karo sebelum mulai belajar</p>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-sm text-blue-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Jawab semua soal untuk mengukur pemahaman awalmu.</span>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('learning.pretest.submit') }}" id="pretest-form">
            @csrf

            <div class="space-y-6">
                @foreach($questions as $index => $question)
                    @include('learning._question-card', [
                        'question' => $question,
                        'index'    => $index,
                        'formName' => 'pretest-form',
                    ])
                @endforeach
            </div>

            {{-- Submit --}}
            <div class="mt-8 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold
                               px-8 py-3 rounded-xl shadow transition active:scale-95">
                    Selesai & Lanjut ke Panduan
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    {{-- Alpine.js audio player component --}}
</x-app-layout>


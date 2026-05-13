<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumb :items="[
            ['label' => 'Dasbor', 'url' => route('dashboard')],
            ['label' => 'Mulai Belajar'],
            ['label' => 'Buku Panduan'],
        ]" />
    </x-slot>

    {{-- Stepper --}}
    @include('learning._stepper', ['step' => 2])

    <div class="max-w-2xl mx-auto py-8 px-4">

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <div class="text-blue-200 text-xs font-bold tracking-wider uppercase mb-1">Langkah 2 dari 4</div>
                    <h1 class="text-xl font-bold">Buku Panduan</h1>
                    <p class="text-blue-100 text-sm">
                        {{ $level?->name ?? 'Materi Pembelajaran' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Guidebook content --}}
        @if($sections->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p class="text-sm">Konten panduan belum tersedia untuk level ini.</p>
                <p class="text-xs mt-1">Klik "Lanjutkan ke Post-test" untuk melanjutkan.</p>
            </div>
        @else
            <div class="space-y-6" id="guidebook-content">
                @foreach($sections as $section)
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="bg-gray-50 border-b border-gray-100 px-6 py-4">
                            <h2 class="font-semibold text-gray-800">{{ $section->title }}</h2>
                            @if($section->subtitle)
                                <p class="text-sm text-gray-500 mt-0.5">{{ $section->subtitle }}</p>
                            @endif
                        </div>

                        @if($section->description)
                            <div class="px-6 py-4 text-gray-600 text-sm leading-relaxed prose prose-sm max-w-none">
                                {!! nl2br(e($section->description)) !!}
                            </div>
                        @endif

                        @if($section->items->isNotEmpty())
                            <ul class="divide-y divide-gray-50">
                                @foreach($section->items as $item)
                                    <li class="px-6 py-4">
                                        @if($item->title)
                                            <h3 class="font-medium text-gray-800 text-sm mb-1">{{ $item->title }}</h3>
                                        @endif
                                        @if($item->content)
                                            <p class="text-gray-600 text-sm leading-relaxed">{!! nl2br(e($item->content)) !!}</p>
                                        @endif
                                        @if($item->example)
                                            <div class="mt-2 bg-blue-50 border-l-4 border-blue-400 px-4 py-2 rounded-r-lg text-sm text-blue-800 italic">
                                                {{ $item->example }}
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Continue button --}}
        <div class="mt-10 flex justify-end">
            <form method="POST" action="{{ route('learning.guidebook.complete') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold
                               px-8 py-3 rounded-xl shadow transition active:scale-95">
                    Lanjutkan ke Post-test
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</x-app-layout>

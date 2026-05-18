<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumb :items="[
            ['label' => 'Dasbor', 'url' => route('dashboard')],
            ['label' => 'Tingkat', 'url' => route('learn.index')],
            ['label' => $level->name]
        ]" />
    </x-slot>

    <div class="pt-4 pb-8">
        <x-ui.container size="md">

            <x-ui.button variant="ghost" :href="route('learn.index')">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Tingkat
            </x-ui.button>

            @if (session('error'))
                <div class="p-3 rounded bg-red-100 text-red-800 mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <x-ui.card>
                @if ($level->description)
                    <div class="mb-6 text-gray-600 text-lg">{{ $level->description }}</div>
                @endif

                <div class="space-y-4">
                    @forelse ($lessons as $lesson)
                        <div class="flex items-center justify-between border rounded p-4 bg-gray-50 hover:bg-gray-100 transition">
                            <div class="flex items-center gap-4">
                                {{-- Icon/Number --}}
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-700 font-bold text-lg">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <x-ui.section-title :level="3">{{ $lesson->title }}</x-ui.section-title>
                                    <div class="text-sm text-gray-500">{{ $lesson->questions_count }} Pertanyaan</div>

                                    {{-- Progress Info --}}
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        {{-- Status Badge --}}
                                        @if($lesson->status === 'completed')
                                            <x-ui.badge variant="success">Lulus</x-ui.badge>
                                        @elseif($lesson->status === 'failed')
                                            <x-ui.badge variant="danger">Gagal</x-ui.badge>
                                        @elseif($lesson->status === 'in_progress')
                                            <x-ui.badge variant="warning">Sedang Berjalan</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="default">Belum Dimulai</x-ui.badge>
                                        @endif

                                        {{-- Last Score --}}
                                        @if($lesson->latest_attempt)
                                             <span class="text-xs text-gray-600 border-l pl-2 border-gray-300">
                                                Terakhir: {{ $lesson->latest_attempt->score }}/{{ $lesson->latest_attempt->total_questions }}
                                             </span>
                                        @endif

                                        {{-- Attempts Count --}}
                                        @if($lesson->attempts_count > 0)
                                            <span class="text-xs text-gray-500 border-l pl-2 border-gray-300">
                                                {{ $lesson->attempts_count }} percobaan
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-2">
                                {{-- Continue Button --}}
                                @if($lesson->status === 'in_progress' && $lesson->latest_attempt)
                                     <a href="{{ route('learn.resume', $lesson->latest_attempt->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 underline">
                                         Lanjut
                                     </a>
                                @endif

                                <form method="POST" action="{{ route('learn.start', $lesson) }}">
                                    @csrf
                                    <x-ui.button variant="primary" type="submit">
                                        {{ $lesson->status === 'in_progress' ? 'Mulai Ulang' : 'Mulai Kuis' }}
                                    </x-ui.button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state 
                            title="Belum ada pelajaran"
                            description="Belum ada lesson di level ini."
                        />
                    @endforelse
                </div>
            </x-ui.card>

        </x-ui.container>
    </div>
</x-app-layout>

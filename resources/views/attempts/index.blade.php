<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumb :items="[
            ['label' => 'Dasbor', 'url' => route('dashboard')],
            ['label' => 'Percobaan']
        ]" />
    </x-slot>

    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 bg-white border border-gray-200 rounded-xl shadow-sm">
            <div>
                <h1 class="text-lg font-semibold text-gray-900">Riwayat Percobaan</h1>
                <p class="mt-1 text-sm text-gray-600">Riwayat kuis yang telah Anda kerjakan.</p>
            </div>

            <div class="flex items-center gap-3">
                @if(request()->anyFilled(['lesson_id', 'level_id', 'status']))
                    <a href="{{ route('attempts.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 hover:underline">
                        Hapus filter
                    </a>
                @endif
                
                {{-- Filter Button (Modal Trigger) --}}
                <button data-modal-target="attempts-filter-modal" data-modal-toggle="attempts-filter-modal" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                    <svg class="w-4 h-4 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    Filter
                    @if(request()->anyFilled(['lesson_id', 'level_id', 'status']))
                        <span class="ml-2 inline-flex items-center justify-center w-2 h-2 p-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full"> </span>
                    @endif
                </button>

                <a href="{{ route('learn.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-200">
                    Pergi ke Belajar
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Pelajaran</th>
                            <th class="px-4 py-3">Tingkat</th>
                            <th class="px-4 py-3">Skor</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($attempts as $attempt)
                            <tr class="bg-white border-t border-gray-100">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $attempt->lesson->title ?? $attempt->lesson->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $attempt->lesson->level->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ (int) ($attempt->score ?? 0) }}%
                                </td>
                                <td class="px-4 py-3">
                                    @if((bool) ($attempt->passed ?? false))
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                            Lulus
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                            Gagal
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ optional($attempt->created_at)->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('learn.result', [$attempt->lesson_id, $attempt->id]) }}"
                                       class="text-sm font-semibold text-gray-900 hover:underline">
                                        Lihat hasil →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-600">
                                    Tidak ada percobaan yang ditemukan.
                                    <a href="{{ route('learn.index') }}" class="font-semibold text-gray-900 hover:underline">
                                        Mulai belajar
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attempts->hasPages())
                <div class="px-4 py-4 border-t border-gray-100">
                    {{ $attempts->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Filter Modal --}}
    <div id="attempts-filter-modal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-lg max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow">
                <!-- Modal header -->
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Saring Percobaan
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="attempts-filter-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form action="{{ route('attempts.index') }}" method="GET">
                    <div class="p-6 space-y-6">
                         {{-- Lesson Filter --}}
                        <div>
                            <label for="lesson_id" class="block mb-2 text-sm font-medium text-gray-900">Pelajaran</label>
                            <select name="lesson_id" id="lesson_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Semua Pelajaran</option>
                                @foreach($lessonOptions as $lesson)
                                    <option value="{{ $lesson->id }}" {{ request('lesson_id') == $lesson->id ? 'selected' : '' }}>
                                        {{ $lesson->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Level Filter --}}
                        <div>
                            <label for="level_id" class="block mb-2 text-sm font-medium text-gray-900">Tingkat</label>
                            <select name="level_id" id="level_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Semua Tingkat</option>
                                @foreach($levelOptions as $level)
                                    <option value="{{ $level->id }}" {{ request('level_id') == $level->id ? 'selected' : '' }}>
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status Filter --}}
                        <div>
                            <label for="status" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                            <select name="status" id="status"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Semua Status</option>
                                <option value="passed" {{ request('status') === 'passed' ? 'selected' : '' }}>Lulus</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                            </select>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="submit" class="text-white bg-gray-900 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Terapkan Filter</button>
                        <a href="{{ route('attempts.index') }}" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

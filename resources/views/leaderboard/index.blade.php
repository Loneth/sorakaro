<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumb :items="[
            ['label' => 'Dasbor', 'url' => route('dashboard')],
            ['label' => 'Papan Peringkat']
        ]" />
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 bg-white border border-gray-200 rounded-xl shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Papan Peringkat</h1>
                <p class="mt-1 text-sm text-gray-600">Lihat siapa yang memuncaki klasemen!</p>
            </div>
            
            {{-- Toggle Range --}}
            <div class="inline-flex rounded-lg shadow-sm" role="group">
                <a href="{{ route('leaderboard.index', ['range' => 'weekly']) }}" 
                   class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-l-lg focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700
                   {{ $range === 'weekly' ? 'bg-gray-100 text-blue-700' : 'bg-white text-gray-900 hover:bg-gray-100 hover:text-blue-700' }}">
                    Mingguan
                </a>
                <a href="{{ route('leaderboard.index', ['range' => 'all']) }}" 
                   class="px-4 py-2 text-sm font-medium border border-l-0 border-gray-200 rounded-r-lg focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700
                   {{ $range === 'all' ? 'bg-gray-100 text-blue-700' : 'bg-white text-gray-900 hover:bg-gray-100 hover:text-blue-700' }}">
                   Sepanjang Masa
                </a>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-6 py-3 w-16 text-center">Peringkat</th>
                            <th class="px-6 py-3">Pengguna</th>
                            <th class="px-6 py-3 text-right">Poin</th>
                            <th class="px-6 py-3 text-center">Percobaan</th>
                            <th class="px-6 py-3 text-center">Tingkat Lulus</th>
                            <th class="px-6 py-3 text-center">Rata-rata Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaderboard as $index => $user)
                            @php
                                $rank = $index + 1;
                                $isCurrentUser = $user->id === auth()->id();
                            @endphp
                            <tr class="border-b border-gray-100 {{ $isCurrentUser ? 'bg-blue-50' : 'bg-white hover:bg-gray-50' }}">
                                <td class="px-6 py-4 text-center">
                                    @if($rank === 1)
                                        <span class="text-xl">🥇</span>
                                    @elseif($rank === 2)
                                        <span class="text-xl">🥈</span>
                                    @elseif($rank === 3)
                                        <span class="text-xl">🥉</span>
                                    @else
                                        <span class="font-medium text-gray-500">{{ $rank }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 w-full">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $user->name }}</span>
                                        @if($isCurrentUser)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Anda
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">
                                    {{ number_format($user->total_correct) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{ $user->total_attempts }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{ $user->pass_rate }}%
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{ $user->avg_score }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    Tidak ada catatan yang ditemukan untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

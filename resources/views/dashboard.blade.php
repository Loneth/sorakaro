<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumb :items="[['label' => 'Dashboard']]" />
    </x-slot>

    <div class="space-y-8 animate-fade-in">

        {{-- ── HERO ── --}}
        <div class="bg-blue-600 rounded-2xl p-8 lg:p-10 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-72 h-72 bg-blue-500/30 rounded-full -translate-y-1/3 translate-x-1/4 pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="flex items-center gap-4 text-2xl lg:text-3xl font-bold mb-1">
                       <img src="{{ asset('images/welcome.png') }}" alt="Welcome" class="h-20 md:h-24 w-auto drop-shadow-md">
                       <span>Selamat Datang, Menjuah-Juah {{ explode(' ', Auth::user()->name)[0] }}</span>
                    </h1>
                    <p class="text-blue-100 text-sm">
                        @if($levelsCompleted > 0)
                            Kamu sudah menyelesaikan {{ $levelsCompleted }} dari {{ $totalLevels }} level. Terus semangat! 🚀
                        @else
                            Mulai perjalanan belajar Bahasa Karo kamu sekarang!
                        @endif
                    </p>
                        <div class="mt-4 max-w-xs">
                            <div class="flex justify-between text-xs text-blue-200 mb-1">
                                <span>Perjalanan Belajar</span>
                                <span>{{ $overallProgress }}%</span>
                            </div>
                            <div class="w-full bg-white/20 rounded-full h-2">
                                <div class="h-2 rounded-full bg-white transition-all duration-700"
                                     style="width: {{ max(0, min(100, $overallProgress)) }}%"></div>
                            </div>
                        </div>
                </div>

                <div class="flex flex-col items-start lg:items-center">
                    <a href="{{ $smartCTA['route'] }}"
                       class="inline-flex items-center gap-2 px-7 py-3.5 bg-white text-blue-700 font-bold rounded-xl shadow-lg hover:scale-[1.04] transition-all duration-200">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        {{ $smartCTA['label'] }}
                    </a>
                    <p class="text-blue-100 text-[11px] mt-2 font-medium opacity-80">{{ $smartCTA['subtext'] }}</p>
                </div>
            </div>
        </div>

        {{-- ── STATS ── --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="dash-card p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($totalXP ?? 0) }}</div>
                    <div class="text-xs text-gray-500 font-medium">Total XP</div>
                </div>
            </div>

            <div class="dash-card p-5 flex items-center justify-between col-span-1 sm:col-span-1 lg:col-span-2">
                <div class="w-full">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-2">
                            <div class="text-lg font-bold text-gray-900">🔥 {{ $dailyStreak ?? 0 }} Hari Beruntun</div>
                        </div>
                        <div class="text-[10px] text-gray-400 font-medium uppercase tracking-wider hidden sm:block">Pertahankan streak kamu!</div>
                    </div>
                    <div class="flex justify-between items-center w-full max-w-sm">
                        @foreach($streakHistory as $day)
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-[10px] font-medium {{ $day['active'] ? 'text-blue-600' : 'text-gray-400' }}">{{ $day['day'] }}</span>
                                <div class="w-6 h-6 sm:w-8 sm:h-8 rounded flex items-center justify-center {{ $day['active'] ? 'bg-blue-500 shadow-sm shadow-blue-200' : 'bg-gray-100' }}">
                                    @if($day['active'])
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="dash-card p-5 flex items-center gap-4 hidden lg:flex">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $levelsCompleted ?? 0 }} <span class="text-sm text-gray-400 font-medium">/ {{ $totalLevels ?? 0 }}</span></div>
                    <div class="text-xs text-gray-500 font-medium">Level Selesai</div>
                </div>
            </div>
        </div>

        {{-- ── MAIN CONTENT ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Left Column (3/5) --}}
            <div class="lg:col-span-3 space-y-6">

                {{-- Learning Progress Summary Card --}}
                <div class="dash-card p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-base font-bold text-gray-900">Progress Belajar Bahasa Karo</h2>
                    </div>

                    @if($levelCards->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-4">Belum ada level tersedia.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($levelCards as $card)
                                @php
                                    $level     = $card['level'];
                                    $unlocked  = $card['is_unlocked'];
                                    $completed = $card['is_completed'];
                                    $pct       = $card['progress_pct'];
                                @endphp

                                <div class="flex items-center gap-4">
                                    {{-- Icon --}}
                                    <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center
                                        {{ $completed ? 'bg-green-100' : ($unlocked ? 'bg-blue-50' : 'bg-gray-100') }}">
                                        @if($completed)
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        @elseif(!$unlocked)
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        @else
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                                        @endif
                                    </div>

                                    {{-- Label + bar --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-sm font-semibold {{ $unlocked ? 'text-gray-800' : 'text-gray-400' }}">
                                                {{ $level->name }}
                                            </span>
                                            @if($completed)
                                                <span class="text-xs font-bold text-green-600">✅ Selesai</span>
                                            @elseif(!$unlocked)
                                                <span class="text-xs text-gray-400">🔒 Terkunci</span>
                                            @else
                                                <span class="text-xs text-blue-500 font-bold">Terbuka</span>
                                            @endif
                                        </div>

                                        @if($unlocked && !$completed)
                                            <div class="w-full bg-gray-100 rounded-full h-2">
                                                <div class="h-2 rounded-full bg-blue-500 transition-all duration-700"
                                                     style="width: {{ $pct }}%"></div>
                                            </div>
                                        @elseif($completed)
                                            <div class="w-full bg-green-100 rounded-full h-2">
                                                <div class="h-2 rounded-full bg-green-400 w-full"></div>
                                            </div>
                                        @else
                                            <div class="w-full bg-gray-100 rounded-full h-2"></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Recent Activity --}}
                <div class="dash-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-gray-900">Aktivitas Terakhir</h2>
                        <a href="{{ route('attempts.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                            Lihat semua →
                        </a>
                    </div>

                    @if(empty($recentActivities) || count($recentActivities) === 0)
                        <div class="text-center py-6">
                            <p class="text-sm text-gray-400 mb-3">Belum ada aktivitas belajar.</p>
                            <a href="{{ route('learn.index') }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                                Mulai Sekarang →
                            </a>
                        </div>
                    @else
                        <div class="divide-y divide-gray-50">
                            @foreach($recentActivities as $a)
                                <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-sm">
                                        {{ $a['icon'] }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate">{{ $a['title'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $a['time_ago'] }}</div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @if($a['type'] === 'pretest' || $a['type'] === 'guidebook')
                                            <span class="text-xs font-semibold text-gray-400">Selesai</span>
                                        @elseif($a['type'] === 'posttest_passed')
                                            <span class="text-xs font-semibold text-blue-600">Lulus</span>
                                        @else
                                            <span class="text-xs font-semibold text-gray-400">Coba Lagi</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column (2/5) --}}
            <div class="lg:col-span-2 space-y-6">



                {{-- Leaderboard --}}
                <div class="dash-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-gray-900">Papan Peringkat</h2>
                        <span class="text-[11px] font-semibold px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full">Mingguan</span>
                    </div>

                    @if($topLeaderboard->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-3">Belum ada aktivitas minggu ini.</p>
                    @else
                        <div class="space-y-2.5">
                            @foreach($topLeaderboard as $index => $user)
                                <div class="flex items-center gap-3 p-2.5 rounded-xl {{ $user->is_me ? 'bg-blue-50' : '' }}">
                                    <span class="w-6 text-center text-sm font-bold {{ $index === 0 ? 'text-blue-600' : 'text-gray-400' }}">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="w-8 h-8 rounded-full bg-blue-{{ $index === 0 ? '600' : ($index === 1 ? '400' : '300') }} flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-xs">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 truncate block">
                                            {{ $user->name }}
                                            @if($user->is_me)
                                                <span class="text-[10px] font-bold text-blue-600 ml-1">ANDA</span>
                                            @endif
                                        </span>
                                    </div>
                                    <span class="text-xs font-bold text-gray-500">{{ number_format($user->total_correct) }} pts</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between">
                            @if($myRank)
                                <span class="text-xs text-gray-400">Peringkat Anda: <span class="font-bold text-gray-700">#{{ $myRank }}</span></span>
                            @else
                                <span class="text-xs text-gray-400">Belum ada peringkat</span>
                            @endif
                            <a href="{{ route('leaderboard.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat semua →</a>
                        </div>
                    @endif
                </div>


            </div>
        </div>

    </div>
</x-app-layout>

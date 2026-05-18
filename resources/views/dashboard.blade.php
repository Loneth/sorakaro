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
                       <img src="{{ Auth::user()->gender === 'female' ? asset('images/female.png') : asset('images/male.png') }}" alt="User Avatar" class="h-20 md:h-24 w-auto drop-shadow-md">
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

                <div class="flex flex-col items-start lg:items-center gap-3">
                    <img src="{{ asset('images/welcome.png') }}" alt="Welcome" class="hidden sm:block h-24 lg:h-32 w-auto drop-shadow-lg object-contain">
                    <div class="flex flex-col items-start lg:items-center">
                        <a href="{{ $smartCTA['route'] }}"
                           class="inline-flex items-center gap-2 px-7 py-3.5 bg-white text-blue-700 font-bold rounded-xl shadow-lg hover:scale-[1.04] transition-all duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            {{ $smartCTA['label'] }}
                        </a>
                        <p class="text-blue-100 text-[11px] mt-2 font-medium opacity-80 text-center">{{ $smartCTA['subtext'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── STATS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="dash-card p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($totalAssessmentsPassed ?? 0) }}</div>
                    <div class="text-xs text-gray-500 font-medium">Asesmen Lulus</div>
                </div>
            </div>

            <div class="dash-card p-5 flex items-center justify-between sm:col-span-2">
                <div class="w-full">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1.5 text-lg font-bold text-gray-900">
                                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                {{ $dailyStreak ?? 0 }} Hari Beruntun
                            </div>
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Left Column --}}
            <div class="space-y-6">

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
                                                <span class="inline-flex items-center gap-1 text-xs font-bold text-green-600">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                    Selesai
                                                </span>
                                            @elseif(!$unlocked)
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                                    Terkunci
                                                </span>
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

                {{-- Category Performance --}}
                <div class="dash-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-gray-900">Kategori Performa</h2>
                    </div>

                    @if($categoryStats->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-4">Selesaikan kuis untuk melihat performamu.</p>
                    @else
                        @if($performanceInsight)
                            <div class="mb-5 p-3 bg-blue-50/80 rounded-xl border border-blue-100">
                                <p class="text-xs font-semibold text-blue-700 leading-relaxed">{{ $performanceInsight }}</p>
                            </div>
                        @endif

                        <div class="space-y-4">
                            @foreach($categoryStats as $stat)
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base">{!! $stat['icon'] !!}</span>
                                            <span class="text-sm font-semibold text-gray-800">{{ $stat['category'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $stat['bg'] }} {{ $stat['text_color'] }} uppercase tracking-wider">
                                                {{ $stat['state'] }}
                                            </span>
                                            <span class="text-sm font-bold {{ $stat['text_color'] }}">{{ $stat['percentage'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $stat['color'] }} transition-all duration-1000 ease-out"
                                             style="width: {{ $stat['percentage'] }}%"></div>
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
                                        {!! $a['icon'] !!}
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

            {{-- Right Column --}}
            <div class="space-y-6">

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
                                <div class="flex flex-col p-3 rounded-xl border border-gray-100 {{ $user->is_me ? 'bg-blue-50' : 'bg-white' }}">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="text-base font-bold {{ $index === 0 ? 'text-yellow-500' : ($index === 1 ? 'text-gray-400' : 'text-amber-700') }}">
                                            #{{ $index + 1 }}
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-sm font-bold text-gray-900 truncate flex items-center gap-2">
                                                {{ $user->name }}
                                                @if($user->is_me)
                                                    <span class="text-[10px] font-bold text-blue-600 bg-blue-100 px-1.5 py-0.5 rounded">ANDA</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="space-y-1.5 text-xs text-gray-700 font-medium">
                                        <div class="flex items-center justify-between bg-gray-50 px-2 py-1.5 rounded">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Rata-rata Posttest:
                                            </span>
                                            <span class="font-bold text-gray-900">{{ $user->avg_posttest_score !== null ? $user->avg_posttest_score . '%' : '-' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between bg-gray-50 px-2 py-1.5 rounded">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                Level Selesai:
                                            </span>
                                            <span class="font-bold text-gray-900">{{ $user->completed_levels }}</span>
                                        </div>
                                        <div class="flex items-center justify-between bg-gray-50 px-2 py-1.5 rounded">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                Hari Beruntun:
                                            </span>
                                            <span class="font-bold text-gray-900">{{ $user->streak }}</span>
                                        </div>
                                    </div>
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

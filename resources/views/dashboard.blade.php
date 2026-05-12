<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumb :items="[
            ['label' => 'Dashboard']
        ]" />
    </x-slot>

    <div class="space-y-8 animate-fade-in">

        {{-- ── HERO ── --}}
        <div class="bg-blue-600 rounded-2xl p-8 lg:p-10 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-72 h-72 bg-blue-500/30 rounded-full -translate-y-1/3 translate-x-1/4 pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/3 w-48 h-48 bg-blue-800/20 rounded-full translate-y-1/2 pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold mb-1">
                        Halo, {{ explode(' ', Auth::user()->name)[0] }} 👋
                    </h1>

                    @if($heroCTAState === 'active' && $learningSession)
                        @php
                            $stepLabels = [
                                'not_started'    => ['label' => 'Pre-test', 'icon' => '📝', 'sub' => 'Mulai dengan pre-test untuk deteksi levelmu'],
                                'pretest_done'   => ['label' => 'Panduan', 'icon' => '📖', 'sub' => 'Pelajari materi sesuai levelmu'],
                                'guidebook_done' => ['label' => 'Post-test', 'icon' => '✅', 'sub' => 'Uji pemahamanmu dengan post-test'],
                                'posttest_done'  => ['label' => 'Hasil', 'icon' => '🏆', 'sub' => 'Lihat hasil dan perkembanganmu'],
                            ];
                            $info = $stepLabels[$learningSession->status] ?? $stepLabels['not_started'];
                        @endphp
                        <p class="text-blue-100 text-sm flex items-center gap-1.5">
                            <span>{{ $info['icon'] }}</span>
                            <span>Langkah selanjutnya: <strong class="text-white">{{ $info['label'] }}</strong></span>
                        </p>
                        <p class="text-blue-200 text-xs mt-0.5">{{ $info['sub'] }}</p>
                    @elseif($heroCTAState === 'completed')
                        <p class="text-blue-100 text-sm">🎉 Sesi belajar selesai! Ingin belajar lagi?</p>
                    @else
                        <p class="text-blue-100 text-sm">Siap memulai perjalanan belajarmu? 🚀</p>
                    @endif

                    {{-- Progress mini bar for active session --}}
                    @if($heroCTAState === 'active' && $learningSession)
                        @php
                            $stepProgress = [
                                'not_started'    => 0,
                                'pretest_done'   => 33,
                                'guidebook_done' => 66,
                                'posttest_done'  => 90,
                            ];
                            $pct = $stepProgress[$learningSession->status] ?? 0;
                        @endphp
                        <div class="mt-4 max-w-xs">
                            <div class="flex justify-between text-xs text-blue-200 mb-1">
                                <span>Progress</span>
                                <span>{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-white/20 rounded-full h-2">
                                <div class="h-2 rounded-full bg-white transition-all duration-700" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>

                <a href="{{ route('learning.start') }}"
                   id="hero-cta-btn"
                   class="inline-flex items-center gap-2 px-7 py-3.5 bg-white text-blue-700 font-bold rounded-xl shadow-lg hover:scale-[1.04] hover:shadow-xl transition-all duration-200 self-start lg:self-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    @if($heroCTAState === 'active')
                        Lanjutkan Belajar
                    @elseif($heroCTAState === 'completed')
                        Belajar Lagi
                    @else
                        Mulai Belajar
                    @endif
                </a>
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

            <div class="dash-card p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $dailyStreak ?? 0 }}</div>
                    <div class="text-xs text-gray-500 font-medium">Rekor Hari</div>
                </div>
            </div>

            <div class="dash-card p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $lessonsCompleted ?? 0 }}</div>
                    <div class="text-xs text-gray-500 font-medium">Pelajaran Selesai</div>
                </div>
            </div>
        </div>

        {{-- ── MAIN CONTENT ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Left Column (3/5) --}}
            <div class="lg:col-span-3 space-y-6">

                {{-- ── GUIDED LEARNING CARD ── --}}
                <div class="dash-card p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-base font-bold text-gray-900">Alur Belajar Terpandu</h2>
                        @if($heroCTAState === 'active' && $learningSession)
                            <span class="text-[11px] font-semibold px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full">Sedang Berlangsung</span>
                        @elseif($heroCTAState === 'completed')
                            <span class="text-[11px] font-semibold px-2.5 py-1 bg-green-50 text-green-600 rounded-full">Selesai</span>
                        @else
                            <span class="text-[11px] font-semibold px-2.5 py-1 bg-gray-100 text-gray-500 rounded-full">Belum Dimulai</span>
                        @endif
                    </div>

                    {{-- Step visualization --}}
                    @php
                        $sessionStatus = $learningSession->status ?? 'none';
                        $stepDefs = [
                            ['key' => 'pretest',   'label' => 'Pre-test',  'desc' => 'Deteksi level awal', 'icon' => '📝',
                             'done' => in_array($sessionStatus, ['pretest_done','guidebook_done','posttest_done','completed']),
                             'active' => $sessionStatus === 'not_started'],
                            ['key' => 'guidebook', 'label' => 'Panduan',   'desc' => 'Pelajari materi',    'icon' => '📖',
                             'done' => in_array($sessionStatus, ['guidebook_done','posttest_done','completed']),
                             'active' => $sessionStatus === 'pretest_done'],
                            ['key' => 'posttest',  'label' => 'Post-test', 'desc' => 'Uji pemahaman',      'icon' => '✅',
                             'done' => in_array($sessionStatus, ['posttest_done','completed']),
                             'active' => $sessionStatus === 'guidebook_done'],
                            ['key' => 'result',    'label' => 'Hasil',     'desc' => 'Lihat perkembangan', 'icon' => '🏆',
                             'done' => $sessionStatus === 'completed',
                             'active' => $sessionStatus === 'posttest_done'],
                        ];
                    @endphp

                    <div class="flex items-start gap-0 mb-6">
                        @foreach($stepDefs as $i => $s)
                            <div class="flex flex-col items-center {{ $i < count($stepDefs)-1 ? 'flex-1' : '' }}">
                                <div class="flex items-center w-full">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all flex-shrink-0
                                        {{ $s['done']   ? 'bg-blue-600 text-white shadow-md' : '' }}
                                        {{ $s['active'] && !$s['done'] ? 'bg-blue-600 text-white ring-4 ring-blue-100' : '' }}
                                        {{ !$s['done'] && !$s['active'] ? 'bg-gray-100 text-gray-400' : '' }}">
                                        @if($s['done'])
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <span>{{ $i+1 }}</span>
                                        @endif
                                    </div>
                                    @if($i < count($stepDefs)-1)
                                        <div class="flex-1 h-0.5 mx-1.5 rounded {{ $s['done'] ? 'bg-blue-500' : 'bg-gray-200' }}"></div>
                                    @endif
                                </div>
                                <div class="mt-2 text-center px-1">
                                    <div class="text-xs font-semibold {{ $s['active'] && !$s['done'] ? 'text-blue-600' : ($s['done'] ? 'text-blue-500' : 'text-gray-400') }}">
                                        {{ $s['label'] }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 leading-tight mt-0.5 hidden sm:block">{{ $s['desc'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- CTA button --}}
                    <a href="{{ route('learning.start') }}"
                       class="flex items-center justify-between gap-4 p-4 rounded-xl
                           {{ $heroCTAState === 'completed' ? 'bg-gray-50 border border-gray-200' : 'bg-blue-600 text-white' }}
                           hover:opacity-90 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full {{ $heroCTAState === 'completed' ? 'bg-gray-200' : 'bg-white/20' }} flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 {{ $heroCTAState === 'completed' ? 'text-gray-600' : '' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <div>
                                <div class="font-bold text-sm {{ $heroCTAState === 'completed' ? 'text-gray-800' : '' }}">
                                    @if($heroCTAState === 'active') Lanjutkan Belajar
                                    @elseif($heroCTAState === 'completed') Belajar Lagi
                                    @else Mulai Belajar
                                    @endif
                                </div>
                                <div class="text-xs {{ $heroCTAState === 'completed' ? 'text-gray-500' : 'text-blue-100' }}">
                                    Pre-test → Panduan → Post-test → Hasil
                                </div>
                            </div>
                        </div>
                        <svg class="w-4 h-4 {{ $heroCTAState === 'completed' ? 'text-gray-400' : 'text-blue-200' }} group-hover:translate-x-1 transition-transform flex-shrink-0"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

                {{-- Recent Activity --}}
                <div class="dash-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-gray-900">Aktivitas Terakhir</h2>
                        <a href="{{ route('attempts.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                            Lihat semua →
                        </a>
                    </div>

                    @if(empty($recentAttempts) || count($recentAttempts) === 0)
                        <div class="text-center py-8">
                            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <p class="text-sm text-gray-400 mb-3">Belum ada aktivitas belajar.</p>
                            <a href="{{ route('learning.start') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                                Mulai Sekarang →
                            </a>
                        </div>
                    @else
                        <div class="divide-y divide-gray-50">
                            @foreach($recentAttempts as $a)
                                <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                    <div class="flex-shrink-0">
                                        @if(($a['passed'] ?? false) === true)
                                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate">{{ $a['lesson'] ?? '-' }}</div>
                                        <div class="text-xs text-gray-400">{{ $a['date'] ?? '-' }}</div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="text-sm font-bold text-gray-700">{{ $a['score'] ?? 0 }}%</span>
                                        @if(($a['passed'] ?? false) === true)
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

                {{-- Performance --}}
                <div class="dash-card p-6">
                    <h2 class="text-base font-bold text-gray-900 mb-5">Performa</h2>
                    <div class="space-y-5">
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="text-gray-500">Akurasi</span>
                                <span class="font-bold text-gray-900">{{ $avgScore ?? 0 }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="h-2 rounded-full bg-blue-500 transition-all duration-700" style="width: {{ max(0, min(100, $avgScore ?? 0)) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="text-gray-500">Tingkat Kelulusan</span>
                                <span class="font-bold text-gray-900">{{ $passRate ?? 0 }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="h-2 rounded-full bg-blue-400 transition-all duration-700" style="width: {{ max(0, min(100, $passRate ?? 0)) }}%"></div>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-gray-50">
                            <div class="text-xs text-gray-400">{{ $totalAttempts ?? 0 }} total percobaan</div>
                        </div>
                    </div>
                </div>

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

                {{-- Topic Mastery --}}
                @if(!empty($categoryPerformance) && count($categoryPerformance) > 0)
                    <div class="dash-card p-6">
                        <h2 class="text-base font-bold text-gray-900 mb-4">Penguasaan Materi</h2>
                        <div class="space-y-3.5">
                            @foreach($categoryPerformance as $c)
                                @php($pct = (int) ($c['percent'] ?? 0))
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600 truncate mr-2">{{ $c['name'] ?? 'Materi' }}</span>
                                        <span class="font-bold text-gray-700">{{ $pct }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full bg-blue-500 transition-all duration-500" style="width: {{ max(0, min(100, $pct)) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>

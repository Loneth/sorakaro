<div class="min-h-screen bg-gray-50">
    {{-- Mobile topbar --}}
    <header class="sticky top-0 z-30 bg-white border-b border-gray-100 sm:hidden">
        <div class="px-4 h-14 flex items-center justify-between">
            <button type="button"
                    class="inline-flex items-center p-2 text-gray-600 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    data-drawer-target="sidebar"
                    data-drawer-toggle="sidebar"
                    aria-controls="sidebar">
                <span class="sr-only">Open sidebar</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <a href="{{ route('dashboard') }}" class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Sorakaro" class="h-10 md:h-12 w-auto object-contain">
            </a>

            <button type="button"
                    class="inline-flex items-center p-2 text-gray-600 rounded-lg hover:bg-gray-100"
                    data-dropdown-toggle="user-dropdown-mobile"
                    data-dropdown-placement="bottom-end">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                    <span class="text-blue-700 font-bold text-xs">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            </button>

            <div id="user-dropdown-mobile"
                 class="hidden z-50 my-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100">
                <div class="px-4 py-3 border-b border-gray-100">
                    <div class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
                </div>
                <ul class="py-1">
                    <li>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profil</a>
                    </li>
                </ul>
                <div class="py-1 border-t border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="flex">
        {{-- Sidebar --}}
        <aside id="sidebar"
               class="fixed top-0 left-0 z-40 w-60 h-screen bg-blue-700 transition-transform -translate-x-full sm:translate-x-0"
               aria-label="Sidebar">

            <div class="h-full flex flex-col">
                {{-- Brand --}}
                <div class="px-5 py-6">
                    <a href="{{ route('dashboard') }}" class="hidden sm:flex items-center justify-center">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Sorakaro" class="h-20 w-auto object-contain">
                    </a>
                </div>

                {{-- Nav --}}
                <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-item {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : 'sidebar-item-idle' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <div class="flex flex-col items-start leading-tight">
                            <span class="font-medium">Dashboard</span>
                            <span class="text-[10px] italic opacity-70">dasbor</span>
                        </div>
                    </a>
                    {{-- Mulai Belajar → level selection --}}
                    <a href="{{ route('learn.index') }}"
                       class="sidebar-item {{ request()->routeIs('learn.*', 'learning.*') ? 'sidebar-item-active' : 'sidebar-item-idle' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex flex-col items-start leading-tight">
                            <span class="font-medium">Mulai Belajar</span>
                            <span class="text-[10px] italic opacity-70">mulaierlajar</span>
                        </div>
                    </a>

                    {{-- Kamus Bahasa Karo --}}
                    <a href="{{ route('dictionary.index') }}"
                       class="sidebar-item {{ request()->routeIs('dictionary.*') ? 'sidebar-item-active' : 'sidebar-item-idle' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <div class="flex flex-col items-start leading-tight">
                            <span class="font-medium">Kamus</span>
                            <span class="text-[10px] italic opacity-70">kamus karo</span>
                        </div>
                    </a>


                    <a href="{{ route('attempts.index') }}"
                       class="sidebar-item {{ request()->routeIs('attempts.*') ? 'sidebar-item-active' : 'sidebar-item-idle' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <div class="flex flex-col items-start leading-tight">
                            <span class="font-medium">Progres Saya</span>
                            <span class="text-[10px] italic opacity-70">kemajunku</span>
                        </div>
                    </a>

                    <a href="{{ route('leaderboard.index') }}"
                       class="sidebar-item {{ request()->routeIs('leaderboard.*') ? 'sidebar-item-active' : 'sidebar-item-idle' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="flex flex-col items-start leading-tight">
                            <span class="font-medium">Papan Peringkat</span>
                            <span class="text-[10px] italic opacity-70">tingkat nilai</span>
                        </div>
                    </a>
                </nav>


                {{-- Bottom --}}
                <div class="px-3 py-4 border-t border-white/10 space-y-0.5">
                    <a href="{{ route('profile.edit') }}"
                       class="sidebar-item {{ request()->routeIs('profile.*') ? 'sidebar-item-active' : 'sidebar-item-idle' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <div class="flex flex-col items-start leading-tight">
                            <span class="font-medium">Profil</span>
                            <span class="text-[10px] italic opacity-70">profil</span>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="sidebar-item w-full sidebar-item-idle hover:!text-red-200">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <div class="flex flex-col items-start leading-tight">
                                <span class="font-medium">Keluar</span>
                                <span class="text-[10px] italic opacity-70">ndarat</span>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Content --}}
        <div class="flex-1 sm:ml-60 min-w-0">
            @isset($header)
                <header class="hidden sm:block bg-white border-b border-gray-100">
                    <div class="max-w-6xl mx-auto px-6 lg:px-8 py-4">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="max-w-6xl mx-auto px-6 lg:px-8 py-8 animate-fade-in">
                {{ $slot }}
            </main>
        </div>
    </div>
</div>

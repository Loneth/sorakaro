@php
    $isDashboard = request()->routeIs('dashboard');
    $isLearn = request()->routeIs('learn.*');
@endphp

<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between py-3">
            {{-- Brand --}}
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <x-application-logo class="h-16 w-auto object-contain" />
            </a>

            {{-- Right controls --}}
            <div class="flex items-center gap-2 md:order-2">
                {{-- User dropdown trigger --}}
                <button type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200"
                        id="user-menu-button"
                        data-dropdown-toggle="user-dropdown"
                        data-dropdown-placement="bottom-end">
                    <span class="text-xl leading-none">{{ Auth::user()->getAvatarIcon() }}</span>
                    <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="m19 9-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown menu --}}
                <div id="user-dropdown"
                     class="hidden z-50 my-2 w-56 bg-white rounded-xl shadow border border-gray-100">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</div>
                        <div class="text-sm text-gray-500 truncate">{{ Auth::user()->email }}</div>
                    </div>
                    <ul class="py-2">
                        <li>
                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Profile
                            </a>
                        </li>
                    </ul>
                    <div class="py-2 border-t border-gray-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Mobile menu button --}}
                <button data-collapse-toggle="navbar-main"
                        type="button"
                        class="inline-flex items-center p-2 w-10 h-10 justify-center text-gray-500 rounded-lg md:hidden hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200"
                        aria-controls="navbar-main"
                        aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 7h14M5 12h14M5 17h14"/>
                    </svg>
                </button>
            </div>

            {{-- Main nav links --}}
            <div class="hidden w-full md:flex md:w-auto md:order-1" id="navbar-main">
                <ul class="flex flex-col mt-4 md:mt-0 md:flex-row md:items-center md:gap-2 font-medium">
                    <li>
                        <a href="{{ route('dashboard') }}"
                           class="block px-4 py-2 rounded-lg md:py-2
                                  {{ $isDashboard ? 'text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('learn.index') }}"
                           class="block px-4 py-2 rounded-lg md:py-2
                                  {{ $isLearn ? 'text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            Learn
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

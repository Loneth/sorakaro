<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumb :items="[
            ['label' => 'Dasbor', 'url' => route('dashboard')],
            ['label' => 'Tingkat']
        ]" />
    </x-slot>

    <div class="pt-4 pb-8">
        <x-ui.container>

            @if (session('error'))
                <div class="p-3 rounded bg-red-100 text-red-800 mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="p-3 rounded bg-green-100 text-green-800 mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-6">
                @foreach ($levels as $level)
                    @php
                        $isUnlocked = $user->hasUnlockedLevel($level);
                    @endphp
                    
                    <x-ui.card class="{{ !$isUnlocked ? 'opacity-60' : '' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <x-ui.section-title :level="2">{{ $level->name }}</x-ui.section-title>
                                </div>
                                
                                @if ($level->description)
                                    <div class="text-sm text-gray-600">{{ $level->description }}</div>
                                @endif
                                
                                @if (!$isUnlocked)
                                    <div class="text-xs text-gray-500 mt-2">
                                        Selesaikan tingkat sebelumnya untuk membuka kuncian
                                    </div>
                                @endif
                            </div>
                            
                            @if ($isUnlocked)
                                <x-ui.button variant="primary" :href="route('learning.start.level', $level->id)">
                                    Pilih
                                </x-ui.button>
                            @else
                                <x-ui.button variant="disabled" disabled>
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    Terkunci
                                </x-ui.button>
                            @endif
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

        </x-ui.container>
    </div>
</x-app-layout>

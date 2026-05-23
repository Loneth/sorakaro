<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Nama --}}
        <div>
            <x-input-label for="name">
                <div class="flex flex-col leading-tight">
                    <span>Nama</span>
                    <span class="text-[10px] italic opacity-60 font-normal">Gelarndu</span>
                </div>
            </x-input-label>
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div class="mt-4">
            <x-input-label for="email">
                <div class="flex flex-col leading-tight">
                    <span>Email</span>
                    <span class="text-[10px] italic opacity-60 font-normal">Email</span>
                </div>
            </x-input-label>
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Jenis Kelamin --}}
        <div class="mt-4">
            <x-input-label for="gender">
                <div class="flex flex-col leading-tight">
                    <span>Jenis Kelamin</span>
                    <span class="text-[10px] italic opacity-60 font-normal">tading-tading</span>
                </div>
            </x-input-label>
            <div class="mt-2 grid grid-cols-2 gap-6 max-w-xs">
                <label class="inline-flex items-center">
                    <input type="radio" name="gender" value="male" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" {{ old('gender') === 'male' ? 'checked' : '' }} required>
                    <span class="ml-2 text-gray-700">
                        <span class="block text-sm font-medium">Laki-laki</span>
                        <span class="block text-[10px] italic opacity-60">Dilaki</span>
                    </span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="gender" value="female" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" {{ old('gender') === 'female' ? 'checked' : '' }} required>
                    <span class="ml-2 text-gray-700">
                        <span class="block text-sm font-medium">Perempuan</span>
                        <span class="block text-[10px] italic opacity-60">Diberu</span>
                    </span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>

        {{-- Umur --}}
        <div class="mt-4">
            <x-input-label for="age">
                <div class="flex flex-col leading-tight">
                    <span>Umur <span class="text-gray-400 font-normal">(Opsional)</span></span>
                    <span class="text-[10px] italic opacity-60 font-normal">Umur</span>
                </div>
            </x-input-label>
            <x-text-input id="age" class="block mt-1 w-full" type="number" min="5" max="100" name="age" :value="old('age')" autocomplete="age" />
            <x-input-error :messages="$errors->get('age')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div class="mt-4">
            <x-input-label for="password">
                <div class="flex flex-col leading-tight">
                    <span>Kata Sandi</span>
                    <span class="text-[10px] italic opacity-60 font-normal">Pasword</span>
                </div>
            </x-input-label>

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Konfirmasi Password --}}
        <div class="mt-4">
            <x-input-label for="password_confirmation">
                <div class="flex flex-col leading-tight">
                    <span>Konfirmasi Kata Sandi</span>
                    <span class="text-[10px] italic opacity-60 font-normal">Konfirmasi Pasword</span>
                </div>
            </x-input-label>

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                Sudah punya akun?
            </a>

            <x-primary-button class="ms-4">
                <div class="flex flex-col items-center leading-tight">
                    <span>Daftar</span>
                    <span class="text-[10px] italic opacity-70 font-normal">daftar</span>
                </div>
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

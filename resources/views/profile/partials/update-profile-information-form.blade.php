<section x-data="{ gender: '{{ old('gender', $user->gender) }}' }">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            <div class="flex flex-col leading-tight">
                <span>Informasi Profil</span>
                <span class="text-[11px] italic opacity-60 font-normal">keterangen diri</span>
            </div>
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Perbarui informasi profil dan alamat email akun kamu.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Nama --}}
        <div>
            <x-input-label for="name">
                <div class="flex flex-col leading-tight">
                    <span>Nama</span>
                    <span class="text-[10px] italic opacity-60 font-normal">Gelarndu</span>
                </div>
            </x-input-label>
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email">
                <div class="flex flex-col leading-tight">
                    <span>Email</span>
                    <span class="text-[10px] italic opacity-60 font-normal">Email</span>
                </div>
            </x-input-label>
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        Email kamu belum diverifikasi.

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Klik di sini untuk mengirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            Tautan verifikasi baru telah dikirim ke alamat email kamu.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Jenis Kelamin --}}
        <div>
            <x-input-label for="gender">
                <div class="flex flex-col leading-tight">
                    <span>Jenis Kelamin</span>
                    <span class="text-[10px] italic opacity-60 font-normal">tading-tading</span>
                </div>
            </x-input-label>
            <div class="mt-2 grid grid-cols-2 gap-6 max-w-xs">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="gender" value="male" x-model="gender" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" required>
                    <span class="ml-2 text-gray-700">
                        <span class="block text-sm font-medium">Laki-laki</span>
                        <span class="block text-[10px] italic opacity-60">Dilaki</span>
                    </span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="gender" value="female" x-model="gender" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" required>
                    <span class="ml-2 text-gray-700">
                        <span class="block text-sm font-medium">Perempuan</span>
                        <span class="block text-[10px] italic opacity-60">Diberu</span>
                    </span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('gender')" />
        </div>

        {{-- Umur --}}
        <div>
            <x-input-label for="age">
                <div class="flex flex-col leading-tight">
                    <span>Umur <span class="text-gray-400 font-normal">(Opsional)</span></span>
                    <span class="text-[10px] italic opacity-60 font-normal">Umur</span>
                </div>
            </x-input-label>
            <x-text-input id="age" name="age" type="number" min="5" max="100" class="mt-1 block w-full" :value="old('age', $user->age)" autocomplete="age" />
            <x-input-error class="mt-2" :messages="$errors->get('age')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>
                <div class="flex flex-col items-center leading-tight">
                    <span>Simpan</span>
                    <span class="text-[10px] italic opacity-70 font-normal">simpa</span>
                </div>
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>

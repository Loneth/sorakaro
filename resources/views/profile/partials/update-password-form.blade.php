<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            <div class="flex flex-col leading-tight">
                <span>Perbarui Kata Sandi</span>
                <span class="text-[11px] italic opacity-60 font-normal">ganti pasword</span>
            </div>
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Pastikan akun kamu menggunakan kata sandi yang panjang dan acak agar tetap aman.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        {{-- Password Saat Ini --}}
        <div>
            <x-input-label for="update_password_current_password">
                <div class="flex flex-col leading-tight">
                    <span>Kata Sandi Saat Ini</span>
                    <span class="text-[10px] italic opacity-60 font-normal">pasword si lit</span>
                </div>
            </x-input-label>
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        {{-- Password Baru --}}
        <div>
            <x-input-label for="update_password_password">
                <div class="flex flex-col leading-tight">
                    <span>Kata Sandi Baru</span>
                    <span class="text-[10px] italic opacity-60 font-normal">pasword si mbaru</span>
                </div>
            </x-input-label>
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        {{-- Konfirmasi Password Baru --}}
        <div>
            <x-input-label for="update_password_password_confirmation">
                <div class="flex flex-col leading-tight">
                    <span>Konfirmasi Kata Sandi</span>
                    <span class="text-[10px] italic opacity-60 font-normal">konfirmasi pasword</span>
                </div>
            </x-input-label>
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>
                <div class="flex flex-col items-center leading-tight">
                    <span>Simpan</span>
                    <span class="text-[10px] italic opacity-70 font-normal">simpa</span>
                </div>
            </x-primary-button>

            @if (session('status') === 'password-updated')
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

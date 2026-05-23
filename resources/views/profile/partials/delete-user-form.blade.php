<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            <div class="flex flex-col leading-tight">
                <span>Hapus Akun</span>
                <span class="text-[11px] italic opacity-60 font-normal">busur kin akun</span>
            </div>
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Setelah akun kamu dihapus, semua data dan informasi akan dihapus secara permanen. Sebelum menghapus akun, harap unduh data yang ingin kamu simpan.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <div class="flex flex-col items-center leading-tight">
            <span>Hapus Akun</span>
            <span class="text-[10px] italic opacity-70 font-normal">busur akun</span>
        </div>
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                <div class="flex flex-col leading-tight">
                    <span>Yakin ingin menghapus akun?</span>
                    <span class="text-[11px] italic opacity-60 font-normal">lit ka nge busur akunndu?</span>
                </div>
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Setelah akun dihapus, semua data akan hilang secara permanen. Masukkan kata sandi kamu untuk mengonfirmasi penghapusan akun.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Kata Sandi" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Kata Sandi"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    <div class="flex flex-col items-center leading-tight">
                        <span>Hapus Akun</span>
                        <span class="text-[10px] italic opacity-70 font-normal">busur akun</span>
                    </div>
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>

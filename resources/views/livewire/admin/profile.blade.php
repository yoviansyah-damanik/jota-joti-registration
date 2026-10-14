<div>
    <x-ui.page-header :title="__('Edit Profile')" :subtitle="__('Kelola informasi profil akun dan kata sandi admin')" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kartu Info Ringkas Akun --}}
        <div class="lg:col-span-1">
            <x-ui.card :title="__('Informasi Akun')" icon="user">
                <div class="flex flex-col items-center text-center py-4">
                    <div class="bg-primary text-primary-content flex size-20 items-center justify-center rounded-full text-2xl font-bold shadow-md ring-4 ring-primary/20">
                        {{ \Illuminate\Support\Str::of($user?->name)->substr(0, 1)->upper() }}
                    </div>
                    <h3 class="mt-4 text-base font-bold">{{ $user?->name }}</h3>
                    <p class="text-base-content/60 text-sm">{{ $user?->username ? '@'.$user->username : '-' }}</p>
                    <span class="badge badge-primary badge-sm mt-2 font-medium">Administrator</span>
                </div>

                <div class="border-base-200 mt-2 space-y-3 border-t pt-4 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-base-content/60">{{ __('Email') }}</span>
                        <span class="font-medium truncate max-w-[180px]">{{ $user?->email }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-base-content/60">{{ __('Bergabung') }}</span>
                        <span class="font-medium">{{ $user?->created_at?->translatedFormat('d F Y') ?? '-' }}</span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Form Edit Data & Keamanan --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Form Informasi Profil --}}
            <form wire:submit="updateProfile">
                <x-ui.card :title="__('Data Pribadi')" icon="user">
                    <div class="space-y-4">
                        <x-ui.input
                            :label="__('Nama Lengkap')"
                            name="name"
                            wire:model="name"
                            required
                        />

                        <x-ui.input
                            :label="__('Username')"
                            name="username"
                            wire:model="username"
                            :hint="__('Digunakan untuk login ke panel admin.')"
                            required
                        />

                        <x-ui.input
                            :label="__('Alamat Email')"
                            name="email"
                            type="email"
                            wire:model="email"
                            required
                        />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="updateProfile" class="loading loading-spinner loading-xs"></span>
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </x-ui.card>
            </form>

            {{-- Form Ubah Kata Sandi --}}
            <form wire:submit="updatePassword">
                <x-ui.card :title="__('Ubah Kata Sandi')" icon="key">
                    <div class="space-y-4">
                        <x-ui.input
                            :label="__('Kata Sandi Saat Ini')"
                            name="current_password"
                            type="password"
                            wire:model="current_password"
                            required
                        />

                        <x-ui.input
                            :label="__('Kata Sandi Baru')"
                            name="password"
                            type="password"
                            wire:model="password"
                            :hint="__('Minimal 8 karakter.')"
                            required
                        />

                        <x-ui.input
                            :label="__('Konfirmasi Kata Sandi Baru')"
                            name="password_confirmation"
                            type="password"
                            wire:model="password_confirmation"
                            required
                        />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn btn-neutral" wire:loading.attr="disabled">
                            <span wire:loading wire:target="updatePassword" class="loading loading-spinner loading-xs"></span>
                            {{ __('Perbarui Kata Sandi') }}
                        </button>
                    </div>
                </x-ui.card>
            </form>
        </div>
    </div>
</div>

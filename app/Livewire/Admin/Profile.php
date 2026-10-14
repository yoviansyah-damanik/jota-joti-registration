<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Profile extends Component
{
    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Inisialisasi data profil pengguna yang sedang login.
     */
    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->name = (string) $user->name;
        $this->username = (string) ($user->username ?? '');
        $this->email = (string) $user->email;
    }

    /**
     * Perbarui informasi nama, username, dan email.
     */
    public function updateProfile(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'alpha_dash',
                'min:3',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda strip, dan garis bawah.',
            'username.unique' => 'Username ini sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
        ]);

        $user->update($validated);

        $this->dispatch('toast', message: __('Profil berhasil diperbarui.'));
    }

    /**
     * Perbarui kata sandi akun dengan memverifikasi kata sandi saat ini.
     */
    public function updatePassword(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user->update([
            'password' => $this->password,
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->dispatch('toast', message: __('Kata sandi berhasil diperbarui.'));
    }

    public function render()
    {
        return view('livewire.admin.profile', [
            'user' => Auth::user(),
        ])->title(__('Edit Profile'));
    }
}

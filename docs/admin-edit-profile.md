# Admin Edit Profile

Halaman edit profile memungkinkan pengguna admin mengelola informasi akun dan kata sandi.

## Fitur
1. **Data Pribadi**:
   - Memperbarui Nama Lengkap, Username, dan Email.
   - Validasi keunikan username dan email mengabaikan ID user yang sedang login.
2. **Ubah Kata Sandi**:
   - Verifikasi kata sandi saat ini (`current_password`).
   - Kata sandi baru minimal 8 karakter dengan konfirmasi.
3. **Akses Cepat**:
   - Menu **Edit Profile** di sidebar.
   - Kartu info profil pengguna di footer sidebar dapat langsung diklik untuk membuka halaman profil.

## Komponen & Rute
- **Livewire**: `App\Livewire\Admin\Profile`
- **View**: `resources/views/livewire/admin/profile.blade.php`
- **Route**: `GET /admin/profile` (name: `admin.profile`)
- **Test**: `tests/Feature/AdminProfileTest.php`

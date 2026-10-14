# Global Wizard Reset (Reset Pendaftaran)

- **Tujuan**:
  - Memberikan kemampuan bagi pendaftar untuk mereset seluruh formulir dan mengulang proses pendaftaran dari awal kapan saja.
- **Komponen & View**:
  - Livewire Component: `App\Livewire\Public\Registration\Wizard`
  - View Blade: `resources/views/livewire/public/registration/wizard.blade.php`
- **Fitur & Penempatan**:
  1. **Header Formulir**: Terletak di sisi kanan judul formulir pendaftaran (`x-ui.page-header`) sehingga mudah diakses pada setiap langkah registrasi.
  2. **Panel Navigasi Desktop**: Terletak pada bagian bawah rail langkah pendaftaran (`aside`).
  3. **Aksi (`resetDraft`)**:
     - Menghapus seluruh draft pendaftaran dari sesi dan berkas sementara yang diunggah (`RegistrationDraft::clear()`).
     - Mengembalikan alur wizard ke Langkah 1 (`$this->step = 1`).
     - Menampilkan notifikasi toast info dan mengarahkan kembali ke `/daftar`.

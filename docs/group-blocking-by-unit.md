# Group Blocking by Unit (Pemisahan Nomor Grup Sesuai Golongan)

- **Konsep**:
  - Penggalang dan Penegak/Pandega memiliki blok nomor grup terpisah dan **tidak bercampur** dalam satu grup.
  - Setiap gudep hanya mendistribusikan pesertanya ke nomor grup yang dialokasikan untuk golongannya.
- **Konfigurasi Pengaturan (`/admin/settings`)**:
  - `penggalang_group_count`: Jumlah grup Penggalang (default: 5).
  - `penggalang_start_number`: Nomor awal grup Penggalang (default: 1).
  - `penegak_group_count`: Jumlah grup Penegak/Pandega (default: 5).
  - `penegak_start_number`: Nomor awal grup Penegak/Pandega (default: otomatis setelah Penggalang).
- **Komponen & File Terkait**:
  - `App\Services\SettingService`: Menyediakan rentang grup per golongan (`penggalangGroups()`, `penegakGroups()`, `groupsForUnit()`, `unitForGroup()`).
  - `App\Services\GroupAssignmentService`:
    - Mengelompokkan gudep berdasarkan `troop->unit`.
    - Menugaskan peserta Penggalang hanya ke nomor grup Penggalang, dan Penegak hanya ke nomor grup Penegak.
    - Evaluasi keseimbangan persebaran dilakukan per blok golongan secara independen.
  - `App\Livewire\Admin\Settings` & Blade: Konfigurasi blok dan visualisasi rentang grup.
  - `App\Livewire\Admin\Groups\Assign` & Blade: Ringkasan blok grup Penggalang vs Penegak.
  - `App\Livewire\Admin\Groups\BalanceReport` & Blade: Indikator golongan pada tiap grup.
  - `App\Livewire\Admin\Groups\Details` & Blade: Matriks persebaran dengan header bertingkat per golongan dan shading otomatis untuk kolom grup di luar golongan gudep.

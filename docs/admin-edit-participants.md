# Admin Edit Participants (Edit Data Peserta pada Menu Registrasi)

- **Komponen & View**:
  - Livewire Component: `App\Livewire\Admin\Registrations\ParticipantTable`
  - Blade: `resources/views/livewire/admin/registrations/participant-table.blade.php`
- **Fitur Edit**:
  - Tombol edit (ikon pensil) pada setiap baris peserta di tabel rincian registrasi gudep (`/admin/registrations/{troop}`).
  - Modal interaktif untuk mengubah:
    - **Nama Lengkap** (`full_name`)
    - **Tanggal Lahir** (`birth_date`)
    - **Jenis Kelamin** (`gender`: L / P)
    - **Nomor KTA/NTA** (`kta_number`: opsional, dengan validasi duplikasi mengecualikan ID peserta yang sedang diedit)
    - **Nomor Grup** (`group_number`: pilihan dinamis sesuai golongan gudep Penggalang / Penegak, atau *Unassigned*)
- **Event & Reaktivitas**:
  - Menyimpan perubahan dan mengirim notifikasi toast serta memicu event `troop-participants-updated`.

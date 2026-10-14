# Version Release Notes

## Release v1.5.0 (2026-10-14)

### Major Enhancements & New Features

1. **Admin Edit Profile & Account Security (`/admin/profile`)**:
   - **Data Pribadi**: Memperbarui Nama Lengkap, Username, dan Email akun admin dengan validasi format dan keunikan yang aman mengabaikan ID admin yang sedang aktif.
   - **Ubah Kata Sandi**: Penggantian kata sandi dengan verifikasi kata sandi saat ini (`current_password`) serta validasi konfirmasi kata sandi baru (minimal 8 karakter).
   - **Navigasi Cepat Sidebar**: Menambahkan item menu **Edit Profile** dan tautan interaktif pada kartu profil pengguna di bagian footer sidebar admin.
   - **Feedback Notifikasi**: Toast notification instan saat data profil atau kata sandi berhasil diperbarui.

2. **Penyempurnaan Integrasi Penilaian Tugas**:
   - Memastikan rute `/admin/tasks` dan `/admin/profile` masuk ke dalam pengujian akses seluruh halaman admin.

### Test Coverage
- 91 automated feature and unit tests passing (`100% pass`).

---

## Release v1.4.0 (2026-10-14)

### Major Enhancements & New Features

1. **Task Submission & Assessment System (Pengiriman & Penilaian Tugas JOTA-JOTI)**:
   - **Form Pengiriman Tugas (`/tugas`)**: Peserta dapat mengirimkan laporan tugas dengan memasukkan ID Peserta (verifikasi otomatis), JID, JJID, berkas PDF Tugas JOTA Digital (maks. 10 MB), berkas PDF Tugas JOTI (maks. 10 MB), dan catatan opsional. Mendukung pengiriman baru maupun perbaikan tugas revisi.
   - **Halaman Pemantauan Tugas (`/tugas/status`)**: Peserta dapat mengecek evaluasi tugas secara mandiri berdasarkan ID Peserta, melihat status Tugas JOTA Digital, Tugas JOTI, Status Akhir, catatan evaluasi, serta tautan berkas tugas.
   - **Halaman Penilaian Tugas Admin (`/admin/tasks`)**: Panel admin untuk mengevaluasi tugas, meninjau berkas PDF secara langsung, serta memberikan status *Ditolak*, *Revisi*, atau *Selesai* pada Tugas JOTA Digital, Tugas JOTI, dan penetapan Status Akhir.
   - **Pengunduhan Aman Dokumen Tugas (`/tugas/dokumen/{submission}/{type}`)**: Dilindungi hak akses admin atau otentikasi ID Peserta yang bersangkutan.

2. **Perbaikan Transisi Langkah Peserta Manual**:
   - Memperbaiki `RegistrationDraft::isStepComplete(2)` agar peserta yang ditambahkan secara manual tanpa melampirkan berkas Excel dapat langsung melanjutkan ke Langkah 3 dan menyelesaikan registrasi.

3. **Fitur Reset Pendaftaran Global pada Wizard**:
   - Tombol **Reset Pendaftaran** diletakkan secara global pada header wizard dan panel samping desktop, dengan tampilan *highlight* aksen error/rose, efek interaktif animasi putar ikon, dan dialog konfirmasi keamanan browser.

### Test Coverage
- 84 automated feature and unit tests passing (`100% pass`).

---

## Release v1.3.0 (2026-10-14)

### Major Enhancements & Updates

1. **Flexible Participant Registration Count (Pendaftaran Jumlah Peserta Bebas)**:
   - Batasan minimal 5 peserta dihapus. Gugusdepan kini dapat mendaftar dengan jumlah peserta berapapun (termasuk 1 atau 2 peserta, minimal 1).
   - Penyesuaian kalkulasi total biaya pendaftaran dan distribusi penugasan nomor grup tetap proporsional.
   - Pembaruan teks panduan pendaftaran pada wizard dan halaman syarat registrasi publik.

2. **Admin Participant Management (Edit Data Peserta pada Menu Registrasi)**:
   - Admin dapat langsung mengedit data peserta dari tabel registrasi gudep (`/admin/registrations/{troop}`).
   - Modal interaktif pengeditan Nama Lengkap, Tanggal Lahir, Jenis Kelamin (L/P), Nomor KTA/NTA (opsional), dan Nomor Grup (sesuai alokasi golongan Penggalang / Penegak).
   - Validasi duplikasi KTA/NTA yang aman mengecualikan data peserta yang sedang diedit.

3. **Manual Attendance Sheet Refinements (Penyempurnaan Lembar Absensi Manual)**:
   - Format tanggal otomatis terisi sesuai tanggal sesi kegiatan (contoh: "Padangsidimpuan, 17 Oktober 2026").
   - Penandatangan lembar absensi disesuaikan menjadi "Administrator Absensi" dengan garis tanda tangan ringkas tanpa kurung.

### Test Coverage
- 77 automated feature and unit tests passing (`100% pass`).

---

## Release v1.2.0 (2026-10-14)

### Major Enhancements & New Features

1. **Security & Data Privacy (UUID Migration)**:
   - Registration and public card links use encrypted/UUID tokens.
   - Sensitive uploads (proof of payment, assignment letters) protected by UUID identifiers.

2. **Admin Authentication & Registration Controls**:
   - Login uses `username` (fallback to email).
   - Admin can manually add participants during registration.
   - NTA / KTA field is optional (can be left blank).

3. **Seeder Environment Rules**:
   - `local` / `dev`: Seeds complete sample dataset (users, troops, participants, payments, attendance).
   - `production`: Seeds admin account only.

4. **Official Branding & Logo**:
   - Official JOTA-JOTI logo upload and management in `/admin/settings`.
   - Public homepage section with high-resolution and transparent mascot logo download.

5. **Group Blocking by Scout Unit (Pemisahan Golongan)**:
   - Strict unit blocking: Penggalang (e.g. Groups 1–5) and Penegak/Pandega (e.g. Groups 6–10) are strictly segregated.
   - Dynamic settings in admin panel with realtime range calculation.
   - Detailed matrix breakdown view (`/admin/groups/details`) with unit-level super headers and shaded non-applicable group cells.

6. **Manual Attendance Printing (Cetak Absensi Manual)**:
   - Dedicated print sheet endpoint (`/admin/print/attendance-manual`).
   - Default 1 page per troop (`group_by=troop`) with page breaks.
   - Standard 20 rows per sheet (options: 15, 20, 25), automatically filling remaining rows with blank numbered signature slots for walk-in participants.
   - Scout standard staggered signatures (`1. ... / 2. ...`), attendance recap, and leader/instructor verification block.

### Test Coverage
- 74 automated feature and unit tests passing (`100% pass`).

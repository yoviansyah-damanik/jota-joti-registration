# Version Release Notes

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

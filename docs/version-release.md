# Version Release Notes

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

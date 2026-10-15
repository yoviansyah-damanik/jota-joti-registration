# Certificate Settings & Task Completion Eligibility

## Overview
Allows administrators to upload certificate template images (A4 landscape) and visually position text fields, and restricts participant certificate issuance strictly to participants who have passed their uploaded tasks (`TaskStatus::Completed`).

## Key Changes
1. **Certificate Service Eligibility (`CertificateService.php`)**:
   - `eligibleParticipants(Troop $troop)` queries participants where `taskSubmission` has `final_status === TaskStatus::Completed`.
   - Participants with pending, revision, rejected, or missing tasks are not eligible for participant certificates.
2. **Template & Layout Settings (`SettingService.php`)**:
   - Storage keys: `certificate_participant_template`, `certificate_participant_layout`, `certificate_troop_template`, `certificate_troop_layout`.
   - Layout fields support `x` (%), `y` (%), `font_size` (pt), `color` (hex), `align` (`center` | `left` | `right`), and `bold` (boolean).
3. **Interactive Visual Positioner (`App\Livewire\Admin\Certificates\Settings`)**:
   - Visual drag-and-drop & slider controls over certificate canvas preview.
   - Separate tabs for Participant Certificates (Full Name, Participant ID) and Troop Certificates (Troop Name, Troop Number).
   - Upload template images (PNG/JPG/WebP up to 5MB), delete templates to revert to default, and preview sample PDF directly.
4. **PDF Generation (`CertificatePdf.php`)**:
   - Renders custom uploaded template as background when available, overlaid with dynamic text at configured coordinates.
   - Falls back cleanly to application default vector frame when no template image is uploaded.
5. **Routes & Navigation**:
   - Admin route: `/admin/certificates/settings` (`admin.certificates.settings`).
   - Preview route: `/admin/certificates/preview/{type}` (`admin.certificates.preview`).
   - Sidebar item added under "Sertifikat" with `award` icon.

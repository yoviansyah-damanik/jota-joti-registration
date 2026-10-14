# Manual Participant Step Transition Fix

- **Masalah**:
  - Saat pendaftar menambahkan peserta secara manual tanpa melampirkan berkas Excel di Langkah 2, pendaftaran tidak dapat melanjutkan ke Langkah 3 (tetap tertahan di Langkah 2).
- **Penyebab**:
  - Pada `App\Support\RegistrationDraft::isStepComplete(int $step)`, evaluasi Langkah 2 sebelumnya memiliki syarat ganda:
    `$this->participants() !== [] && $this->file(DocumentType::ParticipantsExcel) !== null`.
  - Ketika peserta ditambahkan secara manual tanpa mengunggah berkas Excel, `$this->file(DocumentType::ParticipantsExcel)` bernilai `null` sehingga Langkah 2 dianggap belum lengkap (`isStepComplete(2) == false`) dan `$draft->currentStep()` selalu mengembalikan nilai `2`.
- **Solusi**:
  - Memperbarui `isStepComplete(2)` pada [RegistrationDraft.php](file:///d:/WebApps/jota-joti-registration/app/Support/RegistrationDraft.php) menjadi:
    `2 => $this->participants() !== []`.
  - Selama data peserta tidak kosong (baik berasal dari unggahan berkas Excel maupun input manual satu per satu), Langkah 2 dinyatakan lengkap dan form langsung melanjutkan ke Langkah 3.

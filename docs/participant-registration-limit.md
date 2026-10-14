# Flexible Participant Registration Count

- **Penyesuaian Batas Minimal**:
  - `App\Services\SettingService::minParticipantsPerTroop()` mengembalikan nilai `1` (sebelumnya dikalkulasikan $N \times 1 = 5$).
  - Gugusdepan dapat mendaftarkan peserta dengan jumlah berapapun (termasuk 1 atau 2 orang, minimal 1 peserta).
- **Alur & Validasi Pendaftaran**:
  - `App\Livewire\Public\Registration\Step2ParticipantUpload`: Validasi upload Excel maupun penambahan manual menerima jumlah peserta $\ge 1$.
  - `App\Livewire\Concerns\ParsesParticipantSheet`: Error batasan minimal hanya dipicu jika baris peserta kosong (0 peserta).
  - `App\Services\GroupAssignmentService::buildSequence()`: Penugasan grup mendukung jumlah peserta berapapun ($\ge 1$) dan mendistribusikan peserta ke grup dengan total terendah.
- **Tampilan & Bahasa**:
  - Halaman ketentuan (`terms-page.blade.php`) dan panduan langkah pendaftaran (`step2-participant-upload.blade.php`) diperbarui untuk menginformasikan bahwa jumlah peserta bebas/fleksibel.

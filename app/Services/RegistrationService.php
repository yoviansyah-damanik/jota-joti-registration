<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\TroopStatus;
use App\Models\Troop;
use App\Models\User;
use App\Support\RegistrationDraft;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    public function __construct(private ParticipantIdService $participantIds, private SettingService $settings) {}

    public function isTroopNumberTaken(string $troopNumber, ?string $ignoreTroopId = null): bool
    {
        return Troop::activeNumber($troopNumber)
            ->when($ignoreTroopId, fn ($q) => $q->whereKeyNot($ignoreTroopId))
            ->exists();
    }

    /**
     * Menyimpan draft wizard menjadi pendaftaran gudep beserta peserta dan dokumennya.
     */
    public function submit(RegistrationDraft $draft): Troop
    {
        if ($draft->currentStep() !== 4) {
            throw ValidationException::withMessages(['draft' => __('Registration data is incomplete.')]);
        }

        $troopData = $draft->troop();

        if ($this->isTroopNumberTaken($troopData['troop_number'])) {
            throw ValidationException::withMessages(['draft' => __('This troop number is already registered.')]);
        }

        $troop = DB::transaction(function () use ($draft, $troopData) {
            $troop = Troop::create([
                ...$troopData,
                'registration_code' => $this->generateCode(),
                'fee_per_participant' => $this->settings->int('registration_fee'),
                'status' => TroopStatus::Submitted,
                'submitted_at' => now(),
            ]);

            $troop->participants()->createMany(ParticipantSheetParser::toRecords($draft->participants()));

            foreach ($draft->files() as $type => $meta) {
                $this->attachDocument($troop, DocumentType::from($type), $meta);
            }

            return $troop;
        });

        $draft->clear();

        return $troop;
    }

    /**
     * Revisi oleh pendaftar: mengganti peserta dan/atau dokumen, lalu status kembali menunggu review.
     *
     * @param  array<int, array>|null  $participantRows
     * @param  array<string, array>  $files  metadata file baru per tipe dokumen
     */
    public function revise(Troop $troop, ?array $participantRows, array $files): Troop
    {
        return DB::transaction(function () use ($troop, $participantRows, $files) {
            if ($participantRows !== null) {
                $troop->participants()->delete();
                $troop->participants()->createMany(ParticipantSheetParser::toRecords($participantRows));
            }

            foreach ($files as $type => $meta) {
                $this->attachDocument($troop, DocumentType::from($type), $meta);
            }

            $troop->update(['status' => TroopStatus::Submitted, 'submitted_at' => now()]);

            return $troop->refresh();
        });
    }

    /**
     * Keputusan admin atas pendaftaran.
     */
    public function review(Troop $troop, TroopStatus $status, ?string $note, User $admin): Troop
    {
        if (in_array($status, [TroopStatus::Revision, TroopStatus::Rejected], true) && blank($note)) {
            throw ValidationException::withMessages(['note' => __('A note is required for revision or rejection.')]);
        }

        if ($status === TroopStatus::Verified
            && Troop::verified()->withAnyNumber([$troop->troop_number, $troop->troop_number_pair])->whereKeyNot($troop->id)->exists()) {
            throw ValidationException::withMessages(['note' => __('Another registration with this troop number is already verified.')]);
        }

        $troop->update([
            'status' => $status,
            'admin_note' => $note,
            'verified_by' => $status === TroopStatus::Verified ? $admin->id : null,
            'verified_at' => $status === TroopStatus::Verified ? now() : null,
        ]);

        if ($status === TroopStatus::Verified) {
            // setiap peserta mendapat ID unik (isi QR absensi) saat gudep diverifikasi
            $this->participantIds->issueForTroop($troop);
        } else {
            // nomor grup dilepas bila pendaftaran tidak lagi terverifikasi
            $troop->participants()->update(['group_number' => null, 'assigned_at' => null]);
        }

        return $troop;
    }

    /**
     * Memindahkan file dari lokasi sementara ke folder gudep dan mengganti dokumen lama.
     */
    private function attachDocument(Troop $troop, DocumentType $type, array $meta): void
    {
        $disk = Storage::disk(RegistrationDraft::DISK);
        $target = 'troops/'.$troop->id.'/'.$type->value.'-'.(string) Str::uuid().'.'.pathinfo($meta['path'], PATHINFO_EXTENSION);
        $disk->move($meta['path'], $target);

        $old = $troop->documents()->where('type', $type)->first();
        if ($old) {
            $disk->delete($old->path);
            $old->delete();
        }

        $troop->documents()->create([
            'type' => $type,
            'path' => $target,
            'original_name' => $meta['original_name'],
            'mime' => $meta['mime'],
            'size' => $meta['size'],
        ]);
    }

    public function generateCode(): string
    {
        // tanpa karakter yang mirip (0/O, 1/I/L) agar mudah dibaca pendaftar
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $code = 'JJ-'.collect(range(1, 6))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->implode('');
        } while (Troop::where('registration_code', $code)->exists());

        return $code;
    }
}

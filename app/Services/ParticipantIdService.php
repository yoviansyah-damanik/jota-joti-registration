<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Troop;

/**
 * Menerbitkan ID peserta (isi QR code absensi) saat gudep diverifikasi.
 * ID bersifat acak dan tetap: tidak berubah walau status gudep berubah kemudian.
 */
class ParticipantIdService
{
    public const PREFIX = 'P-';

    /** Tanpa karakter yang mirip (0/O, 1/I/L) agar mudah dibaca bila diketik manual. */
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const LENGTH = 8;

    /**
     * Memberi ID kepada peserta gudep yang belum memilikinya.
     *
     * @return int jumlah ID yang baru diterbitkan
     */
    public function issueForTroop(Troop $troop): int
    {
        $participants = $troop->participants()->whereNull('attendance_code')->get(['id']);

        foreach ($participants as $participant) {
            Participant::whereKey($participant->id)->update(['attendance_code' => $this->generateUnique()]);
        }

        return $participants->count();
    }

    public function generateUnique(): string
    {
        do {
            $code = self::PREFIX.collect(range(1, self::LENGTH))
                ->map(fn () => self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)])
                ->implode('');
        } while (Participant::where('attendance_code', $code)->exists());

        return $code;
    }

    /**
     * Merapikan hasil scan/ketikan: huruf besar, tanpa spasi.
     */
    public static function normalize(string $value): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($value)));
    }
}

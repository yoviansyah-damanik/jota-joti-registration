<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\TroopStatus;
use App\Models\Troop;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Laporan penerimaan uang pendaftaran.
 *
 * Nominal per gudep = jumlah peserta × biaya per peserta saat mendaftar (snapshot di tabel troops).
 * Dianggap "diterima" bila gudep sudah terverifikasi; "menunggu" bila masih diperiksa/revisi.
 */
class PaymentReportService
{
    public const SCOPES = ['all', 'received', 'pending', 'rejected'];

    /**
     * @param  array{from?: ?string, to?: ?string, scope?: ?string, search?: ?string}  $filters
     * @return Builder<Troop>
     */
    public function query(array $filters = []): Builder
    {
        $scope = in_array($filters['scope'] ?? 'all', self::SCOPES, true) ? ($filters['scope'] ?? 'all') : 'all';
        $from = $this->date($filters['from'] ?? null);
        $to = $this->date($filters['to'] ?? null);
        $search = trim((string) ($filters['search'] ?? ''));

        return Troop::query()
            ->withCount('participants')
            ->with(['documents' => fn ($q) => $q->where('type', DocumentType::PaymentProof)])
            ->when($scope === 'received', fn (Builder $q) => $q->where('status', TroopStatus::Verified))
            ->when($scope === 'pending', fn (Builder $q) => $q->whereIn('status', [TroopStatus::Submitted, TroopStatus::Revision]))
            ->when($scope === 'rejected', fn (Builder $q) => $q->where('status', TroopStatus::Rejected))
            // tanggal acuan: tanggal verifikasi (diterima) atau tanggal kirim (belum diverifikasi)
            ->when($from, fn (Builder $q) => $q->whereRaw('DATE(COALESCE(verified_at, submitted_at)) >= ?', [$from]))
            ->when($to, fn (Builder $q) => $q->whereRaw('DATE(COALESCE(verified_at, submitted_at)) <= ?', [$to]))
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->where('troop_number', 'like', "%{$search}%")
                ->orWhere('troop_number_pair', 'like', "%{$search}%")
                ->orWhere('troop_name', 'like', "%{$search}%")))
            ->orderByRaw('COALESCE(verified_at, submitted_at) desc')
            ->orderByDesc('id');
    }

    /**
     * Ringkasan nominal untuk filter yang sama.
     *
     * @param  array{from?: ?string, to?: ?string, scope?: ?string, search?: ?string}  $filters
     * @return array{received: int, received_troops: int, received_participants: int, pending: int, pending_troops: int, rejected_troops: int, expected: int}
     */
    public function summary(array $filters = []): array
    {
        $troops = $this->query($filters)->without('documents')->get();

        $received = $troops->where('status', TroopStatus::Verified);
        $pending = $troops->whereIn('status', [TroopStatus::Submitted, TroopStatus::Revision]);

        $receivedTotal = (int) $received->sum(fn (Troop $t) => $t->fee_total);
        $pendingTotal = (int) $pending->sum(fn (Troop $t) => $t->fee_total);

        return [
            'received' => $receivedTotal,
            'received_troops' => $received->count(),
            'received_participants' => (int) $received->sum('participants_count'),
            'pending' => $pendingTotal,
            'pending_troops' => $pending->count(),
            'rejected_troops' => $troops->where('status', TroopStatus::Rejected)->count(),
            'expected' => $receivedTotal + $pendingTotal,
        ];
    }

    private function date(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}

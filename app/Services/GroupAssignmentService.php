<?php

namespace App\Services;

use App\Models\GroupAssignmentBatch;
use App\Models\Participant;
use App\Models\Troop;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GroupAssignmentService
{
    public function __construct(private SettingService $settings) {}

    /**
     * Grup dianggap sudah disesuaikan bila ada peserta terverifikasi dan semuanya sudah bernomor grup.
     */
    public function isCompleted(): bool
    {
        return Participant::fromVerifiedTroops()->exists()
            && ! Participant::fromVerifiedTroops()->whereNull('group_number')->exists();
    }

    /**
     * Membatalkan pembagian: menghapus nomor grup seluruh peserta.
     *
     * @return int jumlah peserta yang nomornya dihapus
     */
    public function clear(): int
    {
        return Participant::query()
            ->whereNotNull('group_number')
            ->update(['group_number' => null, 'assigned_at' => null]);
    }

    /**
     * Membagikan nomor grup 1..N ke peserta dari gudep terverifikasi.
     *
     * - Dalam satu gudep: setiap nomor mendapat floor(c/N) peserta, sisa (c mod N) diberikan
     *   ke nomor yang total globalnya paling sedikit, sehingga selisih antar nomor maksimal 1.
     * - Urutan peserta dikelompokkan per jenis kelamin lalu diberi nomor secara bergilir
     *   agar komposisi L/P tiap nomor ikut merata.
     *
     * @param  bool  $reset  true = bagi ulang semua peserta; false = hanya gudep yang belum lengkap nomornya
     */
    public function assign(?User $admin = null, bool $reset = false): GroupAssignmentBatch
    {
        $max = $this->settings->maxGroupNumber();

        return DB::transaction(function () use ($admin, $reset, $max) {
            $troops = Troop::verified()
                ->with('participants')
                ->when(! $reset, fn ($q) => $q->whereHas('participants', fn ($q) => $q->whereNull('group_number')))
                ->get()
                ->shuffle();

            // hitungan awal dari peserta yang nomornya dipertahankan
            $global = array_fill(1, $max, 0);
            if (! $reset) {
                Participant::fromVerifiedTroops()
                    ->whereNotIn('troop_id', $troops->modelKeys())
                    ->whereBetween('group_number', [1, $max])
                    ->toBase()
                    ->selectRaw('group_number, count(*) as total')
                    ->groupBy('group_number')
                    ->pluck('total', 'group_number')
                    ->each(function ($total, $number) use (&$global) {
                        $global[(int) $number] = (int) $total;
                    });
            } else {
                Participant::query()->whereNotNull('group_number')->update(['group_number' => null, 'assigned_at' => null]);
            }

            $assignedCount = 0;
            foreach ($troops as $troop) {
                $assignedCount += $this->assignTroop($troop->participants, $max, $global);
            }

            return GroupAssignmentBatch::create([
                'executed_by' => $admin?->id,
                'max_group_number' => $max,
                'is_reset' => $reset,
                'total_troops' => $troops->count(),
                'total_participants' => $assignedCount,
                'summary' => $this->balanceReport(),
            ]);
        });
    }

    /**
     * @param  Collection<int, Participant>  $participants
     * @param  array<int, int>  $global  total peserta per nomor (diperbarui by reference)
     */
    private function assignTroop(Collection $participants, int $max, array &$global): int
    {
        $count = $participants->count();
        if ($count === 0) {
            return 0;
        }

        $sequence = $this->buildSequence($count, $max, $global);

        $ordered = $participants
            ->groupBy(fn (Participant $p) => $p->gender->value)
            ->map(fn (Collection $group) => $group->shuffle())
            ->flatten(1)
            ->values();

        $now = now();
        $updates = [];
        foreach ($ordered as $index => $participant) {
            $updates[$sequence[$index]][] = $participant->id;
            $global[$sequence[$index]]++;
        }

        foreach ($updates as $number => $ids) {
            Participant::whereKey($ids)->update(['group_number' => $number, 'assigned_at' => $now]);
        }

        return $count;
    }

    /**
     * Urutan nomor untuk satu gudep: nomor "sisa" lebih dulu, lalu putaran penuh 1..N (diacak).
     *
     * @param  array<int, int>  $global
     * @return array<int, int>
     */
    public function buildSequence(int $count, int $max, array $global): array
    {
        $base = intdiv($count, $max);
        $remainder = $count % $max;

        // nomor sisa diberikan ke nomor dengan total global terkecil (acak bila seri)
        $extras = collect(range(1, $max))
            ->shuffle()
            ->sortBy(fn (int $number) => $global[$number] ?? 0)
            ->take($remainder)
            ->shuffle()
            ->values()
            ->all();

        $round = collect(range(1, $max))->shuffle()->all();

        $sequence = $extras;
        for ($i = 0; $i < $base; $i++) {
            array_push($sequence, ...$round);
        }

        return $sequence;
    }

    /**
     * Laporan keseimbangan nomor grup untuk seluruh peserta terverifikasi.
     *
     * @return array{max_group_number: int, min_per_number: int, numbers: array, total: int, unassigned: int, spread: int, balanced: bool, troop_issues: array}
     */
    public function balanceReport(): array
    {
        $max = $this->settings->maxGroupNumber();
        $minPerNumber = $this->settings->minPerNumber();

        $rows = Participant::fromVerifiedTroops()
            ->toBase()
            ->selectRaw('troop_id, group_number, gender, count(*) as total')
            ->groupBy('troop_id', 'group_number', 'gender')
            ->get();

        $numbers = collect(range(1, $max))->mapWithKeys(fn (int $n) => [$n => [
            'number' => $n,
            'total' => (int) $rows->where('group_number', $n)->sum('total'),
            'male' => (int) $rows->where('group_number', $n)->where('gender', 'L')->sum('total'),
            'female' => (int) $rows->where('group_number', $n)->where('gender', 'P')->sum('total'),
        ]]);

        $totals = $numbers->pluck('total');
        $spread = $totals->isEmpty() ? 0 : $totals->max() - $totals->min();

        // gudep yang belum memenuhi minimal peserta per nomor
        $troopIssues = $rows->whereNotNull('group_number')
            ->groupBy('troop_id')
            ->map(function (Collection $troopRows) use ($max, $minPerNumber) {
                $perNumber = collect(range(1, $max))->mapWithKeys(fn ($n) => [$n => (int) $troopRows->where('group_number', $n)->sum('total')]);

                return $perNumber->filter(fn ($total) => $total < $minPerNumber)->keys()->all();
            })
            ->filter()
            ->all();

        return [
            'max_group_number' => $max,
            'min_per_number' => $minPerNumber,
            'numbers' => $numbers->values()->all(),
            'total' => (int) $rows->sum('total'),
            'unassigned' => (int) $rows->whereNull('group_number')->sum('total'),
            'spread' => $spread,
            'balanced' => $spread <= 1,
            'troop_issues' => $troopIssues,
        ];
    }
}

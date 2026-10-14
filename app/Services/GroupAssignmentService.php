<?php

namespace App\Services;

use App\Enums\ScoutUnit;
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
     * Membagikan nomor grup ke peserta dari gudep terverifikasi dengan sistem blocking per golongan:
     * - Penggalang mendapatkan blok nomor grup Penggalang (misal 1..5)
     * - Penegak/Pandega mendapatkan blok nomor grup Penegak (misal 6..10)
     * - Penggalang dan Penegak tidak bercampur dalam nomor grup yang sama.
     *
     * @param  bool  $reset  true = bagi ulang semua peserta; false = hanya gudep yang belum lengkap nomornya
     */
    public function assign(?User $admin = null, bool $reset = false): GroupAssignmentBatch
    {
        $penggalangGroups = $this->settings->penggalangGroups();
        $penegakGroups = $this->settings->penegakGroups();
        $max = $this->settings->maxGroupNumber();

        return DB::transaction(function () use ($admin, $reset, $penggalangGroups, $penegakGroups, $max) {
            $troops = Troop::verified()
                ->with('participants')
                ->when(! $reset, fn ($q) => $q->whereHas('participants', fn ($q) => $q->whereNull('group_number')))
                ->get()
                ->shuffle();

            // Hitungan global terpisah per blok golongan
            $globalPenggalang = array_fill_keys($penggalangGroups, 0);
            $globalPenegak = array_fill_keys($penegakGroups, 0);

            if (! $reset) {
                Participant::fromVerifiedTroops()
                    ->whereNotIn('troop_id', $troops->modelKeys())
                    ->whereNotNull('group_number')
                    ->toBase()
                    ->selectRaw('group_number, count(*) as total')
                    ->groupBy('group_number')
                    ->pluck('total', 'group_number')
                    ->each(function ($total, $number) use (&$globalPenggalang, &$globalPenegak) {
                        $num = (int) $number;
                        if (array_key_exists($num, $globalPenggalang)) {
                            $globalPenggalang[$num] = (int) $total;
                        } elseif (array_key_exists($num, $globalPenegak)) {
                            $globalPenegak[$num] = (int) $total;
                        }
                    });
            } else {
                Participant::query()->whereNotNull('group_number')->update(['group_number' => null, 'assigned_at' => null]);
            }

            $assignedCount = 0;
            foreach ($troops as $troop) {
                $isPenegak = $troop->unit === ScoutUnit::PenegakPandega;
                $allowedNumbers = $isPenegak ? $penegakGroups : $penggalangGroups;

                if ($isPenegak) {
                    $assignedCount += $this->assignTroop($troop->participants, $allowedNumbers, $globalPenegak);
                } else {
                    $assignedCount += $this->assignTroop($troop->participants, $allowedNumbers, $globalPenggalang);
                }
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
     * @param  array<int>  $allowedNumbers  daftar nomor grup yang dialokasikan untuk golongan gudep ini
     * @param  array<int, int>  $global  total peserta per nomor (diperbarui by reference)
     */
    private function assignTroop(Collection $participants, array $allowedNumbers, array &$global): int
    {
        $count = $participants->count();
        if ($count === 0 || $allowedNumbers === []) {
            return 0;
        }

        $sequence = $this->buildSequence($count, $allowedNumbers, $global);

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
     * Urutan nomor untuk satu gudep: nomor "sisa" lebih dulu, lalu putaran penuh (diacak).
     *
     * @param  int  $count  jumlah peserta
     * @param  array<int>|int  $numbers  daftar nomor grup yang diizinkan (atau jumlah grup untuk kompatibilitas)
     * @param  array<int, int>  $global
     * @return array<int, int>
     */
    public function buildSequence(int $count, array|int $numbers, array $global): array
    {
        $allowed = is_array($numbers) ? $numbers : range(1, $numbers);
        $totalNumbers = count($allowed);
        if ($totalNumbers === 0) {
            return [];
        }

        $base = intdiv($count, $totalNumbers);
        $remainder = $count % $totalNumbers;

        // nomor sisa diberikan ke nomor dengan total global terkecil (acak bila seri)
        $extras = collect($allowed)
            ->shuffle()
            ->sortBy(fn (int $number) => $global[$number] ?? 0)
            ->take($remainder)
            ->shuffle()
            ->values()
            ->all();

        $round = collect($allowed)->shuffle()->all();

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
        $penggalangGroups = $this->settings->penggalangGroups();
        $penegakGroups = $this->settings->penegakGroups();
        $allGroups = $this->settings->allGroupNumbers();
        $max = $this->settings->maxGroupNumber();
        $minPerNumber = $this->settings->minPerNumber();

        $rows = Participant::fromVerifiedTroops()
            ->toBase()
            ->selectRaw('troop_id, group_number, gender, count(*) as total')
            ->groupBy('troop_id', 'group_number', 'gender')
            ->get();

        $numbers = collect($allGroups)->mapWithKeys(function (int $n) use ($rows, $penggalangGroups) {
            $isPenggalang = in_array($n, $penggalangGroups, true);
            $unit = $isPenggalang ? ScoutUnit::Penggalang : ScoutUnit::PenegakPandega;

            return [$n => [
                'number' => $n,
                'unit' => $unit->value,
                'unit_label' => $unit->label(),
                'total' => (int) $rows->where('group_number', $n)->sum('total'),
                'male' => (int) $rows->where('group_number', $n)->where('gender', 'L')->sum('total'),
                'female' => (int) $rows->where('group_number', $n)->where('gender', 'P')->sum('total'),
            ]];
        });

        // Keseimbangan dihitung per blok golongan (karena golongan tidak bercampur)
        $penggalangTotals = $numbers->whereIn('number', $penggalangGroups)->pluck('total');
        $penggalangSpread = $penggalangTotals->isEmpty() ? 0 : $penggalangTotals->max() - $penggalangTotals->min();

        $penegakTotals = $numbers->whereIn('number', $penegakGroups)->pluck('total');
        $penegakSpread = $penegakTotals->isEmpty() ? 0 : $penegakTotals->max() - $penegakTotals->min();

        $spread = max($penggalangSpread, $penegakSpread);
        $balanced = $penggalangSpread <= 1 && $penegakSpread <= 1;

        // Gudep yang belum memenuhi minimal peserta per nomor di blok golongannya
        $troopIssues = $rows->whereNotNull('group_number')
            ->groupBy('troop_id')
            ->map(function (Collection $troopRows) use ($minPerNumber) {
                $troopId = $troopRows->first()->troop_id;
                $troop = Troop::find($troopId);
                if (! $troop) {
                    return [];
                }

                $allowed = $this->settings->groupsForUnit($troop->unit);
                $perNumber = collect($allowed)->mapWithKeys(fn ($n) => [$n => (int) $troopRows->where('group_number', $n)->sum('total')]);

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
            'balanced' => $balanced,
            'troop_issues' => $troopIssues,
            'penggalang_groups' => $penggalangGroups,
            'penegak_groups' => $penegakGroups,
            'penggalang_spread' => $penggalangSpread,
            'penegak_spread' => $penegakSpread,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Exports\ParticipantsExport;
use App\Exports\PaymentsExport;
use App\Models\AttendanceSession;
use App\Models\Participant;
use App\Models\Troop;
use App\Services\AttendanceService;
use App\Services\PaymentReportService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class AdminExportController extends Controller
{
    public function participants(Request $request)
    {
        $verifiedOnly = $request->boolean('verified');
        $name = $verifiedOnly ? 'peserta-terverifikasi' : 'semua-peserta';

        return Excel::download(new ParticipantsExport($verifiedOnly), $name.'-'.now()->format('Ymd-His').'.xlsx');
    }

    public function payments(Request $request, PaymentReportService $report)
    {
        $filters = $this->paymentFilters($request);

        return Excel::download(new PaymentsExport($report->query($filters)->get(), $report->summary($filters)), 'laporan-penerimaan-biaya-'.now()->format('Ymd-His').'.xlsx');
    }

    /**
     * Laporan penerimaan biaya pendaftaran siap cetak.
     */
    public function paymentsSheet(Request $request, PaymentReportService $report, SettingService $settings)
    {
        $filters = $this->paymentFilters($request);

        return view('print.payments', [
            'troops' => $report->query($filters)->get(),
            'summary' => $report->summary($filters),
            'filters' => $filters,
            'eventName' => $settings->get('event_name'),
            'organizationName' => $settings->get('organization_name'),
        ]);
    }

    /**
     * @return array{from: ?string, to: ?string, scope: string}
     */
    private function paymentFilters(Request $request): array
    {
        return [
            'from' => $request->query('from') ?: null,
            'to' => $request->query('to') ?: null,
            'scope' => (string) $request->query('scope', 'received'),
        ];
    }

    public function attendance(AttendanceSession $session)
    {
        return Excel::download(new AttendanceExport($session), 'absensi-'.Str::slug($session->name).'.xlsx');
    }

    /**
     * Lembar absensi cetak per gudep (dengan kolom tanda tangan).
     */
    public function attendanceSheet(Request $request, AttendanceSession $session, AttendanceService $attendance, SettingService $settings)
    {
        $participants = $attendance->participantsWithStatus($session, [
            'troop_id' => $request->filled('troop_id') ? (string) $request->input('troop_id') : null,
            'group_number' => $request->integer('group_number') ?: null,
        ]);

        return view('print.attendance-sheet', [
            'session' => $session,
            'eventName' => $settings->get('event_name'),
            'organizationName' => $settings->get('organization_name'),
            'settings' => $settings,
            'troops' => $participants->groupBy('troop_id'),
        ]);
    }

    /**
     * Lembar presensi/absensi manual siap cetak.
     * Default per halaman memuat masing-masing gudep dengan baris penuh lembar.
     */
    public function manualAttendanceSheet(Request $request, SettingService $settings)
    {
        $groupBy = in_array($request->query('group_by'), ['group', 'troop'], true)
            ? $request->query('group_by')
            : 'troop';

        $rowsPerPage = max(10, min(35, (int) $request->query('rows_per_page', 20)));
        $unit = $request->query('unit') ?: null;
        $groupNumber = $request->filled('group_number') ? (int) $request->query('group_number') : null;
        $troopId = $request->filled('troop_id') ? (string) $request->query('troop_id') : null;
        $sessionId = $request->filled('session_id') ? (int) $request->query('session_id') : null;
        $session = $sessionId ? AttendanceSession::find($sessionId) : null;

        $pages = [];

        if ($groupBy === 'troop') {
            $troops = Troop::verified()
                ->when($unit, fn ($q) => $q->where('unit', $unit))
                ->when($troopId, fn ($q) => $q->where('id', $troopId))
                ->orderBy('unit')
                ->orderBy('troop_number')
                ->with(['participants' => function ($q) use ($groupNumber) {
                    $q->when($groupNumber !== null, fn ($sub) => $sub->where('group_number', $groupNumber))
                        ->orderByRaw('group_number is null, group_number asc')
                        ->orderBy('full_name');
                }])
                ->get();

            foreach ($troops as $t) {
                $troopParticipants = $t->participants;
                $totalParticipants = $troopParticipants->count();
                $maleCount = $troopParticipants->where('gender', \App\Enums\Gender::Male)->count();
                $femaleCount = $troopParticipants->where('gender', \App\Enums\Gender::Female)->count();

                $chunks = $totalParticipants > 0 ? $troopParticipants->chunk($rowsPerPage) : collect([collect()]);
                $totalPages = $chunks->count();

                foreach ($chunks as $pageIndex => $chunk) {
                    $pages[] = [
                        'type' => 'troop',
                        'troop' => $t,
                        'unit' => $t->unit,
                        'participants' => $chunk,
                        'total_participants' => $totalParticipants,
                        'male_count' => $maleCount,
                        'female_count' => $femaleCount,
                        'page_number' => $pageIndex + 1,
                        'total_pages' => $totalPages,
                        'start_index' => ($pageIndex * $rowsPerPage) + 1,
                        'empty_rows_count' => max(0, $rowsPerPage - $chunk->count()),
                    ];
                }
            }
        } else {
            // Pengelompokan Berdasarkan Nomor Grup
            $allowedGroups = $unit === \App\Enums\ScoutUnit::Penggalang->value
                ? $settings->penggalangGroups()
                : ($unit === \App\Enums\ScoutUnit::PenegakPandega->value ? $settings->penegakGroups() : $settings->allGroupNumbers());

            if ($groupNumber !== null) {
                $allowedGroups = in_array($groupNumber, $allowedGroups, true) ? [$groupNumber] : [$groupNumber];
            }

            $participantsQuery = Participant::fromVerifiedTroops()
                ->with(['troop'])
                ->when($unit, fn ($q) => $q->whereHas('troop', fn ($t) => $t->where('unit', $unit)))
                ->when($troopId, fn ($q) => $q->where('troop_id', $troopId))
                ->orderBy('full_name');

            $allGroupedParticipants = $participantsQuery->get()->groupBy(fn ($p) => $p->group_number ?? 0);

            foreach ($allowedGroups as $g) {
                $groupParticipants = $allGroupedParticipants->get($g, collect());
                $totalParticipants = $groupParticipants->count();
                $maleCount = $groupParticipants->where('gender', \App\Enums\Gender::Male)->count();
                $femaleCount = $groupParticipants->where('gender', \App\Enums\Gender::Female)->count();

                $chunks = $totalParticipants > 0 ? $groupParticipants->chunk($rowsPerPage) : collect([collect()]);
                $totalPages = $chunks->count();

                foreach ($chunks as $pageIndex => $chunk) {
                    $pages[] = [
                        'type' => 'group',
                        'group_number' => $g,
                        'unit' => $settings->unitForGroup($g),
                        'participants' => $chunk,
                        'total_participants' => $totalParticipants,
                        'male_count' => $maleCount,
                        'female_count' => $femaleCount,
                        'page_number' => $pageIndex + 1,
                        'total_pages' => $totalPages,
                        'start_index' => ($pageIndex * $rowsPerPage) + 1,
                        'empty_rows_count' => max(0, $rowsPerPage - $chunk->count()),
                    ];
                }
            }
        }

        $allTroops = Troop::verified()->orderBy('troop_number')->get(['id', 'troop_number', 'troop_number_pair', 'troop_name', 'unit']);
        $sessions = AttendanceSession::orderByDesc('date')->get(['id', 'name', 'date', 'is_active']);

        return view('print.manual-attendance', [
            'groupBy' => $groupBy,
            'rowsPerPage' => $rowsPerPage,
            'unit' => $unit,
            'groupNumber' => $groupNumber,
            'troopId' => $troopId,
            'sessionId' => $sessionId,
            'session' => $session,
            'pages' => $pages,
            'allTroops' => $allTroops,
            'sessions' => $sessions,
            'settings' => $settings,
            'penggalangGroups' => $settings->penggalangGroups(),
            'penegakGroups' => $settings->penegakGroups(),
            'allGroupNumbers' => $settings->allGroupNumbers(),
            'eventName' => $settings->get('event_name'),
            'organizationName' => $settings->get('organization_name'),
        ]);
    }
}

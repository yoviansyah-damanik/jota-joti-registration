<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Exports\ParticipantsExport;
use App\Exports\PaymentsExport;
use App\Models\AttendanceSession;
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
            'troops' => $participants->groupBy('troop_id'),
        ]);
    }
}

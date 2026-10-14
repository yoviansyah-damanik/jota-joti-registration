<?php

namespace App\Exports;

use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private AttendanceSession $session) {}

    public function collection(): Collection
    {
        return app(AttendanceService::class)
            ->participantsWithStatus($this->session)
            ->map(fn ($participant) => [
                $participant->troop->number_label,
                $participant->troop->troop_name,
                $participant->troop->unit?->label(),
                $participant->attendance_code,
                $participant->kta_number,
                $participant->full_name,
                $participant->gender->value,
                $participant->group_number,
                $participant->attendance?->status->label() ?? __('Not recorded'),
                $participant->attendance?->checked_in_at?->format('Y-m-d H:i'),
                $participant->attendance?->note,
            ]);
    }

    public function headings(): array
    {
        return ['No Gugusdepan', 'Nama Gugusdepan', 'Satuan', 'ID Peserta', 'No KTA', 'Nama Lengkap', 'Jenis Kelamin', 'Nomor Grup', 'Status', 'Waktu Hadir', 'Catatan'];
    }

    public function title(): string
    {
        return mb_substr($this->session->name, 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

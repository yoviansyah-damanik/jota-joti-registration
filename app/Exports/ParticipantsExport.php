<?php

namespace App\Exports;

use App\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private bool $verifiedOnly = false) {}

    public function query(): Builder
    {
        return Participant::query()
            ->with('troop')
            ->when($this->verifiedOnly, fn ($q) => $q->fromVerifiedTroops())
            ->join('troops', 'troops.id', '=', 'participants.troop_id')
            ->orderBy('troops.troop_number')
            ->orderBy('participants.full_name')
            ->select('participants.*');
    }

    public function headings(): array
    {
        return ['No Gugusdepan', 'Nama Gugusdepan', 'Satuan', 'Status', 'ID Peserta', 'No KTA', 'Nama Lengkap', 'Tanggal Lahir', 'Jenis Kelamin', 'Nomor Grup'];
    }

    /**
     * @param  Participant  $participant
     */
    public function map(mixed $participant): array
    {
        return [
            $participant->troop->number_label,
            $participant->troop->troop_name,
            $participant->troop->unit?->label(),
            $participant->troop->status->label(),
            $participant->attendance_code,
            $participant->kta_number,
            $participant->full_name,
            $participant->birth_date->format('Y-m-d'),
            $participant->gender->value,
            $participant->group_number,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

<?php

namespace App\Exports;

use App\Models\Troop;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Laporan penerimaan biaya pendaftaran; baris terakhir memuat total diterima dan menunggu verifikasi.
 */
class PaymentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /**
     * @param  Collection<int, Troop>  $troops
     * @param  array{received: int, pending: int}  $summary
     */
    public function __construct(private Collection $troops, private array $summary) {}

    public function collection(): Collection
    {
        // dua baris ringkasan ditambahkan setelah data gudep
        return $this->troops->concat([
            ['label' => 'TOTAL DITERIMA', 'amount' => $this->summary['received']],
            ['label' => 'TOTAL MENUNGGU VERIFIKASI', 'amount' => $this->summary['pending']],
        ]);
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'No Gugusdepan', 'Nama Gugusdepan', 'Satuan', 'Status', 'Jumlah Peserta', 'Biaya per Peserta', 'Jumlah (Rp)', 'Bukti Pembayaran'];
    }

    /**
     * @param  Troop|array{label: string, amount: int}  $row
     */
    public function map(mixed $row): array
    {
        static $number = 0;

        if (is_array($row)) {
            return ['', '', '', '', '', '', '', $row['label'], $row['amount'], ''];
        }

        return [
            ++$number,
            ($row->verified_at ?? $row->submitted_at)?->format('Y-m-d'),
            $row->number_label,
            $row->troop_name,
            $row->unit?->label(),
            $row->status->label(),
            $row->participants_count,
            $row->fee_per_participant,
            $row->fee_total,
            $row->documents->isNotEmpty() ? 'Ada' : 'Tidak ada',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->troops->count() + 3;

        return [
            1 => ['font' => ['bold' => true]],
            ($lastRow - 1) => ['font' => ['bold' => true]],
            $lastRow => ['font' => ['bold' => true]],
        ];
    }
}

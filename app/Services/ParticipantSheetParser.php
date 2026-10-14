<?php

namespace App\Services;

use App\Enums\Gender;
use App\Enums\TroopStatus;
use App\Imports\ParticipantImport;
use App\Models\Participant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ParticipantSheetParser
{
    private const DATE_FORMATS = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d'];

    private const GENDER_ALIASES = [
        'L' => 'L', 'LK' => 'L', 'LAKI-LAKI' => 'L', 'LAKI LAKI' => 'L', 'PUTRA' => 'L',
        'P' => 'P', 'PR' => 'P', 'PEREMPUAN' => 'P', 'PUTRI' => 'P',
    ];

    /**
     * Membaca file Excel peserta lalu memvalidasi setiap baris.
     *
     * @param  string|null  $ignoreTroopId  gudep yang sedang direvisi (KTA miliknya tidak dianggap duplikat)
     * @return array{rows: array<int, array>, errors: array<int, string>, valid: bool}
     */
    public function parse(string $path, ?string $ignoreTroopId = null): array
    {
        try {
            // tipe dideteksi dari isi file karena file sementara upload bisa tanpa ekstensi
            $sheet = Excel::toCollection(new ParticipantImport, $path, null, IOFactory::identify($path))->first() ?? collect();
        } catch (Throwable) {
            return $this->result([], [__('The file could not be read. Make sure it is a valid .xlsx file based on the template.')]);
        }

        $missing = $this->missingColumns($sheet);
        if ($missing !== []) {
            return $this->result([], [__('Missing columns: :columns. Please use the provided template.', ['columns' => implode(', ', $missing)])]);
        }

        $rows = $sheet
            ->map(fn (Collection $row, int $index) => $this->normalizeRow($row, $index + 2))
            ->reject(fn (array $row) => $row['kta_number'] === ParticipantImport::EXAMPLE_KTA)
            ->values()
            ->all();

        if ($rows === []) {
            return $this->result([], [__('The file does not contain any participant rows.')]);
        }

        $rows = $this->validateRows($rows, $ignoreTroopId);

        return $this->result($rows, []);
    }

    /**
     * @return array<int, string>
     */
    private function missingColumns(Collection $sheet): array
    {
        $headers = array_keys($sheet->first()?->toArray() ?? []);

        return array_values(array_diff(ParticipantImport::COLUMNS, $headers));
    }

    private function normalizeRow(Collection $row, int $excelRow): array
    {
        $kta = $row->get('no_kta');
        if (is_float($kta) && floor($kta) === $kta) {
            $kta = (int) $kta;
        }

        $genderRaw = strtoupper(trim((string) $row->get('jenis_kelamin')));

        return [
            'row' => $excelRow,
            'kta_number' => trim((string) $kta),
            'full_name' => preg_replace('/\s+/', ' ', trim((string) $row->get('nama_lengkap'))),
            'birth_date' => $this->parseDate($row->get('tanggal_lahir')),
            'birth_date_raw' => trim((string) $row->get('tanggal_lahir')),
            'gender' => self::GENDER_ALIASES[$genderRaw] ?? null,
            'gender_raw' => $genderRaw,
            'errors' => [],
        ];
    }

    /**
     * Mendukung tanggal numerik Excel maupun teks dengan beberapa format umum.
     */
    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return CarbonImmutable::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            } catch (Throwable) {
                return null;
            }
        }

        $value = trim((string) $value);

        foreach (self::DATE_FORMATS as $format) {
            try {
                $date = CarbonImmutable::createFromFormat('!'.$format, $value);
            } catch (Throwable) {
                continue;
            }

            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * @param  array<int, array>  $rows
     * @return array<int, array>
     */
    private function validateRows(array $rows, ?string $ignoreTroopId): array
    {
        $ktaCounts = array_count_values(array_filter(array_column($rows, 'kta_number')));

        $registered = Participant::query()
            ->whereIn('kta_number', array_keys($ktaCounts))
            ->whereHas('troop', fn ($q) => $q->whereIn('status', TroopStatus::activeValues()))
            ->when($ignoreTroopId, fn ($q) => $q->where('troop_id', '!=', $ignoreTroopId))
            ->pluck('kta_number')
            ->flip();

        $today = CarbonImmutable::today();

        foreach ($rows as &$row) {
            $errors = [];

            if ($row['kta_number'] !== '') {
                if (mb_strlen($row['kta_number']) > 50) {
                    $errors[] = __('KTA number is too long.');
                } elseif (($ktaCounts[$row['kta_number']] ?? 0) > 1) {
                    $errors[] = __('KTA number is duplicated in this file.');
                } elseif ($registered->has($row['kta_number'])) {
                    $errors[] = __('KTA number is already registered by another troop.');
                }
            }

            if ($row['full_name'] === '') {
                $errors[] = __('Full name is required.');
            } elseif (mb_strlen($row['full_name']) > 255) {
                $errors[] = __('Full name is too long.');
            }

            if ($row['birth_date'] === null) {
                $errors[] = __('Invalid date of birth (use YYYY-MM-DD).');
            } else {
                $birthDate = CarbonImmutable::parse($row['birth_date']);
                if ($birthDate->isAfter($today) || $birthDate->diffInYears($today) > 100) {
                    $errors[] = __('Date of birth is out of a reasonable range.');
                }
            }

            if ($row['gender'] === null) {
                $errors[] = __('Gender must be L or P.');
            }

            $row['errors'] = $errors;
        }

        return $rows;
    }

    private function result(array $rows, array $errors): array
    {
        $valid = $errors === [] && $rows !== [] && collect($rows)->every(fn ($row) => $row['errors'] === []);

        return ['rows' => $rows, 'errors' => $errors, 'valid' => $valid];
    }

    /**
     * Mengambil kolom yang disimpan ke tabel participants.
     *
     * @param  array<int, array>  $rows
     * @return array<int, array>
     */
    public static function toRecords(array $rows): array
    {
        return array_map(fn (array $row) => [
            'kta_number' => filled($row['kta_number'] ?? null) ? $row['kta_number'] : null,
            'full_name' => $row['full_name'],
            'birth_date' => $row['birth_date'],
            'gender' => Gender::from($row['gender']),
        ], $rows);
    }
}

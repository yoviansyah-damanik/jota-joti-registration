<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\DocumentType;
use App\Enums\ScoutUnit;
use App\Enums\TaskStatus;
use App\Enums\TroopStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Participant;
use App\Models\TaskSubmission;
use App\Models\Troop;
use App\Models\User;
use App\Services\GroupAssignmentService;
use App\Services\SettingService;
use App\Services\ParticipantIdService;
use App\Services\RegistrationService;
use App\Support\RegistrationDraft;
use Database\Seeders\Support\DemoDocuments;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Data contoh pendaftaran gudep se-Kota Padangsidimpuan.
 * Jalankan: php artisan db:seed --class=DemoSeeder (aman dijalankan ulang, data demo lama dibersihkan).
 */
class DemoSeeder extends Seeder
{
    private const TROOP_COUNT = 40;

    private const DEMO_EMAIL_DOMAIN = '@demo.jotajoti.test';

    private const KWARRAN = [
        '01' => 'Padangsidimpuan Utara',
        '02' => 'Padangsidimpuan Selatan',
        '03' => 'Padangsidimpuan Batunadua',
        '04' => 'Padangsidimpuan Hutaimbaru',
        '05' => 'Padangsidimpuan Tenggara',
        '06' => 'Padangsidimpuan Angkola Julu',
    ];

    private const MARGA = [
        'Harahap', 'Siregar', 'Nasution', 'Lubis', 'Hasibuan', 'Daulay', 'Pulungan', 'Rangkuti',
        'Ritonga', 'Pohan', 'Batubara', 'Tanjung', 'Dalimunthe', 'Hutasuhut', 'Simatupang', 'Matondang',
    ];

    private const REVISION_NOTES = [
        'Bukti transfer kurang jelas, mohon unggah ulang dengan foto yang terbaca.',
        'Surat tugas belum ditandatangani Pembina Gugus Depan.',
        'Terdapat peserta dengan tanggal lahir yang tidak sesuai KTA, mohon diperiksa kembali.',
    ];

    private const REJECTION_NOTES = [
        'Pembayaran tidak ditemukan pada rekening panitia.',
        'Gugus depan sudah terdaftar melalui pendaftaran lain.',
    ];

    private ParticipantIdService $participantIds;

    public function run(RegistrationService $registrations, GroupAssignmentService $assignments, ParticipantIdService $participantIds): void
    {
        // Jangan jalankan seeder data demo di environment production
        if (app()->environment('production', 'prod')) {
            $this->command?->warn('DemoSeeder dilewati karena aplikasi berada pada environment production.');

            return;
        }

        $this->participantIds = $participantIds;
        $this->purgePreviousDemo();

        $admin = User::where('is_admin', true)->first();
        $schools = collect($this->schools())->shuffle()->take(self::TROOP_COUNT)->values();
        $statuses = $this->statusPlan($schools->count());
        $nextNumber = [];
        $totalParticipants = 0;

        DB::transaction(function () use ($schools, $statuses, &$nextNumber, &$totalParticipants, $admin, $registrations) {
            foreach ($schools as $index => [$school, $level]) {
                $prefix = array_rand(self::KWARRAN);
                // nomor pertama selalu ganjil, pasangan (+1) otomatis dari model
                $nextNumber[$prefix] = ($nextNumber[$prefix] ?? fake()->numberBetween(0, 20) * 2 + 1) + fake()->randomElement([2, 4, 6]);
                $status = $statuses[$index];
                $submittedAt = Carbon::now()->subDays(fake()->numberBetween(1, 25))->setTime(fake()->numberBetween(7, 21), fake()->numberBetween(0, 59));

                $troopNumber = sprintf('%s-%03d', $prefix, $nextNumber[$prefix]);
                $troop = Troop::create([
                    'troop_number' => $troopNumber,
                    'troop_number_pair' => \App\Support\TroopNumber::pair($troopNumber) ?? $troopNumber,
                    'troop_name' => $school,
                    'unit' => $level === 'sma' ? ScoutUnit::PenegakPandega : ScoutUnit::Penggalang,
                    'pic_name' => $this->personName(fake()->boolean()).', '.fake()->randomElement(['S.Pd.', 'S.Pd.I.', 'S.Pd.', 'M.Pd.', 'S.E.']),
                    'pic_phone' => '08'.fake()->randomElement(['12', '13', '21', '22', '52', '53', '77', '81']).fake()->numerify('########'),
                    'pic_email' => 'pembina'.($index + 1).self::DEMO_EMAIL_DOMAIN,
                    'registration_code' => $registrations->generateCode(),
                    'fee_per_participant' => (int) app(SettingService::class)->get('registration_fee'),
                    'status' => $status,
                    'admin_note' => match ($status) {
                        TroopStatus::Revision => fake()->randomElement(self::REVISION_NOTES),
                        TroopStatus::Rejected => fake()->randomElement(self::REJECTION_NOTES),
                        default => null,
                    },
                    'verified_by' => $status === TroopStatus::Verified ? $admin?->id : null,
                    'verified_at' => $status === TroopStatus::Verified ? $submittedAt->copy()->addDays(fake()->numberBetween(1, 3)) : null,
                    'submitted_at' => $submittedAt,
                ]);
                $troop->forceFill(['created_at' => $submittedAt, 'updated_at' => $submittedAt])->saveQuietly();

                $rows = $this->participantRows($troop, $level, fake()->numberBetween(6, 24));
                Participant::insert(array_map(fn ($row) => [
                    'id' => (string) Str::uuid(),
                    'troop_id' => $troop->id,
                    'kta_number' => $row[0],
                    'full_name' => $row[1],
                    'birth_date' => $row[2],
                    'gender' => $row[3],
                    'created_at' => $submittedAt,
                    'updated_at' => $submittedAt,
                ], $rows));
                $totalParticipants += count($rows);

                if ($status === TroopStatus::Verified) {
                    $this->participantIds->issueForTroop($troop);
                }

                $this->storeDocuments($troop, $rows);
            }
        });

        $batch = $assignments->assign($admin);
        $this->seedAttendance($admin);
        $this->seedTaskSubmissions($admin);

        $this->command?->info(sprintf(
            'Demo: %d gudep, %d peserta, %d peserta mendapat nomor grup.',
            $schools->count(), $totalParticipants, $batch->total_participants,
        ));
    }

    /**
     * Menghapus data demo sebelumnya beserta file dokumennya.
     */
    private function purgePreviousDemo(): void
    {
        $disk = Storage::disk(RegistrationDraft::DISK);

        Troop::where('pic_email', 'like', '%'.self::DEMO_EMAIL_DOMAIN)->each(function (Troop $troop) use ($disk) {
            $disk->deleteDirectory('troops/'.$troop->id);
            $troop->delete();
        });
    }

    /**
     * Sebaran status: ±55% terverifikasi, 25% menunggu, 10% revisi, 10% ditolak.
     *
     * @return array<int, TroopStatus>
     */
    private function statusPlan(int $count): array
    {
        $revision = (int) round($count * 0.1);
        $rejected = (int) round($count * 0.1);
        $submitted = (int) round($count * 0.25);

        return collect()
            ->pad($revision, TroopStatus::Revision)
            ->merge(array_fill(0, $rejected, TroopStatus::Rejected))
            ->merge(array_fill(0, $submitted, TroopStatus::Submitted))
            ->pipe(fn ($c) => $c->merge(array_fill(0, $count - $c->count(), TroopStatus::Verified)))
            ->shuffle()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: string}>
     */
    private function participantRows(Troop $troop, string $level, int $count): array
    {
        [$minAge, $maxAge] = match ($level) {
            'sd' => [11, 12],
            'smp' => [12, 15],
            default => [16, 18],
        };

        $digits = str_replace('-', '', $troop->troop_number);

        return collect(range(1, $count))->map(function (int $i) use ($digits, $minAge, $maxAge) {
            $male = fake()->boolean();

            return [
                sprintf('1277.%s.%03d', $digits, $i),
                $this->personName($male),
                fake()->dateTimeBetween("-{$maxAge} years", "-{$minAge} years")->format('Y-m-d'),
                $male ? 'L' : 'P',
            ];
        })->all();
    }

    private function personName(bool $male): string
    {
        return fake()->firstName($male ? 'male' : 'female').' '.fake()->randomElement(self::MARGA);
    }

    /**
     * @param  array<int, array>  $rows
     */
    private function storeDocuments(Troop $troop, array $rows): void
    {
        $disk = Storage::disk(RegistrationDraft::DISK);
        $dir = 'troops/'.$troop->id;
        $proofIsImage = fake()->boolean(70);

        $files = [
            DocumentType::ParticipantsExcel->value => [
                'content' => DemoDocuments::participantsXlsx($rows),
                'ext' => 'xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'name' => 'data-peserta-'.$troop->troop_number.'.xlsx',
            ],
            DocumentType::AssignmentLetter->value => [
                'content' => DemoDocuments::pdf([
                    'SURAT TUGAS',
                    'Gugus Depan '.$troop->number_label,
                    $troop->troop_name,
                    '',
                    'Menugaskan '.count($rows).' orang peserta didik untuk mengikuti',
                    'JOTA JOTI Kwarcab Kota Padangsidimpuan.',
                    '',
                    'Pembina Gugus Depan,',
                    $troop->pic_name,
                ]),
                'ext' => 'pdf',
                'mime' => 'application/pdf',
                'name' => 'surat-tugas.pdf',
            ],
            DocumentType::PaymentProof->value => $proofIsImage
                ? [
                    'content' => DemoDocuments::receiptPng([
                        'Tanggal  : '.$troop->submitted_at->format('d/m/Y H:i'),
                        'Dari     : '.$troop->pic_name,
                        'Ke       : Panitia JOTA JOTI Kwarcab Psp',
                        'Jumlah   : Rp 50.000',
                        'Berita   : Gudep '.$troop->number_label,
                    ]),
                    'ext' => 'png',
                    'mime' => 'image/png',
                    'name' => 'bukti-transfer.png',
                ]
                : [
                    'content' => DemoDocuments::pdf(['BUKTI TRANSFER', 'Jumlah: Rp 50.000', 'Berita: Gudep '.$troop->number_label]),
                    'ext' => 'pdf',
                    'mime' => 'application/pdf',
                    'name' => 'bukti-transfer.pdf',
                ],
        ];

        foreach ($files as $type => $file) {
            $path = "{$dir}/{$type}.{$file['ext']}";
            $disk->put($path, $file['content']);

            $troop->documents()->create([
                'type' => $type,
                'path' => $path,
                'original_name' => $file['name'],
                'mime' => $file['mime'],
                'size' => strlen($file['content']),
            ]);
        }
    }

    /**
     * Dua sesi absensi; sesi pertama sudah terisi sebagian.
     */
    private function seedAttendance(?User $admin): void
    {
        $first = AttendanceSession::firstOrCreate(
            ['name' => 'Hari 1 - Pembukaan'],
            ['date' => now()->toDateString(), 'starts_at' => '07:30', 'ends_at' => '12:00', 'is_active' => true],
        );
        AttendanceSession::firstOrCreate(
            ['name' => 'Hari 2 - Kegiatan Utama'],
            ['date' => now()->addDay()->toDateString(), 'starts_at' => '08:00', 'ends_at' => '16:00', 'is_active' => true],
        );

        $now = now();
        $records = Participant::fromVerifiedTroops()->pluck('id')
            ->filter(fn () => fake()->boolean(75))
            ->map(function (string $id) use ($first, $admin, $now) {
                $status = fake()->randomElement([...array_fill(0, 8, AttendanceStatus::Present), AttendanceStatus::Excused, AttendanceStatus::Absent]);

                return [
                    'attendance_session_id' => $first->id,
                    'participant_id' => $id,
                    'status' => $status->value,
                    'checked_in_at' => $status === AttendanceStatus::Present ? $now->copy()->setTime(7, fake()->numberBetween(15, 59)) : null,
                    'recorded_by' => $admin?->id,
                    'note' => $status === AttendanceStatus::Excused ? 'Sakit' : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

        Attendance::where('attendance_session_id', $first->id)->delete();
        foreach ($records->chunk(200) as $chunk) {
            Attendance::insert($chunk->values()->all());
        }
    }

    /**
     * Daftar satuan pendidikan di Kota Padangsidimpuan beserta jenjangnya.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function schools(): array
    {
        $list = [];

        foreach (range(1, 11) as $n) {
            $list[] = ["SMP Negeri {$n} Padangsidimpuan", 'smp'];
        }
        foreach (range(1, 8) as $n) {
            $list[] = ["SMA Negeri {$n} Padangsidimpuan", 'sma'];
        }
        foreach (range(1, 3) as $n) {
            $list[] = ["SMK Negeri {$n} Padangsidimpuan", 'sma'];
        }
        foreach ([200101, 200104, 200107, 200110, 200113, 200116, 200119, 200122, 200125, 200128, 200203, 200206] as $n) {
            $list[] = ["SD Negeri {$n} Padangsidimpuan", 'sd'];
        }

        return [
            ...$list,
            ['MTs Negeri 1 Padangsidimpuan', 'smp'],
            ['MTs Negeri 2 Padangsidimpuan', 'smp'],
            ['MAN 1 Padangsidimpuan', 'sma'],
            ['MAN 2 Padangsidimpuan', 'sma'],
            ['SMP Muhammadiyah Padangsidimpuan', 'smp'],
            ['SMA Muhammadiyah Padangsidimpuan', 'sma'],
            ['SMP Swasta Nurul Ilmi Padangsidimpuan', 'smp'],
            ['SMA Swasta Nurul Ilmi Padangsidimpuan', 'sma'],
            ['SMA Swasta Kartika I-5 Padangsidimpuan', 'sma'],
            ['SMK Swasta Kesehatan Padangsidimpuan', 'sma'],
            ['Pondok Pesantren Al-Abraar Padangsidimpuan', 'sma'],
        ];
    }

    private function seedTaskSubmissions(?User $admin): void
    {
        $participants = Participant::fromVerifiedTroops()->inRandomOrder()->take(12)->get();
        if ($participants->isEmpty()) {
            return;
        }

        $disk = Storage::disk('local');
        $dummyPdf = DemoDocuments::pdf(['LAPORAN TUGAS JOTA-JOTI', 'Kwarcab Kota Padangsidimpuan']);

        $jotaPath = 'tasks/jota/demo-jota.pdf';
        $jotiPath = 'tasks/joti/demo-joti.pdf';
        $disk->put($jotaPath, $dummyPdf);
        $disk->put($jotiPath, $dummyPdf);

        $statusPlans = [
            TaskStatus::Completed,
            TaskStatus::Completed,
            TaskStatus::Completed,
            TaskStatus::Pending,
            TaskStatus::Pending,
            TaskStatus::Revision,
            TaskStatus::Revision,
            TaskStatus::Rejected,
        ];

        foreach ($participants as $i => $participant) {
            $status = $statusPlans[$i % count($statusPlans)];
            TaskSubmission::create([
                'participant_id' => $participant->id,
                'jid' => 'YB6'.fake()->regexify('[A-Z]{3}'),
                'jjid' => 'JJID'.fake()->numerify('#####'),
                'jota_file_path' => $jotaPath,
                'joti_file_path' => $jotiPath,
                'notes' => fake()->boolean(60) ? 'Laporan pelaksanaan kegiatan JOTA dan JOTI.' : null,
                'jota_status' => $status,
                'jota_admin_note' => $status === TaskStatus::Revision ? 'Laporan logsheet QSO perlu dilengkapi format RST.' : ($status === TaskStatus::Completed ? 'Logsheet QSO lengkap dan valid.' : null),
                'joti_status' => $status === TaskStatus::Rejected ? TaskStatus::Rejected : TaskStatus::Completed,
                'joti_admin_note' => $status === TaskStatus::Completed ? 'Bukti interaksi IRC / ScoutLink terverifikasi.' : null,
                'final_status' => $status,
                'admin_notes' => $status === TaskStatus::Revision ? 'Mohon unggah perbaikan tugas sesuai arahan panitia.' : null,
                'assessed_by' => $status !== TaskStatus::Pending ? $admin?->id : null,
                'assessed_at' => $status !== TaskStatus::Pending ? now()->subHours(fake()->numberBetween(1, 48)) : null,
            ]);
        }
    }
}

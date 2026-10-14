<?php

namespace Tests\Feature;

use App\Imports\ParticipantImport;
use App\Models\Participant;
use App\Models\Troop;
use App\Services\ParticipantSheetParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsParticipantSheet;
use Tests\TestCase;

class ParticipantSheetParserTest extends TestCase
{
    use BuildsParticipantSheet, RefreshDatabase;

    private function parse(array $rows, ?array $headings = null, ?string $ignoreTroopId = null): array
    {
        // simpan referensi file agar file sementara tidak terhapus sebelum dibaca
        $file = $this->participantSheet($rows, $headings);

        return app(ParticipantSheetParser::class)->parse($file->getRealPath(), $ignoreTroopId);
    }

    public function test_valid_sheet_is_parsed(): void
    {
        $result = $this->parse([
            ['12.001', 'Budi Santoso', '2010-08-17', 'L'],
            ['12.002', 'Siti Aminah', '17/08/2011', 'perempuan'],
        ]);

        $this->assertTrue($result['valid']);
        $this->assertCount(2, $result['rows']);
        $this->assertSame('2011-08-17', $result['rows'][1]['birth_date']);
        $this->assertSame('P', $result['rows'][1]['gender']);
    }

    public function test_example_row_from_template_is_ignored(): void
    {
        $result = $this->parse([
            [ParticipantImport::EXAMPLE_KTA, 'Contoh Nama Peserta', '2010-08-17', 'L'],
            ['12.001', 'Budi Santoso', '2010-08-17', 'L'],
        ]);

        $this->assertTrue($result['valid']);
        $this->assertCount(1, $result['rows']);
    }

    public function test_invalid_rows_are_reported(): void
    {
        $result = $this->parse([
            ['12.001', 'Budi', '2010-08-17', 'L'],
            ['12.001', 'Duplikat', '2010-08-17', 'L'],
            ['12.003', '', 'bukan tanggal', 'X'],
        ]);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['rows'][0]['errors']);
        $this->assertNotEmpty($result['rows'][1]['errors']);
        $this->assertCount(3, $result['rows'][2]['errors']);
    }

    public function test_missing_columns_are_reported(): void
    {
        $result = $this->parse([['12.001', 'Budi']], ['No KTA', 'Nama Lengkap']);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_kta_registered_by_active_troop_is_rejected_but_rejected_troop_is_ignored(): void
    {
        Participant::factory()->for(Troop::factory())->create(['kta_number' => '12.001']);
        Participant::factory()->for(Troop::factory()->state(['status' => 'rejected']))->create(['kta_number' => '12.002']);

        $result = $this->parse([
            ['12.001', 'Budi', '2010-08-17', 'L'],
            ['12.002', 'Siti', '2010-08-17', 'P'],
        ]);

        $this->assertNotEmpty($result['rows'][0]['errors']);
        $this->assertSame([], $result['rows'][1]['errors']);
    }

    public function test_empty_kta_number_is_accepted(): void
    {
        $result = $this->parse([
            ['', 'Budi Santoso', '2010-08-17', 'L'],
            [null, 'Siti Aminah', '2011-08-17', 'P'],
        ]);

        $this->assertTrue($result['valid']);
        $this->assertCount(2, $result['rows']);
        $this->assertSame([], $result['rows'][0]['errors']);
        $this->assertSame([], $result['rows'][1]['errors']);
    }
}

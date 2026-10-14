<?php

namespace App\Livewire\Concerns;

use App\Services\ParticipantSheetParser;
use App\Services\SettingService;

/**
 * Dipakai oleh wizard pendaftaran dan form revisi untuk unggah & pratinjau Excel peserta.
 * Komponen wajib memiliki properti $participantFile (TemporaryUploadedFile).
 */
trait ParsesParticipantSheet
{
    /** @var array<int, array> */
    public array $rows = [];

    /** @var array<int, string> */
    public array $sheetErrors = [];

    public function updatedParticipantFile(): void
    {
        $this->resetErrorBag('participantFile');
        $this->validateOnly('participantFile', $this->participantFileRules());

        $result = app(ParticipantSheetParser::class)->parse($this->participantFile->getRealPath(), $this->ignoredTroopId());

        $this->rows = $result['rows'];
        $this->sheetErrors = $result['errors'];

        $minimum = app(SettingService::class)->minParticipantsPerTroop();
        if ($this->rows !== [] && count($this->rows) < $minimum) {
            $this->sheetErrors[] = __('A troop must register at least :count participants.', ['count' => $minimum]);
        }
    }

    protected function participantFileRules(): array
    {
        return ['participantFile' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120']];
    }

    protected function ignoredTroopId(): ?string
    {
        return null;
    }

    public function sheetIsValid(): bool
    {
        return $this->rows !== []
            && $this->sheetErrors === []
            && collect($this->rows)->every(fn ($row) => $row['errors'] === []);
    }
}

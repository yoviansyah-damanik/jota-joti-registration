<?php

namespace App\Livewire\Admin\Registrations;

use App\Enums\Gender;
use App\Enums\TroopStatus;
use App\Models\Participant;
use App\Models\Troop;
use App\Services\ParticipantIdService;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy]
class ParticipantTable extends Component
{
    #[Locked]
    public string $troopId;

    public string $search = '';

    public bool $showAddModal = false;

    public string $full_name = '';

    public string $birth_date = '';

    public string $gender = 'L';

    public string $kta_number = '';

    #[On('troop-reviewed')]
    public function refreshTable(): void
    {
        //
    }

    public function openAddModal(): void
    {
        $this->resetErrorBag();
        $this->reset('full_name', 'birth_date', 'gender', 'kta_number');
        $this->gender = 'L';
        $this->showAddModal = true;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
        $this->resetErrorBag();
    }

    public function addParticipant(ParticipantIdService $idService): void
    {
        $this->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:L,P'],
            'kta_number' => ['nullable', 'string', 'max:50'],
        ], attributes: [
            'full_name' => __('Full name'),
            'birth_date' => __('Date of birth'),
            'gender' => __('Gender'),
            'kta_number' => __('KTA number'),
        ]);

        $kta = filled($this->kta_number) ? trim($this->kta_number) : null;

        if ($kta !== null) {
            $isDuplicate = Participant::where('kta_number', $kta)
                ->whereHas('troop', fn ($q) => $q->whereIn('status', TroopStatus::activeValues()))
                ->exists();

            if ($isDuplicate) {
                $this->addError('kta_number', __('This KTA number is already registered.'));

                return;
            }
        }

        $troop = Troop::findOrFail($this->troopId);

        $troop->participants()->create([
            'full_name' => trim($this->full_name),
            'birth_date' => $this->birth_date,
            'gender' => Gender::from($this->gender),
            'kta_number' => $kta,
        ]);

        if ($troop->status === TroopStatus::Verified) {
            $idService->issueForTroop($troop);
        }

        $this->reset('full_name', 'birth_date', 'gender', 'kta_number', 'showAddModal');
        $this->dispatch('toast', message: __('Participant added successfully.'));
        $this->dispatch('troop-participants-updated');
    }

    public function deleteParticipant(string $participantId): void
    {
        $participant = Participant::where('troop_id', $this->troopId)->findOrFail($participantId);
        $participant->delete();

        $this->dispatch('toast', message: __('Participant removed.'));
        $this->dispatch('troop-participants-updated');
    }

    public function render()
    {
        $participants = Participant::where('troop_id', $this->troopId)
            ->search($this->search)
            ->orderBy('full_name')
            ->get();

        return view('livewire.admin.registrations.participant-table', [
            'participants' => $participants,
            'genderCounts' => $participants->countBy(fn ($p) => $p->gender->value),
        ]);
    }
}

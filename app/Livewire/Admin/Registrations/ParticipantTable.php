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

    public bool $showEditModal = false;

    public ?string $editingParticipantId = null;

    public string $full_name = '';

    public string $birth_date = '';

    public string $gender = 'L';

    public string $kta_number = '';

    public ?int $group_number = null;

    #[On('troop-reviewed')]
    public function refreshTable(): void
    {
        //
    }

    public function openAddModal(): void
    {
        $this->resetErrorBag();
        $this->reset('full_name', 'birth_date', 'gender', 'kta_number', 'group_number', 'editingParticipantId');
        $this->gender = 'L';
        $this->showAddModal = true;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
        $this->resetErrorBag();
    }

    public function openEditModal(string $participantId): void
    {
        $this->resetErrorBag();
        $participant = Participant::where('troop_id', $this->troopId)->findOrFail($participantId);
        $this->editingParticipantId = $participant->id;
        $this->full_name = $participant->full_name;
        $this->birth_date = $participant->birth_date?->format('Y-m-d') ?? '';
        $this->gender = $participant->gender->value;
        $this->kta_number = (string) ($participant->kta_number ?? '');
        $this->group_number = $participant->group_number;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset('editingParticipantId', 'full_name', 'birth_date', 'gender', 'kta_number', 'group_number');
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

    public function updateParticipant(): void
    {
        $this->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:L,P'],
            'kta_number' => ['nullable', 'string', 'max:50'],
            'group_number' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], attributes: [
            'full_name' => __('Full name'),
            'birth_date' => __('Date of birth'),
            'gender' => __('Gender'),
            'kta_number' => __('KTA number'),
            'group_number' => __('Group'),
        ]);

        $kta = filled($this->kta_number) ? trim($this->kta_number) : null;

        if ($kta !== null) {
            $isDuplicate = Participant::where('kta_number', $kta)
                ->where('id', '!=', $this->editingParticipantId)
                ->whereHas('troop', fn ($q) => $q->whereIn('status', TroopStatus::activeValues()))
                ->exists();

            if ($isDuplicate) {
                $this->addError('kta_number', __('This KTA number is already registered.'));

                return;
            }
        }

        $participant = Participant::where('troop_id', $this->troopId)->findOrFail($this->editingParticipantId);

        $participant->update([
            'full_name' => trim($this->full_name),
            'birth_date' => $this->birth_date,
            'gender' => Gender::from($this->gender),
            'kta_number' => $kta,
            'group_number' => $this->group_number ?: null,
        ]);

        $this->showEditModal = false;
        $this->reset('editingParticipantId', 'full_name', 'birth_date', 'gender', 'kta_number', 'group_number');
        $this->dispatch('toast', message: __('Participant updated successfully.'));
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
        $troop = Troop::find($this->troopId);
        $settings = app(\App\Services\SettingService::class);
        $allowedGroups = $troop ? $settings->groupsForUnit($troop->unit) : $settings->allGroupNumbers();

        $participants = Participant::where('troop_id', $this->troopId)
            ->search($this->search)
            ->orderBy('full_name')
            ->get();

        return view('livewire.admin.registrations.participant-table', [
            'participants' => $participants,
            'allowedGroups' => $allowedGroups,
            'genderCounts' => $participants->countBy(fn ($p) => $p->gender->value),
        ]);
    }
}

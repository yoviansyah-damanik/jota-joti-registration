<?php

namespace App\Livewire\Admin\Tasks;

use App\Enums\TaskStatus;
use App\Models\TaskSubmission;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Penilaian Tugas JOTA-JOTI')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public string $unitFilter = 'all';

    // State Modal Penilaian
    public bool $showAssessModal = false;

    public ?string $selectedSubmissionId = null;

    public ?TaskSubmission $selectedSubmission = null;

    public string $jota_status = 'pending';

    public ?string $jota_admin_note = null;

    public string $joti_status = 'pending';

    public ?string $joti_admin_note = null;

    public string $final_status = 'pending';

    public ?string $admin_notes = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingUnitFilter(): void
    {
        $this->resetPage();
    }

    public function openAssessModal(string $id): void
    {
        $this->resetValidation();
        $submission = TaskSubmission::with(['participant.troop', 'assessor'])->findOrFail($id);

        $this->selectedSubmissionId = $submission->id;
        $this->selectedSubmission = $submission;
        $this->jota_status = $submission->jota_status->value;
        $this->jota_admin_note = $submission->jota_admin_note;
        $this->joti_status = $submission->joti_status->value;
        $this->joti_admin_note = $submission->joti_admin_note;
        $this->final_status = $submission->final_status->value;
        $this->admin_notes = $submission->admin_notes;

        $this->showAssessModal = true;
    }

    public function closeAssessModal(): void
    {
        $this->showAssessModal = false;
        $this->selectedSubmissionId = null;
        $this->selectedSubmission = null;
    }

    public function updatedJotaStatus(): void
    {
        $this->syncFinalStatus();
    }

    public function updatedJotiStatus(): void
    {
        $this->syncFinalStatus();
    }

    public function syncFinalStatus(): void
    {
        if ($this->jota_status === TaskStatus::Completed->value && $this->joti_status === TaskStatus::Completed->value) {
            $this->final_status = TaskStatus::Completed->value;
        } elseif ($this->jota_status === TaskStatus::Revision->value || $this->joti_status === TaskStatus::Revision->value) {
            $this->final_status = TaskStatus::Revision->value;
        } elseif ($this->jota_status === TaskStatus::Rejected->value || $this->joti_status === TaskStatus::Rejected->value) {
            $this->final_status = TaskStatus::Rejected->value;
        } else {
            $this->final_status = TaskStatus::Pending->value;
        }
    }

    public function saveAssessment(): void
    {
        $this->validate([
            'jota_status' => ['required', 'in:pending,completed,revision,rejected'],
            'joti_status' => ['required', 'in:pending,completed,revision,rejected'],
            'final_status' => ['required', 'in:pending,completed,revision,rejected'],
            'jota_admin_note' => ['nullable', 'string', 'max:1000'],
            'joti_admin_note' => ['nullable', 'string', 'max:1000'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $submission = TaskSubmission::with('participant')->findOrFail($this->selectedSubmissionId);

        $submission->update([
            'jota_status' => TaskStatus::from($this->jota_status),
            'jota_admin_note' => $this->jota_admin_note ? trim($this->jota_admin_note) : null,
            'joti_status' => TaskStatus::from($this->joti_status),
            'joti_admin_note' => $this->joti_admin_note ? trim($this->joti_admin_note) : null,
            'final_status' => TaskStatus::from($this->final_status),
            'admin_notes' => $this->admin_notes ? trim($this->admin_notes) : null,
            'assessed_by' => auth()->id(),
            'assessed_at' => now(),
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => __('Penilaian tugas untuk :name berhasil disimpan.', ['name' => $submission->participant->full_name]),
        ]);

        $this->closeAssessModal();
    }

    public function render()
    {
        $query = TaskSubmission::query()
            ->with(['participant.troop', 'assessor'])
            ->latest('updated_at');

        if ($this->search !== '') {
            $query->search(trim($this->search));
        }

        if ($this->statusFilter !== 'all') {
            $query->where('final_status', $this->statusFilter);
        }

        if ($this->unitFilter !== 'all') {
            $query->whereHas('participant.troop', fn ($q) => $q->where('unit', $this->unitFilter));
        }

        $counts = [
            'total' => TaskSubmission::count(),
            'pending' => TaskSubmission::where('final_status', TaskStatus::Pending)->count(),
            'completed' => TaskSubmission::where('final_status', TaskStatus::Completed)->count(),
            'revision' => TaskSubmission::where('final_status', TaskStatus::Revision)->count(),
            'rejected' => TaskSubmission::where('final_status', TaskStatus::Rejected)->count(),
        ];

        return view('livewire.admin.tasks.index', [
            'submissions' => $query->paginate(15),
            'counts' => $counts,
        ]);
    }
}

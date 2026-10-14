<?php

namespace App\Livewire\Admin\Registrations;

use App\Enums\TroopStatus;
use App\Models\Troop;
use App\Services\RegistrationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    #[Locked]
    public string $troopId;

    public string $note = '';

    public function mount(Troop $troop): void
    {
        $this->troopId = $troop->id;
        $this->note = (string) $troop->admin_note;
    }

    public function review(string $status, RegistrationService $registrations): void
    {
        $status = TroopStatus::tryFrom($status);
        abort_if($status === null || $status === TroopStatus::Submitted, 422);

        $this->validate(['note' => ['nullable', 'string', 'max:2000']]);

        $registrations->review($this->troop(), $status, $this->note ?: null, auth()->user());

        $this->dispatch('toast', message: __('Registration marked as :status.', ['status' => $status->label()]));
        $this->dispatch('troop-reviewed');
    }

    #[On('troop-participants-updated')]
    public function refresh(): void
    {
        // Re-renders the component to update withCount('participants')
    }

    private function troop(): Troop
    {
        return Troop::findOrFail($this->troopId);
    }

    public function render()
    {
        $troop = Troop::with(['documents', 'verifier'])->withCount('participants')->findOrFail($this->troopId);

        return view('livewire.admin.registrations.show', ['troop' => $troop])->title($troop->troop_name);
    }
}

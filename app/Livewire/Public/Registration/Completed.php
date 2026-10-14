<?php

namespace App\Livewire\Public\Registration;

use App\Models\Troop;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.public')]
class Completed extends Component
{
    #[Locked]
    public ?string $troopId = null;

    public function mount()
    {
        $this->troopId = session('registration.completed');

        if (! $this->troopId) {
            return $this->redirectRoute('status', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.public.registration.completed', [
            'troop' => Troop::withCount('participants')->find($this->troopId),
        ])->title(__('Registration Submitted'));
    }
}

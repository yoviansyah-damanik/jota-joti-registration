<?php

namespace App\Livewire\Admin;

use App\Enums\TroopStatus;
use App\Models\Troop;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'latest' => Troop::withCount('participants')
                ->where('status', TroopStatus::Submitted)
                ->oldest('submitted_at')
                ->limit(5)
                ->get(),
        ])->title(__('Dashboard'));
    }
}

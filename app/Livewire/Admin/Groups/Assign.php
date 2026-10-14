<?php

namespace App\Livewire\Admin\Groups;

use App\Models\GroupAssignmentBatch;
use App\Models\Troop;
use App\Services\GroupAssignmentService;
use App\Services\SettingService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Assign extends Component
{
    public bool $resetAll = false;

    public function generate(GroupAssignmentService $assignments): void
    {
        $batch = $assignments->assign(auth()->user(), $this->resetAll);

        $this->resetAll = false;
        $this->dispatch('toast', message: __(':count participants from :troops troops received a group number.', [
            'count' => $batch->total_participants,
            'troops' => $batch->total_troops,
        ]));
        $this->dispatch('groups-assigned');
    }

    public function clear(GroupAssignmentService $assignments): void
    {
        $count = $assignments->clear();

        $this->resetAll = false;
        $this->dispatch('toast', message: __('Group numbers of :count participants were removed.', ['count' => $count]));
        $this->dispatch('groups-assigned');
    }

    public function render(SettingService $settings)
    {
        return view('livewire.admin.groups.assign', [
            'maxGroupNumber' => $settings->maxGroupNumber(),
            'minPerNumber' => $settings->minPerNumber(),
            'verifiedTroops' => Troop::verified()->count(),
            'batches' => GroupAssignmentBatch::with('executor')->latest()->limit(5)->get(),
        ])->title(__('Group Numbers'));
    }
}

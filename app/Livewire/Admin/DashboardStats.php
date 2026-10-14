<?php

namespace App\Livewire\Admin;

use App\Enums\Gender;
use App\Enums\TroopStatus;
use App\Models\Participant;
use App\Models\Troop;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class DashboardStats extends Component
{
    public function render()
    {
        $statusCounts = Troop::toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $participants = Participant::fromVerifiedTroops()
            ->toBase()
            ->selectRaw('gender, count(*) as total, sum(case when group_number is null then 0 else 1 end) as assigned')
            ->groupBy('gender')
            ->get()
            ->keyBy('gender');

        return view('livewire.admin.dashboard-stats', [
            'statusCounts' => $statusCounts,
            'totalParticipants' => (int) $participants->sum('total'),
            'assigned' => (int) $participants->sum('assigned'),
            'male' => (int) ($participants[Gender::Male->value]->total ?? 0),
            'female' => (int) ($participants[Gender::Female->value]->total ?? 0),
            'statuses' => TroopStatus::cases(),
        ]);
    }
}

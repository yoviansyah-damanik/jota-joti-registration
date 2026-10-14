<?php

namespace App\Livewire\Admin\Groups;

use App\Models\Troop;
use App\Services\GroupAssignmentService;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy]
class BalanceReport extends Component
{
    #[On('groups-assigned')]
    public function refreshReport(): void
    {
        //
    }

    public function render(GroupAssignmentService $assignments)
    {
        $report = $assignments->balanceReport();

        return view('livewire.admin.groups.balance-report', [
            'report' => $report,
            'peak' => max(1, collect($report['numbers'])->max('total')),
            'issueTroops' => Troop::whereKey(array_keys($report['troop_issues']))->get(['id', 'troop_number', 'troop_number_pair', 'troop_name']),
        ]);
    }
}

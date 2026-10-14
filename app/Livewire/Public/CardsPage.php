<?php

namespace App\Livewire\Public;

use App\Models\Participant;
use App\Models\Troop;
use App\Services\GroupAssignmentService;
use App\Services\SettingService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman cetak kartu peserta; menampilkan pemberitahuan bila pembagian grup belum selesai.
 */
#[Layout('layouts.public')]
class CardsPage extends Component
{
    public function render(GroupAssignmentService $groups, SettingService $settings)
    {
        return view('livewire.public.cards-page', [
            'available' => $groups->isCompleted(),
            'stats' => [
                'troops' => Troop::verified()->count(),
                'participants' => Participant::fromVerifiedTroops()->count(),
                'groups' => $settings->maxGroupNumber(),
            ],
        ])->title(__('Print Participant Cards'));
    }
}

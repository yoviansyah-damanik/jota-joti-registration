<?php

namespace App\Livewire\Public;

use App\Models\Participant;
use App\Models\Troop;
use App\Services\GroupAssignmentService;
use App\Services\SettingService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class TermsPage extends Component
{
    public bool $agreed = false;

    public function proceed()
    {
        $this->validate(['agreed' => 'accepted'], ['agreed.accepted' => __('Please confirm that all requirements are ready.')]);

        if (! app(SettingService::class)->registrationOpen()) {
            return;
        }

        session()->put('registration.agreed', true);

        return $this->redirectRoute('register', navigate: true);
    }

    public function render(SettingService $settings, GroupAssignmentService $groups)
    {
        $additionalTerms = collect(preg_split('/\r\n|\r|\n/', (string) $settings->get('additional_terms')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        return view('livewire.public.terms-page', [
            'settings' => $settings,
            'groupsCompleted' => $groups->isCompleted(),
            'certificatesPublished' => $settings->eventCompleted(),
            'stats' => [
                'troops' => Troop::verified()->count(),
                'participants' => Participant::fromVerifiedTroops()->count(),
                'groups' => $settings->maxGroupNumber(),
            ],
            'additionalTerms' => $additionalTerms,
        ])->title(__('Registration Requirements'));
    }
}

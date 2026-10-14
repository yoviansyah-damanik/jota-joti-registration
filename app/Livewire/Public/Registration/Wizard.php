<?php

namespace App\Livewire\Public\Registration;

use App\Services\SettingService;
use App\Support\RegistrationDraft;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.public')]
class Wizard extends Component
{
    public int $step = 1;

    public function mount(RegistrationDraft $draft, SettingService $settings)
    {
        if (! session('registration.agreed') || ! $settings->registrationOpen()) {
            return $this->redirectRoute('home', navigate: true);
        }

        $this->step = $draft->currentStep();
    }

    #[On('step-completed')]
    public function next(RegistrationDraft $draft): void
    {
        $this->step = $draft->currentStep();
    }

    /**
     * Hanya boleh berpindah ke langkah yang sudah pernah dilewati.
     */
    public function goTo(int $step, RegistrationDraft $draft): void
    {
        if ($step >= 1 && $step <= $draft->currentStep()) {
            $this->step = $step;
        }
    }

    /**
     * Mereset seluruh formulir pendaftaran wizard dan kembali ke Langkah 1.
     */
    public function resetDraft(RegistrationDraft $draft): void
    {
        $draft->clear();
        session()->put('registration.agreed', true);
        $this->step = 1;

        $this->dispatch('toast', [
            'type' => 'info',
            'message' => __('Formulir pendaftaran telah direset. Silakan mulai kembali dari Langkah 1.'),
        ]);

        $this->redirect(route('register'), navigate: true);
    }

    public function render(RegistrationDraft $draft, SettingService $settings)
    {
        return view('livewire.public.registration.wizard', [
            'steps' => [
                1 => ['title' => __('Troop Data'), 'description' => __('Number, name, unit, and person in charge')],
                2 => ['title' => __('Participant Data'), 'description' => __('Upload the participant Excel file')],
                3 => ['title' => __('Documents'), 'description' => __('Assignment letter and proof of payment')],
                4 => ['title' => __('Review & Submit'), 'description' => __('Check everything, then submit')],
            ],
            // langkah pertama yang belum lengkap; langkah sebelumnya dianggap selesai
            'reached' => $draft->currentStep(),
            'contact' => $settings->get('contact_info'),
        ])->title(__('Registration Form'));
    }
}

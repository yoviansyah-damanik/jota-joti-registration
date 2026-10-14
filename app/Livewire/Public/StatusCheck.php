<?php

namespace App\Livewire\Public;

use App\Livewire\Concerns\ThrottlesActions;
use App\Models\Troop;
use App\Services\CertificateService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.public')]
class StatusCheck extends Component
{
    use ThrottlesActions;

    #[Url(except: '')]
    public string $code = '';

    #[Locked]
    public ?string $troopId = null;

    /**
     * Link dari halaman selesai (?code=...) langsung menampilkan status.
     */
    public function mount(): void
    {
        if ($this->code !== '') {
            $this->check();
        }
    }

    public function check(): void
    {
        $this->code = strtoupper(trim($this->code));

        $this->validate(
            ['code' => ['required', 'string', 'max:20']],
            attributes: ['code' => __('registration code')],
        );

        $this->throttle('status-check', 'code');

        $this->troopId = Troop::where('registration_code', $this->code)->value('id');

        if (! $this->troopId) {
            $this->addError('code', __('No registration matches this code.'));
        }
    }

    /**
     * Render ulang setelah revisi dikirim agar status terbaru tampil.
     */
    #[On('revision-submitted')]
    public function refreshStatus(): void
    {
        //
    }

    public function checkAnother(): void
    {
        $this->reset('code', 'troopId');
    }

    public function render(CertificateService $certificates)
    {
        $troop = $this->troopId
            ? Troop::with(['participants' => fn ($q) => $q->reorder()->orderBy('group_number')->orderBy('full_name')])->find($this->troopId)
            : null;

        $certificateAvailable = $troop && $certificates->published() && $certificates->troopEligible($troop);

        return view('livewire.public.status-check', [
            'troop' => $troop,
            'certificateAvailable' => $certificateAvailable,
            'certificateParticipants' => $certificateAvailable ? $certificates->eligibleParticipants($troop) : collect(),
        ])->title(__('Check Status'));
    }
}

<?php

namespace App\Livewire\Public\Registration;

use App\Enums\ScoutUnit;
use App\Services\RegistrationService;
use App\Support\RegistrationDraft;
use App\Support\TroopNumber;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class Step1TroopData extends Component
{
    public string $troop_number = '';

    public string $troop_name = '';

    public string $unit = '';

    public string $pic_name = '';

    public string $pic_phone = '';

    public string $pic_email = '';

    public function mount(RegistrationDraft $draft): void
    {
        $this->fill($draft->troop());
    }

    public function save(RegistrationDraft $draft, RegistrationService $registrations): void
    {
        // jaga-jaga bila input tidak diformat browser, mis. "01083" → "01-083"
        $this->troop_number = TroopNumber::normalize($this->troop_number);

        $data = $this->validate([
            'troop_number' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if (! preg_match(TroopNumber::PATTERN, $value)) {
                    $fail(__('Troop number must use the xx-xxx format, e.g. 01-083.'));
                } elseif (! TroopNumber::isOdd($value)) {
                    $fail(__('The first troop number must be odd, e.g. 01-083.'));
                } elseif (! TroopNumber::isValid($value)) {
                    $fail(__('Troop number must use the xx-xxx format, e.g. 01-083.'));
                }
            }],
            'troop_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::enum(ScoutUnit::class)],
            'pic_name' => ['required', 'string', 'max:255'],
            'pic_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]{8,}$/'],
            'pic_email' => ['nullable', 'email', 'max:255'],
        ], attributes: [
            'troop_number' => __('troop number'),
            'troop_name' => __('troop name'),
            'unit' => __('unit'),
            'pic_name' => __('person in charge'),
            'pic_phone' => __('phone number'),
            'pic_email' => __('email'),
        ], messages: [
            'unit.required' => __('Please choose a unit (Penggalang or Penegak/Pandega).'),
            'unit.enum' => __('Please choose a unit (Penggalang or Penegak/Pandega).'),
        ]);

        if ($registrations->isTroopNumberTaken($data['troop_number'])) {
            $this->addError('troop_number', __('This troop number is already registered. Use the status page to check it.'));

            return;
        }

        $draft->put('troop', $data);
        $this->dispatch('step-completed');
    }

    public function render()
    {
        return view('livewire.public.registration.step1-troop-data', ['units' => ScoutUnit::cases()]);
    }
}

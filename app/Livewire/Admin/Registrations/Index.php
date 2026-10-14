<?php

namespace App\Livewire\Admin\Registrations;

use App\Enums\ScoutUnit;
use App\Enums\TroopStatus;
use App\Livewire\Concerns\UsesDaisyPagination;
use App\Models\Troop;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    use UsesDaisyPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $unit = '';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'unit'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $troops = Troop::query()
            ->withCount('participants')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('troop_number', 'like', "%{$this->search}%")
                ->orWhere('troop_number_pair', 'like', "%{$this->search}%")
                ->orWhere('troop_name', 'like', "%{$this->search}%")
                ->orWhere('registration_code', 'like', "%{$this->search}%")
                ->orWhere('pic_name', 'like', "%{$this->search}%")))
            ->when(TroopStatus::tryFrom($this->status), fn ($q, $status) => $q->where('status', $status))
            ->when(ScoutUnit::tryFrom($this->unit), fn ($q, $unit) => $q->where('unit', $unit))
            ->latest('submitted_at')
            ->paginate(15)->onEachSide(1);

        return view('livewire.admin.registrations.index', [
            'troops' => $troops,
            'unitOptions' => ScoutUnit::options(),
            'statusOptions' => collect(TroopStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all(),
        ])->title(__('Registrations'));
    }
}

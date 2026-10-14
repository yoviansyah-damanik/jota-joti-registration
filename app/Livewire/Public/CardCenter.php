<?php

namespace App\Livewire\Public;

use App\Livewire\Concerns\UsesDaisyPagination;
use App\Models\Troop;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Daftar gudep di halaman cetak kartu: pilih gudep (nomor & nama), lalu tampilkan data kartu pesertanya.
 */
#[Lazy]
class CardCenter extends Component
{
    use UsesDaisyPagination;

    public string $search = '';

    #[Locked]
    public ?string $troopId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function select(string $troopId): void
    {
        $this->troopId = Troop::verified()->whereKey($troopId)->value('id');
    }

    public function back(): void
    {
        $this->reset('troopId');
    }

    public function render()
    {
        $selected = $this->troopId
            ? Troop::verified()->with(['participants' => fn ($q) => $q->reorder()->orderBy('group_number')->orderBy('full_name')])->find($this->troopId)
            : null;

        $troops = $selected ? null : Troop::verified()
            ->withCount('participants')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('troop_number', 'like', "%{$this->search}%")
                ->orWhere('troop_number_pair', 'like', "%{$this->search}%")
                ->orWhere('troop_name', 'like', "%{$this->search}%")))
            ->orderBy('troop_number')
            ->paginate(12)->onEachSide(1);

        return view('livewire.public.card-center', ['troops' => $troops, 'selected' => $selected]);
    }
}

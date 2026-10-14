<?php

namespace App\Livewire\Admin\Groups;

use App\Enums\Gender;
use App\Livewire\Concerns\UsesDaisyPagination;
use App\Models\Participant;
use App\Models\Troop;
use App\Services\SettingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class Details extends Component
{
    use UsesDaisyPagination;

    #[Url(except: '')]
    public ?string $group = '';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $troopId = '';

    #[Url(except: '')]
    public string $gender = '';

    #[Url(except: 'list')]
    public string $viewMode = 'list'; // 'list' atau 'matrix'

    public function updated($property): void
    {
        if (in_array($property, ['group', 'search', 'troopId', 'gender'], true)) {
            $this->resetPage();
        }
    }

    public function setGroup(?string $group): void
    {
        $this->group = $group === '' ? null : $group;
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['list', 'matrix'], true) ? $mode : 'list';
    }

    public function resetFilters(): void
    {
        $this->reset(['group', 'search', 'troopId', 'gender']);
        $this->resetPage();
    }

    public function render(SettingService $settings)
    {
        $maxGroupNumber = $settings->maxGroupNumber();
        $minPerNumber = $settings->minPerNumber();

        // Statistik peserta terverifikasi
        $verifiedParticipants = Participant::fromVerifiedTroops();
        $totalVerified = (clone $verifiedParticipants)->count();
        $totalAssigned = (clone $verifiedParticipants)->whereNotNull('group_number')->count();
        $totalUnassigned = (clone $verifiedParticipants)->whereNull('group_number')->count();
        $totalMale = (clone $verifiedParticipants)->where('gender', Gender::Male)->count();
        $totalFemale = (clone $verifiedParticipants)->where('gender', Gender::Female)->count();

        // Hitungan per grup untuk pill filter cepat
        $groupCounts = (clone $verifiedParticipants)
            ->whereNotNull('group_number')
            ->selectRaw('group_number, count(*) as count')
            ->groupBy('group_number')
            ->pluck('count', 'group_number')
            ->all();

        // Daftar gudep terverifikasi untuk opsi filter
        $verifiedTroops = Troop::verified()
            ->orderBy('troop_number')
            ->get(['id', 'troop_number', 'troop_number_pair', 'troop_name']);

        // Data mode daftar peserta (list)
        $participants = null;
        if ($this->viewMode === 'list') {
            $query = Participant::fromVerifiedTroops()
                ->with('troop')
                ->when($this->group !== '' && $this->group !== null, function ($q) {
                    if ($this->group === 'unassigned') {
                        $q->whereNull('group_number');
                    } else {
                        $q->where('group_number', (int) $this->group);
                    }
                })
                ->when($this->troopId, fn ($q) => $q->where('troop_id', $this->troopId))
                ->when($this->gender, fn ($q) => $q->where('gender', $this->gender))
                ->when($this->search, function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('full_name', 'like', "%{$this->search}%")
                            ->orWhere('attendance_code', 'like', "%{$this->search}%")
                            ->orWhere('kta_number', 'like', "%{$this->search}%")
                            ->orWhereHas('troop', fn ($t) => $t
                                ->where('troop_name', 'like', "%{$this->search}%")
                                ->orWhere('troop_number', 'like', "%{$this->search}%")
                                ->orWhere('troop_number_pair', 'like', "%{$this->search}%")
                            );
                    });
                })
                ->orderByRaw('group_number is null, group_number asc')
                ->orderBy('full_name');

            $participants = $query->paginate(20)->onEachSide(1);
        }

        // Data mode matriks persebaran gudep x grup (matrix)
        $matrixData = null;
        $penggalangGroups = $settings->penggalangGroups();
        $penegakGroups = $settings->penegakGroups();
        $allGroupNumbers = $settings->allGroupNumbers();

        if ($this->viewMode === 'matrix') {
            $matrixRows = Participant::fromVerifiedTroops()
                ->selectRaw('troop_id, group_number, count(*) as count')
                ->groupBy('troop_id', 'group_number')
                ->get();

            $troopsList = Troop::verified()
                ->withCount('participants')
                ->orderBy('unit')
                ->orderBy('troop_number')
                ->get();

            $matrixData = $troopsList->map(function ($troop) use ($matrixRows, $allGroupNumbers, $settings) {
                $troopRows = $matrixRows->where('troop_id', $troop->id);
                $allowedGroups = $settings->groupsForUnit($troop->unit);
                $groups = [];
                foreach ($allGroupNumbers as $g) {
                    $groups[$g] = (int) ($troopRows->firstWhere('group_number', $g)?->count ?? 0);
                }
                $unassigned = (int) ($troopRows->firstWhere('group_number', null)?->count ?? 0);

                return [
                    'troop' => $troop,
                    'total' => $troop->participants_count,
                    'groups' => $groups,
                    'allowed_groups' => $allowedGroups,
                    'unassigned' => $unassigned,
                ];
            });
        }

        return view('livewire.admin.groups.details', [
            'maxGroupNumber' => $maxGroupNumber,
            'minPerNumber' => $minPerNumber,
            'penggalangGroups' => $penggalangGroups,
            'penegakGroups' => $penegakGroups,
            'allGroupNumbers' => $allGroupNumbers,
            'totalVerified' => $totalVerified,
            'totalAssigned' => $totalAssigned,
            'totalUnassigned' => $totalUnassigned,
            'totalMale' => $totalMale,
            'totalFemale' => $totalFemale,
            'groupCounts' => $groupCounts,
            'verifiedTroops' => $verifiedTroops,
            'participants' => $participants,
            'matrixData' => $matrixData,
        ])->title(__('Group Breakdown Details'));
    }
}

<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-ui.page-header :title="__('Group Breakdown Details')" :subtitle="__('Detailed participant distribution and troop breakdown across group numbers 1 through :max.', ['max' => $maxGroupNumber])">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.groups') }}" wire:navigate class="btn btn-ghost btn-sm">
                <x-ui.icon name="arrow-left" class="size-4" /> {{ __('Back') }}
            </a>
            <a href="{{ route('admin.print.attendance.manual', array_filter(['group_by' => 'group', 'group_number' => $group !== 'unassigned' ? $group : null, 'troop_id' => $troopId])) }}" target="_blank" class="btn btn-outline btn-sm">
                <x-ui.icon name="printer" class="size-4" /> {{ __('Print Attendance') }}
            </a>
            <a href="{{ route('admin.exports.participants', ['verified' => 1]) }}" class="btn btn-secondary btn-sm">
                <x-ui.icon name="download" class="size-4" /> {{ __('Export result') }}
            </a>
        </div>
    </x-ui.page-header>

    {{-- Ringkasan Statistik --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="bg-base-100 rounded-box border-base-200 border p-4 shadow-xs">
            <p class="text-base-content/60 text-xs font-medium uppercase tracking-wider">{{ __('Verified participants') }}</p>
            <p class="mt-1 text-2xl font-bold">{{ $totalVerified }}</p>
            <p class="text-base-content/60 mt-0.5 text-xs">{{ count($verifiedTroops) }} {{ __('troops') }}</p>
        </div>

        <div class="bg-base-100 rounded-box border-base-200 border p-4 shadow-xs">
            <p class="text-base-content/60 text-xs font-medium uppercase tracking-wider">{{ __('Group number assigned') }}</p>
            <p class="text-primary mt-1 text-2xl font-bold">{{ $totalAssigned }}</p>
            <p class="text-base-content/60 mt-0.5 text-xs">{{ $totalVerified > 0 ? round(($totalAssigned / $totalVerified) * 100) : 0 }}% {{ __('assigned') }}</p>
        </div>

        <div class="bg-base-100 rounded-box border-base-200 border p-4 shadow-xs">
            <p class="text-base-content/60 text-xs font-medium uppercase tracking-wider">{{ __('Not assigned yet') }}</p>
            <p @class(['mt-1 text-2xl font-bold', 'text-warning' => $totalUnassigned > 0, 'text-success' => $totalUnassigned === 0])>
                {{ $totalUnassigned }}
            </p>
            <p class="text-base-content/60 mt-0.5 text-xs">{{ $totalUnassigned === 0 ? __('All completed') : __('Need assignment') }}</p>
        </div>

        <div class="bg-base-100 rounded-box border-base-200 border p-4 shadow-xs">
            <p class="text-base-content/60 text-xs font-medium uppercase tracking-wider">{{ __('Gender composition') }}</p>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-primary text-xl font-bold">{{ $totalMale }} <span class="text-xs font-normal text-base-content/60">{{ __('Male') }}</span></span>
                <span class="text-base-content/40">·</span>
                <span class="text-secondary text-xl font-bold">{{ $totalFemale }} <span class="text-xs font-normal text-base-content/60">{{ __('Female') }}</span></span>
            </div>
            <p class="text-base-content/60 mt-0.5 text-xs">{{ $totalVerified }} {{ __('total') }}</p>
        </div>
    </div>

    {{-- Pilihan Tab Tampilan --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="tabs tabs-box bg-base-200/60 p-1">
            <button type="button" wire:click="setViewMode('list')" @class(['tab', 'tab-active font-semibold' => $viewMode === 'list'])>
                <x-ui.icon name="users" class="size-4 mr-1.5" /> {{ __('Participant List') }}
            </button>
            <button type="button" wire:click="setViewMode('matrix')" @class(['tab', 'tab-active font-semibold' => $viewMode === 'matrix'])>
                <x-ui.icon name="table" class="size-4 mr-1.5" /> {{ __('Troop Distribution Matrix') }}
            </button>
        </div>
    </div>

    @if ($viewMode === 'list')
        {{-- Mode Daftar Peserta --}}
        <x-ui.card>
            {{-- Quick Filter Nomor Grup (Pills) --}}
            <div class="border-base-200 mb-4 flex flex-wrap items-center gap-2 border-b pb-4">
                <span class="text-base-content/60 text-xs font-semibold mr-1">{{ __('Filter group:') }}</span>
                <button type="button" wire:click="setGroup('')" @class([
                    'btn btn-xs rounded-full',
                    'btn-primary' => $group === '' || $group === null,
                    'btn-ghost' => $group !== '' && $group !== null,
                ])>
                    {{ __('All groups') }} ({{ $totalVerified }})
                </button>

                <div class="inline-flex items-center gap-1.5 pl-2 border-l border-base-300">
                    <span class="badge badge-info badge-outline badge-xs font-bold uppercase">{{ __('Penggalang') }}</span>
                    @foreach ($penggalangGroups as $n)
                        <button type="button" wire:click="setGroup('{{ $n }}')" @class([
                            'btn btn-xs rounded-full gap-1.5',
                            'btn-neutral ring-2 ring-primary ring-offset-2' => $group === (string) $n,
                            'btn-ghost' => $group !== (string) $n,
                        ])>
                            <x-ui.group-badge :number="$n" size="xs" />
                            <span>{{ $groupCounts[$n] ?? 0 }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="inline-flex items-center gap-1.5 pl-2 border-l border-base-300">
                    <span class="badge badge-success badge-outline badge-xs font-bold uppercase">{{ __('Penegak') }}</span>
                    @foreach ($penegakGroups as $n)
                        <button type="button" wire:click="setGroup('{{ $n }}')" @class([
                            'btn btn-xs rounded-full gap-1.5',
                            'btn-neutral ring-2 ring-primary ring-offset-2' => $group === (string) $n,
                            'btn-ghost' => $group !== (string) $n,
                        ])>
                            <x-ui.group-badge :number="$n" size="xs" />
                            <span>{{ $groupCounts[$n] ?? 0 }}</span>
                        </button>
                    @endforeach
                </div>

                @if ($totalUnassigned > 0)
                    <button type="button" wire:click="setGroup('unassigned')" @class([
                        'btn btn-xs rounded-full gap-1 ml-auto',
                        'btn-warning' => $group === 'unassigned',
                        'btn-ghost text-warning' => $group !== 'unassigned',
                    ])>
                        <x-ui.icon name="exclamation" class="size-3.5" />
                        <span>{{ __('Unassigned') }} ({{ $totalUnassigned }})</span>
                    </button>
                @endif
            </div>

            {{-- Filter Form --}}
            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <div class="md:col-span-2">
                    <x-ui.input
                        name="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search by participant name, attendance ID, troop...') }}"
                        icon="search"
                    />
                </div>

                <div>
                    <select wire:model.live="troopId" class="select select-bordered w-full">
                        <option value="">{{ __('All troops') }}</option>
                        @foreach ($verifiedTroops as $t)
                            <option value="{{ $t->id }}">{{ $t->number_label }} – {{ $t->troop_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select wire:model.live="gender" class="select select-bordered w-full">
                        <option value="">{{ __('All genders') }}</option>
                        <option value="L">{{ __('Male') }}</option>
                        <option value="P">{{ __('Female') }}</option>
                    </select>
                </div>
            </div>

            @if ($group !== '' || $search !== '' || $troopId !== '' || $gender !== '')
                <div class="mt-3 flex items-center justify-between text-xs">
                    <span class="text-base-content/60">{{ __('Active filters applied') }}</span>
                    <button type="button" wire:click="resetFilters" class="link link-hover text-primary font-medium">
                        {{ __('Reset filters') }}
                    </button>
                </div>
            @endif

            {{-- Tabel Peserta --}}
            <div class="mt-4 overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">#</th>
                            <th class="text-center">{{ __('Group') }}</th>
                            <th>{{ __('Attendance ID') }}</th>
                            <th>{{ __('Full name') }}</th>
                            <th class="text-center">{{ __('Gender') }}</th>
                            <th>{{ __('Troop') }}</th>
                            <th>{{ __('Unit') }}</th>
                            <th>{{ __('KTA number') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($participants as $index => $participant)
                            <tr wire:key="p-{{ $participant->id }}" class="hover">
                                <td class="text-center text-xs text-base-content/60">
                                    {{ $participants->firstItem() + $index }}
                                </td>
                                <td class="text-center">
                                    <x-ui.group-badge :number="$participant->group_number" size="md" />
                                </td>
                                <td>
                                    <span class="font-mono text-xs font-semibold">{{ $participant->attendance_code }}</span>
                                </td>
                                <td>
                                    <span class="font-semibold">{{ $participant->full_name }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($participant->gender === \App\Enums\Gender::Male)
                                        <span class="badge badge-soft badge-primary badge-xs">{{ __('Male') }}</span>
                                    @else
                                        <span class="badge badge-soft badge-secondary badge-xs">{{ __('Female') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <span class="font-medium text-xs">{{ $participant->troop->number_label }}</span>
                                        <span class="text-base-content/70 text-xs block truncate max-w-xs">{{ $participant->troop->troop_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-xs">{{ $participant->troop->unit?->label() ?? '-' }}</span>
                                </td>
                                <td class="text-xs text-base-content/70">
                                    {{ $participant->kta_number ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-ui.empty-state icon="users" :title="__('No participants found matching current filters.')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($participants->hasPages())
                <div class="mt-4 border-base-200 border-t pt-4">
                    {{ $participants->links() }}
                </div>
            @endif
        </x-ui.card>
    @else
        {{-- Mode Matriks Persebaran Gudep --}}
        <x-ui.card :title="__('Troop Distribution Matrix')" icon="table">
            <p class="text-base-content/60 -mt-2 mb-4 text-xs">
                {{ __('Shows the number of participants each troop has in every group. Groups highlighted below minimum: :min per number.', ['min' => $minPerNumber]) }}
            </p>

            <div class="overflow-x-auto">
                <table class="table table-sm table-zebra">
                    <thead>
                        <tr>
                            <th rowspan="2" class="w-10 text-center">#</th>
                            <th rowspan="2">{{ __('Troop') }}</th>
                            <th rowspan="2">{{ __('Unit') }}</th>
                            <th rowspan="2" class="text-center font-bold">{{ __('Total') }}</th>
                            @if (count($penggalangGroups) > 0)
                                <th colspan="{{ count($penggalangGroups) }}" class="text-center bg-sky-500/10 text-sky-700 dark:text-sky-300 font-bold uppercase text-[11px] tracking-wider border-b border-sky-500/30">
                                    {{ __('Penggalang Block') }}
                                </th>
                            @endif
                            @if (count($penegakGroups) > 0)
                                <th colspan="{{ count($penegakGroups) }}" class="text-center bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 font-bold uppercase text-[11px] tracking-wider border-b border-emerald-500/30">
                                    {{ __('Penegak / Pandega Block') }}
                                </th>
                            @endif
                            @if ($totalUnassigned > 0)
                                <th rowspan="2" class="text-center text-warning">{{ __('Unassigned') }}</th>
                            @endif
                        </tr>
                        <tr>
                            @foreach ($allGroupNumbers as $g)
                                <th class="text-center">
                                    <x-ui.group-badge :number="$g" size="sm" />
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($matrixData as $idx => $row)
                            <tr wire:key="matrix-{{ $row['troop']->id }}">
                                <td class="text-center text-xs text-base-content/60">{{ $idx + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.registrations.show', $row['troop']->id) }}" wire:navigate class="link link-hover font-semibold text-xs">
                                        {{ $row['troop']->number_label }} – {{ $row['troop']->troop_name }}
                                    </a>
                                </td>
                                <td class="text-xs">
                                    @if ($row['troop']->unit === \App\Enums\ScoutUnit::Penggalang)
                                        <span class="badge badge-info badge-soft badge-xs">{{ $row['troop']->unit->label() }}</span>
                                    @elseif ($row['troop']->unit === \App\Enums\ScoutUnit::PenegakPandega)
                                        <span class="badge badge-success badge-soft badge-xs">{{ $row['troop']->unit->label() }}</span>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td class="text-center font-bold text-xs">{{ $row['total'] }}</td>

                                @foreach ($allGroupNumbers as $g)
                                    @php
                                        $isAllowed = in_array($g, $row['allowed_groups'], true);
                                        $count = $row['groups'][$g] ?? 0;
                                        $isLow = $isAllowed && $count < $minPerNumber && $row['total'] >= (count($row['allowed_groups']) * $minPerNumber);
                                    @endphp
                                    <td @class([
                                        'text-center text-xs',
                                        'bg-base-200/50 text-base-content/25' => ! $isAllowed,
                                    ])>
                                        @if (! $isAllowed)
                                            <span title="{{ __('Group not assigned to this unit') }}">&ndash;</span>
                                        @elseif ($count > 0)
                                            <span @class([
                                                'inline-block px-2 py-0.5 rounded-sm font-semibold',
                                                'bg-warning/20 text-warning-content' => $isLow,
                                                'font-medium text-base-content' => ! $isLow,
                                            ])>
                                                {{ $count }}
                                            </span>
                                        @else
                                            <span class="text-base-content/30">0</span>
                                        @endif
                                    </td>
                                @endforeach

                                @if ($totalUnassigned > 0)
                                    <td class="text-center text-xs">
                                        @if ($row['unassigned'] > 0)
                                            <span class="badge badge-warning badge-xs font-semibold">{{ $row['unassigned'] }}</span>
                                        @else
                                            <span class="text-base-content/30">-</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + count($allGroupNumbers) + ($totalUnassigned > 0 ? 1 : 0) }}">
                                    <x-ui.empty-state icon="table" :title="__('No verified troops found.')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-bold border-t-2 border-base-300">
                            <td colspan="3" class="text-right uppercase tracking-wider text-xs">{{ __('Total') }}</td>
                            <td class="text-center text-xs font-extrabold">{{ $totalVerified }}</td>
                            @foreach ($allGroupNumbers as $g)
                                <td class="text-center text-xs font-extrabold">
                                    {{ $groupCounts[$g] ?? 0 }}
                                </td>
                            @endforeach
                            @if ($totalUnassigned > 0)
                                <td class="text-center text-xs font-extrabold text-warning">
                                    {{ $totalUnassigned }}
                                </td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-ui.card>
    @endif
</div>

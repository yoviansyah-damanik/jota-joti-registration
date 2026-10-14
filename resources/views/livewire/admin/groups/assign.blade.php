<div class="space-y-6">
    <x-ui.page-header :title="__('Group Numbers')" :subtitle="__('Randomly assign group numbers to participants of verified troops.')">
        <a href="{{ route('admin.print.attendance.manual', ['group_by' => 'group']) }}" target="_blank" class="btn btn-outline btn-sm">
            <x-ui.icon name="printer" class="size-4" /> {{ __('Print Group Attendance') }}
        </a>
        <a href="{{ route('admin.groups.details') }}" wire:navigate class="btn btn-primary btn-sm">
            <x-ui.icon name="user-group" class="size-4" /> {{ __('Group Breakdown Details') }}
        </a>
        <a href="{{ route('admin.exports.participants', ['verified' => 1]) }}" class="btn btn-secondary btn-sm">
            <x-ui.icon name="download" class="size-4" /> {{ __('Export result') }}
        </a>
    </x-ui.page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-ui.card :title="__('Generate')" icon="sparkles">
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between border-b border-base-200 pb-2">
                    <span class="text-base-content/70">{{ __('Penggalang block') }}</span>
                    <span class="font-semibold text-sky-700 dark:text-sky-300">
                        {{ __('Grup') }} {{ min($penggalangGroups) }}–{{ max($penggalangGroups) }} ({{ count($penggalangGroups) }})
                    </span>
                </div>
                <div class="flex items-center justify-between border-b border-base-200 pb-2">
                    <span class="text-base-content/70">{{ __('Penegak block') }}</span>
                    <span class="font-semibold text-emerald-700 dark:text-emerald-300">
                        {{ __('Grup') }} {{ min($penegakGroups) }}–{{ max($penegakGroups) }} ({{ count($penegakGroups) }})
                    </span>
                </div>
                <div class="flex items-center justify-between border-b border-base-200 pb-2">
                    <span class="text-base-content/70">{{ __('Min. per number') }}</span>
                    <span class="font-semibold">{{ $minPerNumber }}</span>
                </div>
                <div class="flex items-center justify-between pb-1">
                    <span class="text-base-content/70">{{ __('Verified troops') }}</span>
                    <span class="font-semibold">{{ $verifiedTroops }}</span>
                </div>
            </div>
            <a href="{{ route('admin.settings') }}" wire:navigate class="link link-hover text-xs">{{ __('Change configuration') }}</a>

            <label class="label cursor-pointer gap-2">
                <input type="checkbox" wire:model="resetAll" class="checkbox checkbox-sm checkbox-primary" />
                <span class="text-base-content text-sm">{{ __('Reassign all participants (reset)') }}</span>
            </label>
            <p class="text-base-content/60 text-xs">{{ __('Without reset, only troops with unassigned participants are processed and existing numbers are kept.') }}</p>

            <button type="button" class="btn btn-primary" wire:click="generate" wire:loading.attr="disabled"
                x-confirm="{ message: @js(__('Generate group numbers now?')), tone: 'primary', confirm: @js(__('Generate group numbers')) }">
                <span wire:loading wire:target="generate" class="loading loading-spinner loading-sm"></span>
                <x-ui.icon name="sparkles" class="size-4" wire:loading.remove wire:target="generate" /> {{ __('Generate group numbers') }}
            </button>

            <p class="text-base-content/60 text-xs">{{ __('Cancel the assignment to remove the group numbers of all participants. Participant cards will be unavailable until groups are generated again.') }}</p>
            <button type="button" class="btn btn-error btn-outline" wire:click="clear" wire:loading.attr="disabled"
                x-confirm="{ message: @js(__('Remove the group numbers of ALL participants?')), tone: 'error', confirm: @js(__('Cancel group numbers')) }">
                <span wire:loading wire:target="clear" class="loading loading-spinner loading-sm"></span>
                <x-ui.icon name="trash" class="size-4" wire:loading.remove wire:target="clear" /> {{ __('Cancel group numbers') }}
            </button>
        </x-ui.card>

        <div class="xl:col-span-2">
            <livewire:admin.groups.balance-report />
        </div>
    </div>

    <x-ui.card :title="__('Recent runs')" icon="table">
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr><th>{{ __('Time') }}</th><th>{{ __('By') }}</th><th>{{ __('Mode') }}</th><th>N</th><th>{{ __('Troops') }}</th><th>{{ __('Participants') }}</th><th>{{ __('Balance') }}</th></tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr wire:key="batch-{{ $batch->id }}">
                            <td>{{ $batch->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td>{{ $batch->executor?->name ?? '-' }}</td>
                            <td>{{ $batch->is_reset ? __('Reset') : __('Incremental') }}</td>
                            <td>{{ $batch->max_group_number }}</td>
                            <td>{{ $batch->total_troops }}</td>
                            <td>{{ $batch->total_participants }}</td>
                            <td>
                                @if ($batch->summary['balanced'] ?? false)
                                    <span class="badge badge-soft badge-success badge-sm">{{ __('Balanced') }}</span>
                                @else
                                    <span class="badge badge-soft badge-warning badge-sm">{{ __('Spread :n', ['n' => $batch->summary['spread'] ?? '-']) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-ui.empty-state icon="sparkles" :title="__('No runs yet')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>

<div class="space-y-6">
    <x-ui.page-header :title="__('Attendance')" :subtitle="__('Attendance of participants from verified registrations.')">
        <a href="{{ route('admin.print.attendance.manual', array_filter(['session_id' => $session?->id, 'troop_id' => $troopId, 'group_number' => $groupNumber])) }}" target="_blank" class="btn btn-outline btn-primary btn-sm">
            <x-ui.icon name="printer" class="size-4" /> {{ __('Print Manual Attendance') }}
        </a>
        @if ($session)
            <a href="{{ route('admin.print.attendance', ['session' => $session, 'troop_id' => $troopId, 'group_number' => $groupNumber]) }}" target="_blank" class="btn btn-ghost btn-sm">
                <x-ui.icon name="printer" class="size-4" /> {{ __('Print sheet') }}
            </a>
            <a href="{{ route('admin.exports.attendance', $session) }}" class="btn btn-secondary btn-sm">
                <x-ui.icon name="download" class="size-4" /> {{ __('Export') }}
            </a>
        @endif
    </x-ui.page-header>

    @if ($sessions->isEmpty())
        <x-ui.card>
            <x-ui.empty-state icon="table" :title="__('No attendance session yet')" :description="__('Create a session (e.g. Day 1) before recording attendance.')">
                <a href="{{ route('admin.attendance.sessions') }}" wire:navigate class="btn btn-primary btn-sm mt-2">{{ __('Create session') }}</a>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-ui.card :title="__('Session')" icon="table">
                <x-ui.select name="sessionId" wire:model.live="sessionId"
                    :options="$sessions->mapWithKeys(fn ($s) => [$s->id => $s->name.' · '.$s->date->translatedFormat('d M Y').($s->is_active ? '' : ' ('.__('closed').')')])->all()" />

                @if ($session?->is_active)
                    @include('livewire.admin.attendance.partials.scanner')
                @elseif ($session)
                    <x-ui.alert type="warning" class="text-sm">{{ __('This session is closed. Reopen it on the sessions page to record attendance.') }}</x-ui.alert>
                @endif
            </x-ui.card>

            <div class="xl:col-span-2">
                @if ($session)
                    <livewire:admin.attendance.recap :session-id="$session->id" wire:key="recap-{{ $session->id }}" />
                @endif
            </div>
        </div>

        @if ($session)
            <x-ui.card>
                <div class="grid grid-cols-1 gap-x-4 md:grid-cols-4">
                    <x-ui.input name="search" wire:model.live.debounce.400ms="search" :placeholder="__('Search KTA or name...')" />
                    <x-ui.select name="troopId" wire:model.live="troopId" :options="$troopOptions" :placeholder="__('All troops')" />
                    <x-ui.select name="groupNumber" wire:model.live="groupNumber" :options="$groupOptions" :placeholder="__('All groups')" />
                    <x-ui.select name="status" wire:model.live="status" :options="$statusOptions" :placeholder="__('All statuses')" />
                </div>

                <livewire:admin.attendance.participant-list
                    :session-id="$session->id"
                    :search="$search"
                    :troop-id="$troopId"
                    :group-number="$groupNumber"
                    :status="$status"
                    wire:key="list-{{ $session->id }}"
                />
            </x-ui.card>
        @endif
    @endif
</div>

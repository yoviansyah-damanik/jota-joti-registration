<div class="space-y-6">
    <x-ui.page-header :title="__('Attendance Sessions')" :subtitle="__('Define the days or activities where attendance is taken.')" />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-ui.card :title="$editingId ? __('Edit session') : __('New session')" icon="plus">
            <form wire:submit="save" class="space-y-1">
                <x-ui.input :label="__('Session name')" name="name" wire:model="name" :placeholder="__('e.g. Day 1 - Opening')" required />
                <x-ui.input :label="__('Date')" name="date" type="date" wire:model="date" required />
                <div class="grid grid-cols-2 gap-x-3">
                    <x-ui.input :label="__('Start')" name="starts_at" type="time" wire:model="starts_at" />
                    <x-ui.input :label="__('End')" name="ends_at" type="time" wire:model="ends_at" />
                </div>
                <label class="label cursor-pointer gap-2 py-2">
                    <input type="checkbox" wire:model="is_active" class="toggle toggle-primary toggle-sm" />
                    <span class="text-base-content text-sm">{{ __('Open for attendance') }}</span>
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary flex-1">{{ __('Save') }}</button>
                    @if ($editingId)
                        <button type="button" class="btn btn-ghost" wire:click="cancel">{{ __('Cancel') }}</button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :title="__('Sessions')" icon="table" class="xl:col-span-2">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>{{ __('Session') }}</th><th>{{ __('Date') }}</th><th>{{ __('Time') }}</th><th class="text-center">{{ __('Present') }}</th><th>{{ __('Status') }}</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr wire:key="session-{{ $session->id }}" @class(['bg-primary/5' => $editingId === $session->id])>
                                <td class="font-medium">{{ $session->name }}</td>
                                <td>{{ $session->date->translatedFormat('D, d M Y') }}</td>
                                <td class="text-sm">{{ $session->starts_at ? substr($session->starts_at, 0, 5) : '-' }} – {{ $session->ends_at ? substr($session->ends_at, 0, 5) : '-' }}</td>
                                <td class="text-center">{{ $session->present_count }}</td>
                                <td>
                                    <input type="checkbox" class="toggle toggle-primary toggle-sm" @checked($session->is_active) wire:click="toggle({{ $session->id }})" />
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.attendance.index', ['session' => $session->id]) }}" wire:navigate class="btn btn-soft btn-primary btn-xs">{{ __('Open') }}</a>
                                        <button type="button" class="btn btn-ghost btn-xs" wire:click="edit({{ $session->id }})">{{ __('Edit') }}</button>
                                        <button type="button" class="btn btn-ghost btn-xs text-error" wire:click="delete({{ $session->id }})"
                                            x-confirm="{ message: @js(__('Delete this session and all of its attendance records?')), tone: 'error', confirm: @js(__('Delete')) }">
                                            <x-ui.icon name="trash" class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.empty-state icon="table" :title="__('No attendance session yet')" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</div>

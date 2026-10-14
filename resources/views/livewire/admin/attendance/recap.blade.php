@php
    $percentage = $recap['total'] ? round($recap['counts']['present'] / $recap['total'] * 100) : 0;
    $rows = $mode === 'group' ? $recap['by_group'] : $recap['by_troop'];
@endphp

<x-ui.card :title="__('Recap')" icon="clipboard-check">
    <x-slot:actions>
        <div role="tablist" class="tabs tabs-box tabs-sm">
            <button type="button" role="tab" wire:click="$set('mode', 'troop')" @class(['tab', 'tab-active' => $mode === 'troop'])>{{ __('Per troop') }}</button>
            <button type="button" role="tab" wire:click="$set('mode', 'group')" @class(['tab', 'tab-active' => $mode === 'group'])>{{ __('Per group') }}</button>
        </div>
    </x-slot:actions>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
        <div class="bg-base-200 rounded-box p-3">
            <p class="text-base-content/60 text-xs">{{ __('Participants') }}</p>
            <p class="text-xl font-semibold">{{ $recap['total'] }}</p>
        </div>
        @foreach ($statuses as $status)
            <div class="bg-base-200 rounded-box p-3">
                <p class="text-base-content/60 text-xs">{{ $status->label() }}</p>
                <p class="text-xl font-semibold">{{ $recap['counts'][$status->value] }}</p>
            </div>
        @endforeach
        <div class="bg-base-200 rounded-box p-3">
            <p class="text-base-content/60 text-xs">{{ __('Not recorded') }}</p>
            <p class="text-xl font-semibold">{{ $recap['counts']['unrecorded'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div class="bg-base-200 rounded-box p-3">
            <p class="text-base-content/60 text-xs">{{ __('Present') }} · {{ __('Male') }}</p>
            <p class="text-xl font-semibold">{{ $recap['counts']['present_male'] }}</p>
        </div>
        <div class="bg-base-200 rounded-box p-3">
            <p class="text-base-content/60 text-xs">{{ __('Present') }} · {{ __('Female') }}</p>
            <p class="text-xl font-semibold">{{ $recap['counts']['present_female'] }}</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <progress class="progress progress-success flex-1" value="{{ $percentage }}" max="100"></progress>
        <span class="text-sm font-medium">{{ $percentage }}% {{ __('present') }}</span>
    </div>

    <div class="rounded-box max-h-72 overflow-auto">
        <table class="table table-sm table-pin-rows">
            <thead>
                <tr>
                    <th>{{ $mode === 'group' ? __('Group') : __('Troop') }}</th>
                    <th class="text-center">{{ __('Total') }}</th>
                    @foreach ($statuses as $status)
                        <th class="text-center">{{ $status->label() }}</th>
                    @endforeach
                    <th class="text-center">{{ __('Not recorded') }}</th>
                    <th class="text-center">{{ __('Present') }} {{ __('Male') }}</th>
                    <th class="text-center">{{ __('Present') }} {{ __('Female') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>
                            @if ($mode === 'group')
                                @if ($row['number'])<x-ui.group-badge :number="$row['number']" :label="true" />@else{{ __('Not assigned') }}@endif
                            @else
                                {{ $row['troop']->number_label }} <span class="text-base-content/60 text-xs">{{ $row['troop']->troop_name }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $row['total'] }}</td>
                        @foreach ($statuses as $status)
                            <td class="text-center">{{ $row[$status->value] }}</td>
                        @endforeach
                        <td class="text-center">{{ $row['unrecorded'] }}</td>
                        <td class="text-center">{{ $row['present_male'] }}</td>
                        <td class="text-center">{{ $row['present_female'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-ui.empty-state icon="users" :title="__('No verified participants yet')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

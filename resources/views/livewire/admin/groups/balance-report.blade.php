<x-ui.card :title="__('Balance check')" :subtitle="__(':total verified participants · :unassigned not assigned', ['total' => $report['total'], 'unassigned' => $report['unassigned']])" icon="user-group">
    <x-slot:actions>
        @if ($report['total'] - $report['unassigned'] === 0)
            <span class="badge badge-soft badge-neutral">{{ __('Not generated yet') }}</span>
        @elseif ($report['balanced'])
            <span class="badge badge-soft badge-success">{{ __('Balanced (difference :n)', ['n' => $report['spread']]) }}</span>
        @else
            <span class="badge badge-soft badge-warning">{{ __('Unbalanced (difference :n)', ['n' => $report['spread']]) }}</span>
        @endif
    </x-slot:actions>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Group') }}</th>
                    <th class="w-1/2">{{ __('Participants') }}</th>
                    <th class="text-center">{{ __('Total') }}</th>
                    <th class="text-center">{{ __('Male') }}</th>
                    <th class="text-center">{{ __('Female') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['numbers'] as $row)
                    <tr wire:key="number-{{ $row['number'] }}">
                        <td><x-ui.group-badge :number="$row['number']" size="md" /></td>
                        <td>
                            <div class="bg-base-200 rounded-field flex h-3 overflow-hidden">
                                <div class="bg-primary" style="width: {{ $row['male'] / $peak * 100 }}%"></div>
                                <div class="bg-secondary" style="width: {{ $row['female'] / $peak * 100 }}%"></div>
                            </div>
                        </td>
                        <td class="text-center font-semibold">{{ $row['total'] }}</td>
                        <td class="text-center">{{ $row['male'] }}</td>
                        <td class="text-center">{{ $row['female'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="text-base-content/60 flex gap-4 text-xs">
        <span class="flex items-center gap-1"><span class="bg-primary inline-block size-2.5 rounded-full"></span> {{ __('Male') }}</span>
        <span class="flex items-center gap-1"><span class="bg-secondary inline-block size-2.5 rounded-full"></span> {{ __('Female') }}</span>
    </div>

    @if ($issueTroops->isNotEmpty())
        <x-ui.alert type="warning" class="text-sm">
            <p class="font-medium">{{ __('Troops below the minimum of :min participant(s) per number:', ['min' => $report['min_per_number']]) }}</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($issueTroops as $troop)
                    <li>{{ $troop->number_label }} – {{ $troop->troop_name }} ({{ __('numbers') }}: {{ implode(', ', $report['troop_issues'][$troop->id]) }})</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif
</x-ui.card>

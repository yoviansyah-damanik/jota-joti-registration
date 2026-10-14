{{-- Pratinjau hasil pemeriksaan Excel peserta; butuh $rows, $sheetErrors --}}
@foreach ($sheetErrors as $error)
    <x-ui.alert type="error">{{ $error }}</x-ui.alert>
@endforeach

@if ($rows !== [])
    @php $invalid = collect($rows)->filter(fn ($row) => ($row['errors'] ?? []) !== [])->count(); @endphp

    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="badge badge-soft badge-neutral">{{ __(':count rows', ['count' => count($rows)]) }}</span>
        @if ($invalid > 0)
            <span class="badge badge-soft badge-error">{{ __(':count rows with errors', ['count' => $invalid]) }}</span>
        @else
            <span class="badge badge-soft badge-success">{{ __('All rows are valid') }}</span>
        @endif
    </div>

    <div class="rounded-box max-h-96 overflow-auto">
        <table class="table table-sm table-pin-rows">
            <thead>
                <tr>
                    <th>{{ __('Row') }}</th>
                    <th>{{ __('KTA number') }}</th>
                    <th>{{ __('Full name') }}</th>
                    <th>{{ __('Date of birth') }}</th>
                    <th>{{ __('Gender') }}</th>
                    <th>{{ __('Check') }}</th>
                    @if (method_exists($this, 'removeRow'))
                        <th class="text-right">{{ __('Action') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr @class(['bg-error/10' => ($row['errors'] ?? []) !== []]) wire:key="row-{{ $row['row'] ?? $loop->index }}">
                        <td class="text-base-content/50">{{ $row['row'] ?? ($loop->iteration) }}</td>
                        <td class="font-mono">{{ $row['kta_number'] ?: '-' }}</td>
                        <td class="font-semibold">{{ $row['full_name'] }}</td>
                        <td>{{ $row['birth_date'] ?? $row['birth_date_raw'] }}</td>
                        <td>{{ $row['gender'] ?? $row['gender_raw'] }}</td>
                        <td>
                            @if (($row['errors'] ?? []) === [])
                                <x-ui.icon name="check-circle" class="text-success size-5" />
                            @else
                                <ul class="text-error text-xs">
                                    @foreach (($row['errors'] ?? []) as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        @if (method_exists($this, 'removeRow'))
                            <td class="text-right">
                                <button type="button" class="btn btn-ghost btn-xs text-error btn-square" wire:click="removeRow({{ $loop->index }})" aria-label="{{ __('Remove') }}">
                                    <x-ui.icon name="trash" class="size-4" />
                                </button>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

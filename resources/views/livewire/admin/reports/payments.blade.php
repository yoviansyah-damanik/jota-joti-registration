<div class="space-y-6">
    <x-ui.page-header :title="__('Registration Fee Receipt Report')" :subtitle="__('Fees received from verified troops and fees still awaiting verification.')">
        <a href="{{ route('admin.exports.payments', array_filter($filters)) }}" class="btn btn-secondary btn-sm">
            <x-ui.icon name="download" class="size-4" /> {{ __('Export Excel') }}
        </a>
        <a href="{{ route('admin.print.payments', array_filter($filters)) }}" target="_blank" class="btn btn-primary btn-sm">
            <x-ui.icon name="printer" class="size-4" /> {{ __('Print') }}
        </a>
    </x-ui.page-header>

    {{-- filter periode & status --}}
    <x-ui.card>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
            <x-ui.input :label="__('From date')" name="from" type="date" wire:model.live="from" class="lg:w-44" />
            <x-ui.input :label="__('To date')" name="to" type="date" wire:model.live="to" class="lg:w-44" />

            <div class="flex-1">
                <p class="fieldset-legend mb-1">{{ __('Status') }}</p>
                <div class="join flex-wrap">
                    @foreach ($scopes as $value => $label)
                        <button type="button" wire:click="$set('scope', '{{ $value }}')" @class(['btn join-item', 'btn-primary' => $scope === $value])>{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <button type="button" class="btn btn-ghost" wire:click="resetFilters">{{ __('Reset') }}</button>
        </div>
        <p class="text-base-content/60 text-xs">
            {{ __('The date is the verification date for received fees, or the submission date for fees not verified yet.') }}
            {{ __('Current fee') }}: <span class="font-semibold">{{ \App\Support\Rupiah::format($fee) }}</span> / {{ __('participant') }}
        </p>
    </x-ui.card>

    <livewire:admin.reports.payment-summary :from="$from" :to="$to" lazy />

    <livewire:admin.reports.payment-table :from="$from" :to="$to" :scope="$scope" lazy wire:key="payment-table-{{ md5($from.$to.$scope) }}" />
</div>

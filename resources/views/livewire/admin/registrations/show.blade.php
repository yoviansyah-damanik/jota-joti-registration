<div class="space-y-6">
    <x-ui.page-header :title="$troop->troop_name" :subtitle="__('Troop number').': '.$troop->number_label.' · '.$troop->unit?->label().' · '.$troop->registration_code">
        <a href="{{ route('admin.registrations.index') }}" wire:navigate class="btn btn-ghost btn-sm">
            <x-ui.icon name="arrow-left" class="size-4" /> {{ __('Back') }}
        </a>
        @if ($troop->status === \App\Enums\TroopStatus::Verified)
            <a href="{{ route('admin.cards.show', $troop) }}" target="_blank" class="btn btn-secondary btn-sm">
                <x-ui.icon name="qr" class="size-4" /> {{ __('Print participant cards') }}
            </a>
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-soft btn-sm">
                    <x-ui.icon name="document" class="size-4" /> {{ __('E-Certificates') }}
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box shadow-soft z-10 mt-1 w-64 p-2">
                    <li><a href="{{ route('admin.certificates.troop', $troop) }}">{{ __('Troop certificate') }}</a></li>
                    <li><a href="{{ route('admin.certificates.participants', $troop) }}">{{ __('All participant certificates') }}</a></li>
                </ul>
            </div>
        @endif
        <x-ui.badge-status :status="$troop->status" class="badge-lg" />
    </x-ui.page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="min-w-0 space-y-6 xl:col-span-2">
            @include('livewire.admin.registrations.partials.troop-info')
            @include('livewire.admin.registrations.partials.documents')

            <livewire:admin.registrations.participant-table :troop-id="$troop->id" wire:key="participants-{{ $troop->id }}" />
        </div>

        <div class="min-w-0">
            @include('livewire.admin.registrations.partials.review-form')
        </div>
    </div>
</div>

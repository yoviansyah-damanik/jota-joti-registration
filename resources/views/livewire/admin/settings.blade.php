<div>
    <x-ui.page-header :title="__('Settings')" :subtitle="__('Registration and group number configuration')" />

    <form wire:submit="save" class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-ui.card :title="__('Registration')" icon="flag">
            <x-ui.input :label="__('Event name')" name="event_name" wire:model="event_name" required />
            <x-ui.input :label="__('Organization name')" name="organization_name" wire:model="organization_name" required />

            <label class="label cursor-pointer gap-2 py-2">
                <input type="checkbox" wire:model="registration_open" class="toggle toggle-primary" />
                <span class="text-base-content">{{ __('Registration is open') }}</span>
            </label>

            <x-ui.input :label="__('Registration fee per participant (Rp)')" name="registration_fee" type="number" min="0" wire:model="registration_fee" :hint="__('Total = number of participants × this fee.')" />
            <x-ui.textarea :label="__('Bank account info')" name="bank_account_info" wire:model="bank_account_info" rows="3" />
            <x-ui.input :label="__('Contact info')" name="contact_info" wire:model="contact_info" />
            <x-ui.textarea :label="__('Other terms')" name="additional_terms" wire:model="additional_terms" rows="5" :hint="__('One term per line. Shown on the requirements page.')" />
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card :title="__('Group numbers')" icon="user-group">
                <div class="grid gap-x-4 sm:grid-cols-2">
                    <x-ui.input :label="__('Highest group number (N)')" name="max_group_number" type="number" min="1" max="50" wire:model.live="max_group_number" :hint="__('Participants get a number from 1 to N.')" />
                    <x-ui.input :label="__('Minimum per number per troop')" name="min_per_number" type="number" min="0" max="20" wire:model.live="min_per_number" :hint="__('Each number must be received by at least this many participants in every troop.')" />
                </div>
                <x-ui.alert type="info" class="text-sm">
                    {{ __('Each troop must register at least :count participants (N × minimum).', ['count' => (int) $max_group_number * (int) $min_per_number]) }}
                    {{ __('After changing N, run the group assignment again with reset.') }}
                </x-ui.alert>
            </x-ui.card>

            <x-ui.card :title="__('Certificates')" icon="document">
                <label class="label cursor-pointer justify-start gap-2 py-2">
                    <input type="checkbox" wire:model="event_completed" class="toggle toggle-success" />
                    <span class="text-base-content">{{ __('Event is completed') }}</span>
                </label>
                <p class="text-base-content/60 -mt-1 mb-2 text-xs">{{ __('When enabled, verified troops can download a troop certificate and certificates for participants who attended at least one session.') }}</p>

                <x-ui.input :label="__('Signer name')" name="certificate_signer_name" wire:model="certificate_signer_name" />
                <x-ui.input :label="__('Signer title')" name="certificate_signer_title" wire:model="certificate_signer_title" />
                <x-ui.input :label="__('Certificate city')" name="certificate_city" wire:model="certificate_city" />
            </x-ui.card>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                    {{ __('Save settings') }}
                </button>
            </div>
        </div>
    </form>
</div>

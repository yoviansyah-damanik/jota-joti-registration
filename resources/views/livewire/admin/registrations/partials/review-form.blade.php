<x-ui.card :title="__('Verification')" :subtitle="__('Check the form, participant data, and documents before deciding.')" icon="clipboard-check" class="xl:sticky xl:top-6">
    @if ($troop->verified_at)
        <x-ui.alert type="success" class="text-sm">
            {{ __('Verified by :name on :date', ['name' => $troop->verifier?->name ?? '-', 'date' => $troop->verified_at->translatedFormat('d M Y H:i')]) }}
        </x-ui.alert>
    @endif

    <x-ui.textarea :label="__('Note for registrant')" name="note" wire:model="note" rows="4" :hint="__('Required for revision or rejection. Shown on the status page.')" />

    <div class="grid gap-2">
        <button type="button" class="btn btn-success" wire:click="review('verified')" wire:loading.class="pointer-events-none opacity-70"
            x-confirm="{ message: @js(__('Verify this registration?')), tone: 'success', confirm: @js(__('Verify')) }" @disabled($troop->status === \App\Enums\TroopStatus::Verified)>
            <x-ui.icon name="check-circle" class="size-4" /> {{ __('Verify') }}
        </button>
        <button type="button" class="btn btn-warning btn-soft" wire:click="review('revision')" wire:loading.attr="disabled"
            x-confirm="{ message: @js(__('Ask the registrant to revise this registration?')), tone: 'warning', confirm: @js(__('Needs revision')) }">
            <x-ui.icon name="exclamation" class="size-4" /> {{ __('Needs revision') }}
        </button>
        <button type="button" class="btn btn-error btn-soft" wire:click="review('rejected')" wire:loading.attr="disabled"
            x-confirm="{ message: @js(__('Reject this registration?')), tone: 'error', confirm: @js(__('Reject')) }">
            <x-ui.icon name="x-circle" class="size-4" /> {{ __('Reject') }}
        </button>
    </div>
</x-ui.card>

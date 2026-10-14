<x-ui.step-card :step="3" :title="__('Documents')" :subtitle="__('Upload the assignment letter and proof of payment.')">
    <form wire:submit="save" class="space-y-6">
        {{-- Informasi pembayaran, ditampilkan di dekat unggah bukti agar tidak perlu kembali ke halaman awal --}}
        <x-ui.fee-summary :fee="(int) $settings->get('registration_fee')" :count="$participantCount" :bank="$settings->get('bank_account_info')" class="max-w-xl" />

        <div class="grid gap-6 md:grid-cols-2">
            <x-ui.file-upload
                :label="__('Assignment letter')"
                name="assignmentLetter"
                accept=".pdf,.jpg,.jpeg,.png"
                icon="document"
                :file="$assignmentLetter ? null : $savedFiles['assignmentLetter']"
                :hint="__('PDF/JPG/PNG, max 2 MB.')"
                required
            />
            <x-ui.file-upload
                :label="__('Proof of payment')"
                name="paymentProof"
                accept=".pdf,.jpg,.jpeg,.png"
                icon="banknotes"
                :file="$paymentProof ? null : $savedFiles['paymentProof']"
                :hint="__('PDF/JPG/PNG, max 2 MB.')"
                required
            />
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="button" class="btn btn-ghost" wire:click="$parent.goTo(2)">
                <x-ui.icon name="arrow-left" class="size-4" /> {{ __('Back') }}
            </button>
            <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                {{ __('Save & Continue') }} <x-ui.icon name="arrow-right" class="size-5" />
            </button>
        </div>
    </form>
</x-ui.step-card>

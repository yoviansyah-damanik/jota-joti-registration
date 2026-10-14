<x-ui.card :title="__('Submit Revision')" :subtitle="__('Upload only the files that need to be corrected. Uploading a new participant file replaces all participant data.')" icon="upload">
    <form wire:submit="submit" class="space-y-4">
        @error('revision') <x-ui.alert type="error">{{ $message }}</x-ui.alert> @enderror

        <x-ui.file-upload :label="__('Participant Excel file')" name="participantFile" accept=".xlsx,.xls" :hint="__('Optional. Format .xlsx, max 5 MB.')" />

        <div wire:loading wire:target="participantFile" class="text-base-content/60 flex items-center gap-2 text-sm">
            <span class="loading loading-spinner loading-sm"></span> {{ __('Checking file...') }}
        </div>

        @include('livewire.public.registration.partials.participant-preview')

        <div class="grid gap-x-6 md:grid-cols-2">
            <x-ui.file-upload :label="__('Assignment letter')" name="assignmentLetter" accept=".pdf,.jpg,.jpeg,.png" :hint="__('Optional. PDF/JPG/PNG, max 2 MB.')" />
            <x-ui.file-upload :label="__('Proof of payment')" name="paymentProof" accept=".pdf,.jpg,.jpeg,.png" :hint="__('Optional. PDF/JPG/PNG, max 2 MB.')" />
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading wire:target="submit" class="loading loading-spinner loading-sm"></span>
                {{ __('Submit Revision') }}
            </button>
        </div>
    </form>
</x-ui.card>

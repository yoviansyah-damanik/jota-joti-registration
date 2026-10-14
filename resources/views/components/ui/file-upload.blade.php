@props(['label', 'name', 'accept' => null, 'hint' => null, 'file' => null, 'required' => false, 'icon' => 'upload'])

{{-- Area unggah: klik atau seret file; menampilkan nama file terpilih dan progres unggah --}}
<div
    class="space-y-2"
    x-data="{
        uploading: false,
        progress: 0,
        dragging: false,
        picked: null,
        saved: @js($file),
        drop(event) {
            this.dragging = false;
            if (! event.dataTransfer.files.length) return;
            this.$refs.input.files = event.dataTransfer.files;
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        },
    }"
    x-on:livewire-upload-start="uploading = true"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-cancel="uploading = false"
    x-on:livewire-upload-error="uploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
>
    <p class="text-sm font-semibold">
        {{ $label }} @if ($required)<span class="text-error">*</span>@endif
    </p>

    <label
        for="{{ $name }}"
        x-on:dragover.prevent="dragging = true"
        x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="drop($event)"
        x-bind:class="dragging
            ? 'bg-primary/10 ring-2 ring-primary'
            : (picked || saved ? 'bg-success/10 hover:bg-success/15' : 'bg-base-200 hover:bg-base-300/70')"
        @class([
            'rounded-box flex cursor-pointer flex-col items-center gap-3 px-6 py-9 text-center transition',
            'has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-primary',
            'ring-2 ring-error' => $errors->has($name),
        ])
    >
        <span
            class="flex size-14 items-center justify-center rounded-full"
            x-bind:class="picked || saved ? 'bg-success text-success-content' : 'bg-base-100 text-primary'"
        >
            <x-ui.icon :name="$icon" class="size-7" x-show="! (picked || saved)" />
            <x-ui.icon name="check" class="size-7" x-show="picked || saved" x-cloak />
        </span>

        <span class="min-w-0 max-w-full">
            <span class="block truncate font-semibold" x-text="picked ?? saved ?? @js(__('Click to choose a file or drag it here'))"></span>
            <span class="text-base-content/60 mt-0.5 block text-xs sm:text-sm" x-show="picked || saved" x-cloak>{{ __('Click to replace the file') }}</span>
            @if ($hint)
                <span class="text-base-content/60 mt-0.5 block text-xs sm:text-sm" x-show="! (picked || saved)">{{ $hint }}</span>
            @endif
        </span>

        <input
            type="file"
            id="{{ $name }}"
            x-ref="input"
            wire:model="{{ $name }}"
            x-on:change="picked = $event.target.files[0]?.name ?? null"
            @if ($accept) accept="{{ $accept }}" @endif
            class="sr-only"
            {{ $attributes }}
        />
    </label>

    <progress x-show="uploading" x-cloak class="progress progress-primary w-full" max="100" x-bind:value="progress"></progress>

    @error($name)
        <p class="text-error text-sm">{{ $message }}</p>
    @enderror
</div>

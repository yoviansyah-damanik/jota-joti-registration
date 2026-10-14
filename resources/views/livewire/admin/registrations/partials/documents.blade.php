{{-- Kartu dokumen; tombol Lihat membuka pratinjau dalam modal (gambar/PDF) --}}
<div x-data="documentViewer">
    <x-ui.card :title="__('Documents')" icon="document">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach (\App\Enums\DocumentType::cases() as $type)
                @php
                    $document = $troop->document($type);
                    $previewable = $document && ($document->isImage() || $document->isPdf());
                    $payload = $previewable ? [
                        'url' => route('admin.documents.show', [$document, 'inline' => 1]),
                        'download' => route('admin.documents.show', $document),
                        'title' => $type->label(),
                        'name' => $document->original_name,
                        'kind' => $document->isImage() ? 'image' : 'pdf',
                    ] : null;
                @endphp

                <div class="rounded-box bg-base-200/60 flex flex-col gap-3 p-3" wire:key="doc-{{ $type->value }}">
                    <p class="text-sm font-medium">{{ $type->label() }}</p>

                    @if ($document)
                        @if ($document->isImage())
                            <button type="button" class="rounded-box focus-visible:outline-primary block cursor-zoom-in" x-on:click="view(@js($payload))" aria-label="{{ __('View') }} {{ $type->label() }}">
                                <img src="{{ $payload['url'] }}" alt="{{ $type->label() }}" class="rounded-box bg-base-200 h-36 w-full object-cover" loading="lazy">
                            </button>
                        @else
                            <div class="bg-base-200 rounded-box grid h-36 place-items-center">
                                <x-ui.icon :name="$type === \App\Enums\DocumentType::ParticipantsExcel ? 'table' : 'document'" class="text-base-content/40 size-10" />
                            </div>
                        @endif

                        <p class="text-base-content/60 truncate text-xs" title="{{ $document->original_name }}">{{ $document->original_name }}</p>

                        <div class="flex gap-2">
                            @if ($previewable)
                                <button type="button" class="btn btn-soft btn-xs flex-1" x-on:click="view(@js($payload))">
                                    <x-ui.icon name="eye" class="size-3.5" /> {{ __('View') }}
                                </button>
                            @endif
                            <a href="{{ route('admin.documents.show', $document) }}" class="btn btn-soft btn-xs flex-1">
                                <x-ui.icon name="download" class="size-3.5" /> {{ __('Download') }}
                            </a>
                        </div>
                    @else
                        <p class="text-error text-xs">{{ __('Not uploaded') }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </x-ui.card>

    <x-ui.document-viewer />
</div>

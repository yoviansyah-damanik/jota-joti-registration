@php
    $scrollTo = $scrollTo ?? 'body';
    $scrollSnippet = $scrollTo !== false
        ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()"
        : '';
    $pageName = $paginator->getPageName();
@endphp

{{-- Pagination Livewire bergaya daisyUI; deret nomor halaman bisa digulir agar tidak melebarkan halaman --}}
<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="{{ __('Pagination') }}" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
            <p class="text-base-content/60 text-sm">
                {{ __('Showing :from–:to of :total', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
            </p>

            <div class="join max-w-full overflow-x-auto">
                <button type="button" class="btn btn-sm join-item" @disabled($paginator->onFirstPage())
                    wire:click="previousPage('{{ $pageName }}')" x-on:click="{{ $scrollSnippet }}" aria-label="{{ __('Previous') }}">«</button>

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <button type="button" class="btn btn-sm btn-disabled join-item">{{ $element }}</button>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <button type="button" wire:key="paginator-{{ $pageName }}-page{{ $page }}"
                                @class(['btn btn-sm join-item', 'btn-primary' => $page == $paginator->currentPage()])
                                @if ($page == $paginator->currentPage()) aria-current="page" @endif
                                wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" x-on:click="{{ $scrollSnippet }}">{{ $page }}</button>
                        @endforeach
                    @endif
                @endforeach

                <button type="button" class="btn btn-sm join-item" @disabled(! $paginator->hasMorePages())
                    wire:click="nextPage('{{ $pageName }}')" x-on:click="{{ $scrollSnippet }}" aria-label="{{ __('Next') }}">»</button>
            </div>
        </nav>
    @endif
</div>

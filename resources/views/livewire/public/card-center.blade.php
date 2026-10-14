<div>
    @if ($selected)
        @php $count = $selected->participants->count(); @endphp

        <div class="space-y-6">
            {{-- identitas gugusdepan + aksi --}}
            <section class="hero-dark text-neutral-content rounded-box shadow-soft flex flex-col gap-6 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="text-accent text-xs font-bold uppercase tracking-wide">{{ __('Troop number') }}</p>
                    <p class="mt-1 text-3xl font-extrabold tabular-nums tracking-tight text-white sm:text-4xl">{{ $selected->number_label }}</p>
                    <p class="mt-1 text-lg font-semibold text-white">{{ $selected->troop_name }}</p>
                    <p class="text-neutral-content/70 mt-1 text-sm">{{ __(':count participants', ['count' => $count]) }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('cards.public', $selected) }}" target="_blank" class="btn btn-accent">
                        <x-ui.icon name="printer" class="size-5" /> {{ __('Print participant cards') }}
                    </a>
                    <a href="{{ route('cards.public.pdf', $selected) }}" class="btn border-0 bg-white/10 text-white hover:bg-white/20">
                        <x-ui.icon name="download" class="size-5" /> {{ __('Download all (PDF)') }}
                    </a>
                    <button type="button" class="btn btn-ghost text-white hover:bg-white/10" wire:click="back">
                        <x-ui.icon name="arrow-left" class="size-4" /> {{ __('Choose another troop') }}
                    </button>
                </div>
            </section>

            {{-- daftar peserta --}}
            <section class="rounded-box bg-base-100 shadow-soft overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-extrabold">{{ __('Participants') }}</h2>
                    <span class="badge badge-soft badge-primary">{{ $count }}</span>
                </div>

                <ul class="divide-base-200 divide-y">
                    @foreach ($selected->participants as $participant)
                        @php $color = \App\Support\GroupColor::for($participant->group_number); @endphp
                        <li class="hover:bg-primary/5 flex items-center gap-3 px-5 py-3 transition sm:gap-4 sm:px-6" wire:key="card-p-{{ $participant->id }}">
                            <span class="text-base-content/40 w-6 shrink-0 text-right text-sm tabular-nums">{{ $loop->iteration }}</span>
                            <span class="grid size-11 shrink-0 place-items-center rounded-full text-lg font-extrabold" style="background-color: {{ $color['background'] }}; color: {{ $color['text'] }};"
                                title="{{ $participant->group_number ? __('Group :n', ['n' => $participant->group_number]) : __('Not assigned yet') }}">
                                {{ $participant->group_number ?? '-' }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold" title="{{ $participant->full_name }}">{{ $participant->full_name }}</p>
                                <p class="text-base-content/55 font-mono text-xs">{{ $participant->attendance_code }}</p>
                            </div>
                            <span class="badge badge-soft badge-neutral hidden sm:inline-flex">{{ $participant->gender->label() }}</span>
                            <a href="{{ route('cards.public.pdf', ['troop' => $selected, 'participant' => $participant->attendance_code]) }}"
                                class="btn btn-soft btn-primary btn-sm shrink-0" title="{{ __('Download PDF') }}" aria-label="{{ __('Download PDF') }}">
                                <x-ui.icon name="download" class="size-4" /> <span class="hidden md:inline">PDF</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    @else
        <div class="space-y-5">
            <label class="input input-lg shadow-soft w-full">
                <x-ui.icon name="search" class="text-base-content/50 size-5" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search troop number or name...') }}" autocomplete="off" />
            </label>

            @if ($troops->isEmpty())
                <div class="rounded-box bg-base-100 shadow-soft"><x-ui.empty-state icon="flag" :title="__('No troops found')" /></div>
            @else
                <section class="rounded-box bg-base-100 shadow-soft overflow-hidden">
                    {{-- judul kolom (layar lebar) --}}
                    <div class="bg-base-200/70 text-base-content/60 hidden grid-cols-[9.5rem_minmax(0,1fr)_8rem_7rem_1.5rem] items-center gap-4 px-6 py-3 text-xs font-bold uppercase tracking-wide lg:grid">
                        <span>{{ __('Troop number') }}</span>
                        <span>{{ __('Troop name') }}</span>
                        <span>{{ __('Unit') }}</span>
                        <span>{{ __('Participants') }}</span>
                        <span></span>
                    </div>

                    <ul class="divide-base-200 divide-y">
                        @foreach ($troops as $troop)
                            <li wire:key="card-troop-{{ $troop->id }}">
                                <button type="button" wire:click="select('{{ $troop->id }}')"
                                    class="hover:bg-primary/5 group grid w-full grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-2 px-5 py-4 text-left transition sm:px-6 lg:grid-cols-[9.5rem_minmax(0,1fr)_8rem_7rem_1.5rem]">
                                    <span class="text-primary font-mono text-base font-extrabold tabular-nums lg:text-lg">{{ $troop->number_label }}</span>

                                    <span class="min-w-0 max-lg:order-3 max-lg:col-span-2">
                                        <span class="block font-semibold leading-snug">{{ $troop->troop_name }}</span>
                                        @if ($troop->unit)
                                            <span class="text-base-content/55 text-xs lg:hidden">{{ $troop->unit->label() }}</span>
                                        @endif
                                    </span>

                                    <span class="hidden lg:block">
                                        @if ($troop->unit)
                                            <span class="badge badge-soft badge-neutral">{{ $troop->unit->label() }}</span>
                                        @endif
                                    </span>

                                    <span class="flex items-center justify-end gap-3 lg:justify-start">
                                        <span class="badge badge-soft badge-primary whitespace-nowrap">{{ __(':count participants', ['count' => $troop->participants_count]) }}</span>
                                        <x-ui.icon name="arrow-right" class="text-primary size-5 shrink-0 transition group-hover:translate-x-1 lg:hidden" />
                                    </span>

                                    <x-ui.icon name="arrow-right" class="text-primary hidden size-5 transition group-hover:translate-x-1 lg:block" />
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </section>

                {{ $troops->links() }}
            @endif
        </div>
    @endif
</div>

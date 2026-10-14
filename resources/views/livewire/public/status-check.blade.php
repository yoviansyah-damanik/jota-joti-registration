@php
    use App\Enums\TroopStatus;
@endphp

<div class="space-y-6">
    @if (! $troop)
        {{-- Pencarian kode --}}
        <section class="hero-split relative left-1/2 -mt-8 w-screen -translate-x-1/2">
            <div class="mx-auto grid max-w-6xl lg:min-h-[35rem] lg:grid-cols-[1.2fr_1fr]">
            <div class="hero-dark text-neutral-content flex flex-col justify-center gap-8 p-6 sm:p-10 lg:bg-transparent lg:p-14">
                <div>
                    <p class="text-accent text-sm font-bold">{{ __('Registration status') }}</p>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-4xl xl:text-5xl">{{ __('Check Registration Status') }}</h1>
                    <p class="text-neutral-content/75 mt-3 max-w-xl text-base leading-relaxed sm:text-lg">{{ __('Enter the registration code you received after submitting.') }}</p>
                </div>

                <form wire:submit="check" class="max-w-xl">
                    <label for="code" class="text-neutral-content/80 mb-2 block text-sm font-semibold">{{ __('Registration code') }}</label>
                    <div class="join w-full">
                        <input type="text" id="code" wire:model="code" placeholder="JJ-XXXXXX" autocomplete="off" autofocus required
                            @class(['input input-lg join-item text-base-content w-full font-mono font-bold uppercase tracking-widest', 'input-error' => $errors->has('code')]) />
                        <button type="submit" class="btn btn-accent btn-lg join-item" wire:loading.attr="disabled">
                            <span wire:loading wire:target="check" class="loading loading-spinner loading-sm"></span>
                            <x-ui.icon name="search" class="size-5" wire:loading.remove wire:target="check" /> {{ __('Check') }}
                        </button>
                    </div>
                    @error('code')
                        <p class="mt-2 text-sm font-medium text-red-300">{{ $message }}</p>
                    @enderror
                </form>
            </div>

            <div class="hero-purple order-first flex items-center justify-center p-8 sm:p-12 lg:order-last lg:bg-transparent">
                <x-ui.logo class="h-auto w-44 sm:w-64 lg:w-full lg:max-w-sm" />
            </div>
            </div>
        </section>

        <ol class="grid gap-4 md:grid-cols-3">
            @foreach ([
                ['icon' => 'search', 'title' => __('Find your code'), 'text' => __('It looks like JJ-XXXXXX and is shown after you submit the form.')],
                ['icon' => 'clipboard-check', 'title' => __('Follow the review'), 'text' => __('The committee checks your data and documents.')],
                ['icon' => 'qr', 'title' => __('Print your cards'), 'text' => __('Participant cards are available once verified and groups are assigned.')],
            ] as $tip)
                <li class="rounded-box bg-base-100 shadow-soft flex gap-4 p-5">
                    <span class="bg-primary/10 text-primary grid size-11 shrink-0 place-items-center rounded-full"><x-ui.icon :name="$tip['icon']" class="size-5" /></span>
                    <div>
                        <p class="font-bold">{{ $loop->iteration }}. {{ $tip['title'] }}</p>
                        <p class="text-base-content/65 mt-1 text-sm leading-relaxed">{{ $tip['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        @php
            $status = $troop->status;
            $participants = $troop->participants;
            $isVerified = $status === TroopStatus::Verified;
            $allGrouped = $participants->isNotEmpty() && $participants->every(fn ($p) => $p->group_number);

            // tampilan banner per status (kelas ditulis lengkap agar terbaca Tailwind)
            $banner = match ($status) {
                TroopStatus::Submitted => ['icon' => 'info', 'wrap' => 'bg-primary/5', 'chip' => 'bg-primary/15 text-primary', 'text' => __('Your registration was received and is waiting for the committee to review it.')],
                TroopStatus::Revision => ['icon' => 'exclamation', 'wrap' => 'bg-warning/10', 'chip' => 'bg-warning/25 text-warning-content', 'text' => __('The committee asked for changes. Please fix the data below and resubmit.')],
                TroopStatus::Verified => ['icon' => 'check-circle', 'wrap' => 'bg-success/5', 'chip' => 'bg-success/15 text-success', 'text' => __('Your registration is verified. Participant IDs have been issued.')],
                TroopStatus::Rejected => ['icon' => 'x-circle', 'wrap' => 'bg-error/5', 'chip' => 'bg-error/15 text-error', 'text' => __('Your registration was rejected. See the committee note for the reason.')],
            };

            // linimasa: done | current | error | todo
            $steps = [
                ['label' => __('Submitted'), 'state' => 'done'],
                ['label' => __('Under review'), 'state' => match ($status) {
                    TroopStatus::Verified => 'done',
                    TroopStatus::Rejected => 'error',
                    default => 'current',
                }],
                ['label' => __('Verified'), 'state' => $isVerified ? 'done' : 'todo'],
                ['label' => __('Cards ready'), 'state' => $isVerified ? ($allGrouped ? 'done' : 'current') : 'todo'],
            ];
        @endphp

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="min-w-0 space-y-6">
                {{-- status + linimasa --}}
                <section class="rounded-box bg-base-100 shadow-soft overflow-hidden">
                    <div class="{{ $banner['wrap'] }} flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:p-6">
                        <span class="{{ $banner['chip'] }} grid size-14 shrink-0 place-items-center rounded-full"><x-ui.icon :name="$banner['icon']" class="size-7" /></span>
                        <div class="min-w-0">
                            <p class="text-base-content/60 text-xs font-bold uppercase tracking-wide">{{ __('Registration status') }}</p>
                            <p class="text-2xl font-extrabold tracking-tight">{{ $status->label() }}</p>
                            <p class="text-base-content/70 mt-1 text-sm">{{ $banner['text'] }}</p>
                        </div>
                    </div>

                    <ol class="grid grid-cols-4 px-4 py-6 sm:px-8">
                        @foreach ($steps as $step)
                            @php
                                $previousDone = ! $loop->first && $steps[$loop->index - 1]['state'] === 'done';
                            @endphp
                            <li class="relative flex flex-col items-center gap-2 text-center">
                                @unless ($loop->first)
                                    <span @class(['absolute right-1/2 top-5 h-0.5 w-full -translate-y-1/2', 'bg-primary' => $previousDone, 'bg-base-300' => ! $previousDone])></span>
                                @endunless
                                <span @class([
                                    'relative z-10 grid size-10 place-items-center rounded-full text-sm font-bold',
                                    'bg-primary text-primary-content' => $step['state'] === 'done',
                                    'bg-base-100 text-primary ring-primary ring-2' => $step['state'] === 'current',
                                    'bg-error text-error-content' => $step['state'] === 'error',
                                    'bg-base-200 text-base-content/40' => $step['state'] === 'todo',
                                ])>
                                    @if ($step['state'] === 'done')
                                        <x-ui.icon name="check" class="size-5" />
                                    @elseif ($step['state'] === 'error')
                                        <x-ui.icon name="x-circle" class="size-5" />
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </span>
                                <span @class(['text-xs font-semibold sm:text-sm', 'text-base-content/45' => $step['state'] === 'todo'])>{{ $step['label'] }}</span>
                            </li>
                        @endforeach
                    </ol>

                    @if ($troop->admin_note)
                        <div class="px-5 pb-6 sm:px-6">
                            <x-ui.alert :type="$status === TroopStatus::Rejected ? 'error' : 'warning'">
                                <p class="font-medium">{{ __('Committee note') }}</p>
                                <p class="whitespace-pre-line text-sm">{{ $troop->admin_note }}</p>
                            </x-ui.alert>
                        </div>
                    @endif
                </section>

                {{-- daftar peserta --}}
                <section class="rounded-box bg-base-100 shadow-soft p-5 sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="text-lg font-extrabold">{{ __('Participants') }}</h2>
                        <span class="badge badge-soft badge-primary">{{ $participants->count() }}</span>
                    </div>

                    <div class="rounded-box max-h-[28rem] overflow-auto">
                        <table class="table table-sm table-pin-rows">
                            <thead>
                                <tr>
                                    <th>#</th><th>{{ __('KTA number') }}</th>
                                    @if ($isVerified)<th>{{ __('Participant ID') }}</th>@endif
                                    <th>{{ __('Full name') }}</th><th>{{ __('Gender') }}</th><th>{{ __('Group') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($participants as $participant)
                                    <tr wire:key="p-{{ $participant->id }}">
                                        <td class="text-base-content/50">{{ $loop->iteration }}</td>
                                        <td class="font-mono">{{ $participant->kta_number }}</td>
                                        @if ($isVerified)
                                            <td class="whitespace-nowrap font-mono text-xs">{{ $participant->attendance_code }}</td>
                                        @endif
                                        <td class="font-medium">{{ $participant->full_name }}</td>
                                        <td>{{ $participant->gender->value }}</td>
                                        <td>
                                            @if ($participant->group_number)
                                                <x-ui.group-badge :number="$participant->group_number" size="md" :label="true" />
                                            @else
                                                <span class="text-base-content/40 text-xs">{{ __('Not assigned yet') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($troop->isEditableByRegistrant())
                    <livewire:public.revision-form :troop-id="$troop->id" :code="$troop->registration_code" wire:key="revision-{{ $troop->id }}" />
                @endif
            </div>

            {{-- samping: identitas gugusdepan --}}
            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                <section class="hero-dark text-neutral-content rounded-box shadow-soft space-y-5 p-6">
                    <div>
                        <p class="text-accent text-xs font-bold uppercase tracking-wide">{{ __('Troop number') }}</p>
                        <p class="mt-1 text-3xl font-extrabold tabular-nums tracking-tight text-white">{{ $troop->number_label }}</p>
                        <p class="mt-2 text-lg font-semibold leading-snug text-white">{{ $troop->troop_name }}</p>
                        @if ($troop->unit)
                            <p class="text-neutral-content/70 mt-1 text-sm">{{ $troop->unit->label() }}</p>
                        @endif
                    </div>

                    <div class="rounded-box bg-white/10 px-4 py-3">
                        <p class="text-neutral-content/65 text-xs">{{ __('Registration code') }}</p>
                        <p class="font-mono text-lg font-bold tracking-widest text-white">{{ $troop->registration_code }}</p>
                    </div>

                    <dl class="grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-box bg-white/10 py-3">
                            <dd class="text-xl font-extrabold tabular-nums text-white">{{ $participants->count() }}</dd>
                            <dt class="text-neutral-content/65 text-[11px]">{{ __('Participants') }}</dt>
                        </div>
                        <div class="rounded-box bg-white/10 py-3">
                            <dd class="text-xl font-extrabold tabular-nums text-white">{{ $participants->filter(fn ($p) => $p->gender->value === 'L')->count() }}</dd>
                            <dt class="text-neutral-content/65 text-[11px]">{{ __('Male') }}</dt>
                        </div>
                        <div class="rounded-box bg-white/10 py-3">
                            <dd class="text-xl font-extrabold tabular-nums text-white">{{ $participants->filter(fn ($p) => $p->gender->value === 'P')->count() }}</dd>
                            <dt class="text-neutral-content/65 text-[11px]">{{ __('Female') }}</dt>
                        </div>
                    </dl>

                    <ul class="text-neutral-content/75 space-y-1 text-xs">
                        <li>{{ __('Submitted at :date', ['date' => $troop->submitted_at?->translatedFormat('d F Y H:i')]) }}</li>
                        @if ($troop->verified_at)
                            <li>{{ __('Verified at :date', ['date' => $troop->verified_at->translatedFormat('d F Y H:i')]) }}</li>
                        @endif
                    </ul>
                </section>

                @if ($certificateAvailable)
                    <section class="rounded-box bg-base-100 shadow-soft space-y-3 p-5">
                        <div>
                            <h2 class="text-lg font-extrabold">{{ __('E-Certificates') }}</h2>
                            <p class="text-base-content/60 text-sm">{{ __('The event is completed. Download your certificates below.') }}</p>
                        </div>

                        <a href="{{ route('certificates.troop', $troop->registration_code) }}" class="btn btn-secondary btn-block">
                            <x-ui.icon name="download" class="size-5" /> {{ __('Troop certificate') }}
                        </a>

                        @if ($certificateParticipants->isNotEmpty())
                            <a href="{{ route('certificates.participants', $troop->registration_code) }}" class="btn btn-primary btn-block">
                                <x-ui.icon name="download" class="size-5" /> {{ __('All participant certificates') }} ({{ $certificateParticipants->count() }})
                            </a>

                            <details class="rounded-box bg-base-200/60">
                                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold">{{ __('Per participant') }}</summary>
                                <ul class="max-h-64 divide-y divide-base-300 overflow-auto px-4 pb-2 text-sm">
                                    @foreach ($certificateParticipants as $participant)
                                        <li class="flex items-center justify-between gap-2 py-2">
                                            <span class="min-w-0 truncate">{{ $participant->full_name }}</span>
                                            <a href="{{ route('certificates.participants', [$troop->registration_code, $participant->attendance_code]) }}" class="btn btn-ghost btn-xs btn-square" aria-label="{{ __('Download') }}">
                                                <x-ui.icon name="download" class="size-4" />
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @else
                            <p class="text-base-content/60 text-xs">{{ __('No participant has attendance records, so participant certificates are not available.') }}</p>
                        @endif
                    </section>
                @endif

                <section class="rounded-box bg-base-100 shadow-soft space-y-3 p-5">
                    @if ($isVerified)
                        <a href="{{ route('cards.registrant', $troop->registration_code) }}" target="_blank" class="btn btn-primary btn-block">
                            <x-ui.icon name="qr" class="size-5" /> {{ __('Print participant cards') }}
                        </a>
                        @unless ($allGrouped)
                            <p class="text-base-content/60 text-center text-xs">{{ __('Cards are available after groups are assigned.') }}</p>
                        @endunless
                    @endif
                    <button type="button" class="btn btn-ghost btn-block" wire:click="checkAnother">
                        <x-ui.icon name="arrow-left" class="size-4" /> {{ __('Check another registration') }}
                    </button>
                </section>
            </aside>
        </div>
    @endif
</div>

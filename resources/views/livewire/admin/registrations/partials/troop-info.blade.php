<x-ui.card :title="__('Troop Data')" icon="flag">
    <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
        <div>
            <dt class="text-base-content/60">{{ __('Troop number') }}</dt>
            <dd class="font-medium">{{ $troop->number_label }}</dd>
        </div>
        <div>
            <dt class="text-base-content/60">{{ __('Troop name') }}</dt>
            <dd class="font-medium">{{ $troop->troop_name }}</dd>
        </div>
        <div>
            <dt class="text-base-content/60">{{ __('Unit') }}</dt>
            <dd class="font-medium">{{ $troop->unit?->label() }}</dd>
        </div>
        <div>
            <dt class="text-base-content/60">{{ __('Person in charge') }}</dt>
            <dd>{{ $troop->pic_name }}</dd>
        </div>
        <div>
            <dt class="text-base-content/60">{{ __('Phone / WhatsApp') }}</dt>
            <dd>
                <a class="link" target="_blank" href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $troop->pic_phone)) }}">{{ $troop->pic_phone }}</a>
            </dd>
        </div>
        <div>
            <dt class="text-base-content/60">{{ __('Email') }}</dt>
            <dd>{{ $troop->pic_email ?: '-' }}</dd>
        </div>
        <div>
            <dt class="text-base-content/60">{{ __('Submitted') }}</dt>
            <dd>{{ $troop->submitted_at?->translatedFormat('d F Y H:i') }}</dd>
        </div>
    </dl>
</x-ui.card>

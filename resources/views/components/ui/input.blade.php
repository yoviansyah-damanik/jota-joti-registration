@props(['label' => null, 'name', 'type' => 'text', 'hint' => null, 'required' => false])

<fieldset class="fieldset">
    @if ($label)
        <legend class="fieldset-legend">
            {{ $label }} @if ($required)<span class="text-error">*</span>@endif
        </legend>
    @endif

    {{-- slot append: tombol yang menempel di kanan input; pesan error tetap di bawah keduanya --}}
    @isset($append)
        <div class="join w-full">
            <input
                type="{{ $type }}"
                id="{{ $name }}"
                {{ $attributes->class(['input join-item w-full', 'input-error' => $errors->has($name)]) }}
            />
            {{ $append }}
        </div>
    @else
        <input
            type="{{ $type }}"
            id="{{ $name }}"
            {{ $attributes->class(['input w-full', 'input-error' => $errors->has($name)]) }}
        />
    @endisset

    @error($name)
        <p class="label whitespace-normal text-error">{{ $message }}</p>
    @elseif ($hint)
        <p class="label whitespace-normal">{{ $hint }}</p>
    @enderror
</fieldset>

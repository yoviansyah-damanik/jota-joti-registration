@props(['label' => null, 'name', 'hint' => null, 'rows' => 3, 'required' => false])

<fieldset class="fieldset">
    @if ($label)
        <legend class="fieldset-legend">
            {{ $label }} @if ($required)<span class="text-error">*</span>@endif
        </legend>
    @endif

    <textarea
        id="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->class(['textarea w-full', 'textarea-error' => $errors->has($name)]) }}
    ></textarea>

    @error($name)
        <p class="label whitespace-normal text-error">{{ $message }}</p>
    @elseif ($hint)
        <p class="label whitespace-normal">{{ $hint }}</p>
    @enderror
</fieldset>

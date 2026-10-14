@props(['label' => null, 'name', 'options' => [], 'placeholder' => null, 'hint' => null])

<fieldset class="fieldset">
    @if ($label)
        <legend class="fieldset-legend">{{ $label }}</legend>
    @endif

    <select id="{{ $name }}" {{ $attributes->class(['select w-full', 'select-error' => $errors->has($name)]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>

    @error($name)
        <p class="label whitespace-normal text-error">{{ $message }}</p>
    @elseif ($hint)
        <p class="label whitespace-normal">{{ $hint }}</p>
    @enderror
</fieldset>

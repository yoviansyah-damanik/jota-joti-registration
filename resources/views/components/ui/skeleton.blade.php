@props(['rows' => 4])

{{-- Placeholder untuk komponen Livewire lazy --}}
<div {{ $attributes->merge(['class' => 'card bg-base-100']) }}>
    <div class="card-body gap-3">
        <div class="skeleton h-5 w-1/3"></div>
        @for ($i = 0; $i < $rows; $i++)
            <div class="skeleton h-4 w-full"></div>
        @endfor
    </div>
</div>

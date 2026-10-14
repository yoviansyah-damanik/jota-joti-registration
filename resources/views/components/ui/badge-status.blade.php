@props(['status'])

{{-- Menerima enum yang memiliki label() dan badgeClass(); whitespace-nowrap agar label tidak pecah baris --}}
<span {{ $attributes->class(['badge badge-soft whitespace-nowrap', $status->badgeClass()]) }}>{{ $status->label() }}</span>

@props(['status'])

@php
    $color = match($status) {
        'unpaid' => 'amber',
        'partial' => 'blue',
        'paid' => 'green',
        default => 'zinc',
    };
    $label = strtoupper($status);
@endphp

<flux:badge :color="$color" size="sm">{{ $label }}</flux:badge>

@props(['status'])

@php
    $colors = match($status) {
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
        'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
@endphp

<span class="px-2 py-1 text-xs font-medium rounded {{ $colors }}">
    {{ ucfirst($status) }}
</span>

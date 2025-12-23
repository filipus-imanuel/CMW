@props([
    'rowId'               => null,
    'enable_this_row'     => true,
    'showDetail'          => null,
    'showEdit'            => null,
    'showDelete'          => null,
    'detailDispatchEvent' => "",
    'detailHref'          => null,
    'editDispatchEvent'   => "",
    'editHref'            => null,
    'deleteDispatchEvent' => "",
])
<div class="text-center">
    @if($enable_this_row)
        @if($showDetail)
            @if($detailHref)
                <flux:button href="{{ $detailHref }}" wire:navigate icon="eye" size="xs" class="ms-4 me-4"/>
            @else
                <flux:button icon="eye" size="xs" wire:click="$dispatch('{{ $detailDispatchEvent }}', { id: {{ $rowId }} })" class="ms-4 me-4"/>
            @endif
        @endif
        @if($showEdit)
            @if($editHref)
                <flux:button href="{{ $editHref }}" wire:navigate icon="pencil-square" size="xs" class="ms-4 me-4"/>
            @else
                <flux:button icon="pencil-square" size="xs" wire:click="$dispatch('{{ $editDispatchEvent }}', { id: {{ $rowId }} })" class="ms-4 me-4"/>
            @endif
        @endif
        @if($showDelete)
            <flux:button variant="danger" icon="trash" size="xs" wire:click="$dispatch('{{ $deleteDispatchEvent }}', { id: {{ $rowId }} })" />
        @endif
    @endif
    {{ $slot ?? '' }}
</div>
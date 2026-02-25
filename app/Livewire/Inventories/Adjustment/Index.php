<?php

namespace App\Livewire\Inventories\Adjustment;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Stock Adjustments')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.inventories.adjustment.index');
    }
}

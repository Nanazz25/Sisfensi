<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ModalDelete extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $message = null
    ) {}

    public function render()
    {
        return view('components.modal-delete');
    }
}

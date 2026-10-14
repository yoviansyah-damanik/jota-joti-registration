<?php

namespace App\Livewire\Concerns;

use Livewire\WithPagination;

/**
 * Pagination Livewire dengan tampilan daisyUI yang seragam di seluruh aplikasi.
 */
trait UsesDaisyPagination
{
    use WithPagination;

    public function paginationView(): string
    {
        return 'pagination.daisyui';
    }
}

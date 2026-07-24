<?php

namespace App\Http\Livewire\Pages;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Pages\Page;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class PagesComponent extends Component
{
    private ?int $page_id = null;
    protected $listeners = ['setPageId'];

    public function render()
    {
        return view('livewire.pages.pages.index', [
            'page_id' => $this->page_id,
        ]);
    }

    /**
     * @param $id
     */
    public function setPageId($id = null)
    {
        $this->page_id = $id;
    }

}

<?php

namespace App\Http\Livewire\Subscriptions\Packages;

use App\Models\Subscriptions\Packages\Package;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class PackagesComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $package_id = null;
    protected $listeners = ['setPackageId', 'recountCounters'];
    public ?int $all_packages_count = null;
    public ?int $active_packages_count = null;
    public ?int $inactive_packages_count = null;
    public ?int $filter_id = null;

    public function render()
    {
        $this->all_packages_count = Package::count();

        $this->active_packages_count = Package::where('is_active', true)
            ->count();

        $this->inactive_packages_count = Package::where('is_active', false)
            ->count();

        if ($this->filter_id) {
            $activeNumberFilters = [
                '1' => [
                    'start' => $this->filter_id,
                    'end' => $this->filter_id,
                ]
            ];
        }
        return view('livewire.pages.subscriptions.packages.inquiry', [
            'package_id' => $this->package_id,
            'activeNumberFilters' => $activeNumberFilters ?? []
        ]);
    }

    /**
     * @param $active
     */
    public function changeActiveTab($active)
    {
        $this->page_type = $active;

        $this->emit('rerenderDataTable', ['page_type' => $active]);
    }

    /**
     * @param $id
     */
    public function setPackageId($id = null)
    {
        $this->package_id = $id;

        if (!$id) {
            $this->emit('rerenderDataTable', ['page_type' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_packages_count = Package::count();

        $this->active_packages_count = Package::where('is_active', true)
            ->count();

        $this->inactive_packages_count = Package::where('is_active', false)
            ->count();
    }
}

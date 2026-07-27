<?php

namespace App\Http\Livewire\Countries\Cities;

use App\Models\Countries\Cities\City;
use App\Models\Countries\Governorates\Governorate;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class CitiesSortComponent extends Component
{
    use LivewireAlert;
    use WithFileUploads;

    public int $governorate_id;
    public array $order = [];
    public string $language_column = 'name_ar';

    protected $listeners = [
        'showAddModal',
    ];

    public function loadScripts()
    {
        $this->dispatchBrowserEvent('loadScripts');
    }

    public function render()
    {
        $this->language_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        $cities = City::orderBy('order')
            ->where('governorate_id', $this->governorate_id)
            ->get()
            ->map(function ($city) {
                return [
                    'name' => $city->{$this->language_column},
                    'id' => $city->id,
                ];
            });

        $this->order = City::orderBy('order')
            ->where('governorate_id', $this->governorate_id)
            ->pluck('id')
            ->toArray();

        return view('admin.pages.countries.cities.sort', ['cities' => $cities]);
    }

    public function setOrder($orders)
    {
        DB::beginTransaction();
        try {
            foreach ($orders as $index => $order) {
                City::where('id', $order)
                    ->first()
                    ->update([
                        'order' => $index + 1,
                    ]);
            }
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }

        DB::commit();
        $this->alert('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }
}

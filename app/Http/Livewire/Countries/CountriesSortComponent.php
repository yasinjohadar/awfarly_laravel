<?php

namespace App\Http\Livewire\Countries;

use App\Models\Countries\Country;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class CountriesSortComponent extends Component
{
    use LivewireAlert;

    use WithFileUploads;

    public array $order = [];
    public string $language_column = 'name_ar';

    protected $listeners = [
        'showAddModal'
    ];

    /**
     * dispatch event to load scripts in the view
     */
    public function loadScripts()
    {
        $this->dispatchBrowserEvent('loadScripts');
    }

    public function render()
    {
        $this->language_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        $countries = Country::orderBy('order')
            ->get()
            ->map(function ($country) {
                return [
                    'name' => $country->{$this->language_column},
                    'id' => $country->id,
                ];
            });

        $this->order = Country::orderBy('order')
            ->pluck('id')
            ->toArray();

        return view('admin.pages.countries.sort', ['countries' => $countries]);
    }

    /**
     * set new order for files
     */
    public function setOrder($orders)
    {
        DB::beginTransaction();
        try {
            foreach ($orders as $index => $order) {
                Country::where('id', $order)
                    ->first()
                    ->update([
                        'order' => $index + 1
                    ]);
            }
            /*$this->dispatchBrowserEvent('getData');*/
        } catch (Throwable $e) {
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                 'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();
        //send toastr alert with success
        $this->alert('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }
}

<?php

namespace App\Http\Livewire\Subscriptions\Packages;

use App\Models\Subscriptions\Packages\Package;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class PackagesShowComponent extends Component
{
    public int $package_id;
    public string $features_lang = 'ar';

    public function mount(): void
    {
        $this->features_lang = App::currentLocale() === 'en' ? 'en' : 'ar';
    }

    public function setFeaturesLang(string $lang): void
    {
        if (!in_array($lang, ['ar', 'en'], true)) {
            return;
        }

        $this->features_lang = $lang;
    }

    public function render()
    {
        $package = Package::where('id', $this->package_id)
            ->withCount('advertisers')
            ->first();

        return view('admin.pages.subscriptions.packages.show', [
            'package' => $package,
            'features_lang' => $this->features_lang,
        ]);
    }
}

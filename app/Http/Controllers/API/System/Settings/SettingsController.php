<?php

namespace App\Http\Controllers\API\System\Settings;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Public site branding settings for the mobile/web app.
     *
     * @return Application|ResponseFactory|Response
     */
    public function getSettings()
    {
        $logo = Settings::Logo();

        if (is_string($logo) && !Str::startsWith($logo, ['http://', 'https://'])) {
            $logo = url($logo);
        }

        return $this->apiResponse([
            'name' => Settings::Get('site.name', config('app.name')),
            'logoUrl' => $logo,
        ]);
    }
}

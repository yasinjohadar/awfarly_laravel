<?php

namespace App\Http\Controllers\API\System\GeoIP;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use PulkitJalan\GeoIP\GeoIP;

class GeoIPController extends Controller
{
    public function getGeoIP()
    {
        try {
            //Get user data by ip
            $geoip = (new GeoIP())->get();
        } catch (Exception $exception) {
            return $this->apiExceptionResponse(__('api/shared/requests/requests.something-wrong'));
        }

        return $this->apiResponse([
            'data' => [
                'ip' => request()->getClientIp(),
                'countryCode' => $geoip['countryCode'] ?? 'SA',
                'latitude' => $geoip['latitude'],
                'longitude' => $geoip['longitude'],
            ]
        ]);
    }
}

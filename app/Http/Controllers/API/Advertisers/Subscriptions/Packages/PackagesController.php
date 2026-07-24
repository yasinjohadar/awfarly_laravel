<?php

namespace App\Http\Controllers\API\Advertisers\Subscriptions\Packages;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Advertisers\Subscriptions\Packages\AdvertiserPackagesResource;
use App\Http\Resources\Advertisers\Subscriptions\Packages\PackagesResource;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use App\Models\Subscriptions\Packages\Package;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PackagesController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getPackages()
    {
        //get packages
        $packages = Package::where('is_active', true)
            ->where('is_visible', true)
            ->whereNotNull('product_id')
            ->get();

        //return the data
        return $this->apiResponse(PackagesResource::collection($packages));
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getPackageById($id)
    {
        //get package by ID
        $package = Package::where('id', $id)
            ->where('is_active', true)
            ->where('is_visible', true)
            ->whereNotNull('product_id')
            ->first();

        //return error if there is no package found
        if (!$package) {
            return $this->apiBadRequestResponse(__('api/advertisers/subscriptions/packages/packages.wrong-id'));
        }

        //return data
        return $this->apiResponse(PackagesResource::make($package));
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    public function getUserPackage()
    {
        //get current user package
        $package = AdvertiserPackages::where('advertiser_id', Auth::guard('advertiser-api')->id())
            ->where('advertiser_packages.is_current', true)
            ->join('packages', function ($q){
                $q->on('packages.id', 'advertiser_packages.package_id')
                    ->where('packages.is_visible', true);
            })
            ->first();

        //return error if there is no package found
        if (!$package) {
            return $this->apiBadRequestResponse(__('api/advertisers/subscriptions/packages/packages.no-packages'));
        }

        //return data
        return $this->apiResponse(PackagesResource::make($package->package));
    }
}

<?php

namespace App\Http\Controllers\API\Shared\Checks;

use App\Http\Controllers\Controller;
use App\Models\Users\Admins\AdminUser;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RegisterDataChecksController extends Controller
{
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function checkData(Request $request)
    {
        //get column
        $column = ['column' => array_key_first($request->all())];

        //validate column
        $this->apiValidate($column, [
            'column' => ['required', 'in:username,mobile,email'],
        ]);

        //set column
        $column = $column['column'];

        //check if admin exists
        $admin = AdminUser::where($column, $request->get($column))
            ->exists();

        //check if advertiser exists
        $advertiser = AdvertiserUser::where($column, $request->get($column))
            ->exists();

        //check if customer exists
        $customer = CustomerUser::where($column, $request->get($column))
            ->exists();

        //return true if data was found
        if ($admin || $advertiser || $customer) {
            return $this->apiResponse([
                'message' => __('api/shared/data-checks/data-checks.found', ['type' => ucwords($column)]),
            ]);
        }

        //return false if data wasn't found
        return $this->apiBadRequestResponse(__('api/shared/data-checks/data-checks.notfound', ['type' => ucwords($column)]));
    }
}

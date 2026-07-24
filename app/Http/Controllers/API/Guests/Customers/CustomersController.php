<?php

namespace App\Http\Controllers\API\Guests\Customers;

use App\Helpers\Filter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Users\Customers\CustomersResource;
use App\Http\Resources\Users\Customers\Reports\ReportedCustomersResource;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CustomersController extends Controller
{
    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getCustomerById($id)
    {
        //get user by id
        $customer = CustomerUser::where('id', $id)
            ->first();

        //return error if user wasn't found
        if (!$customer) {
            return $this->apiBadRequestResponse(__('api/guests/customers/customers.wrong-id'));
        }

        //return error if account is closed
        if ($customer->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/guests/customers/customers.status-closed'));
        } elseif ($customer->status === 'banned') //return error if account is banned
        {
            return $this->apiBadRequestResponse(__('api/guests/customers/customers.status-banned'));
        }

        return $this->apiResponse(CustomersResource::make($customer));
    }
}

<?php

namespace App\Http\Controllers\API\Customers\Requests;

use App\Helpers\Filter;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customers\Requests\UsernameRequestsResource;
use App\Models\Requests\UsernameRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestsController extends Controller
{
    /**
     * @param Request $request
     * @param null $id
     * @return Application|ResponseFactory|Response
     */
    public function getUsernameChangeRequests(Request $request, $id = null)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('username.change.pagination.limit', 10);

        //get requests
        $requests = Auth::guard('customer-api')->user()
            ->usernameRequests();

        //if id is provided then get this id if not then show all
        if ($id) {
            $requests = $requests->where('id', $id)
                ->first();
            return $this->apiResponse(UsernameRequestsResource::make($requests));
        }

        //get all requests
        $requests = $requests->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(UsernameRequestsResource::collection($requests));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function changeUsername(Request $request)
    {
        //get user
        $user = Auth::guard('customer-api')->user();

        //get data
        $data = $request->all();

        //validate
        $this->apiValidate($data, [
            'newUsername' => ['required', 'unique:admins_users,username', 'unique:customers_users,username', "unique:advertisers_users,username"],
            'reason' => ['required'],
        ]);

        $username_exists = UsernameRequests::where('new_username', $data['newUsername'])
            ->where('status', 'pending')
            ->first();

        if ($username_exists) {
            return $this->apiBadRequestResponse(__('api/customers/requests/requests.change-username.username-exists'));
        }

        //check if there is old request
        $old_request = $user->usernameRequests()
            ->where('status', 'approved')
            ->latest()
            ->first();

        //return error if old request and now exceeded the time limit
        if ($old_request) {
            //get the limit
            $limit = Settings::Get('username.change.limit', 30);

            //check difference
            $difference = Carbon::make($old_request->created_at)->diffInDays(Carbon::now());

            if ($difference < $limit) {
                return $this->apiBadRequestResponse(__('api/customers/requests/requests.change-username.limit-exceeded', ['days' => $limit]));
            }
        }

        DB::beginTransaction();
        try {
            //create the request
            $request = $user->usernameRequests()
                ->updateOrCreate([
                    'old_username' => $user->username,
                    'status' => 'pending',
                ], [
                    'new_username' => Filter::RemoveHtml($data['newUsername']),
                    'reason' => Filter::RemoveHtml($data['reason']),
                ]);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->apiExceptionResponse(__('api/customers/requests/requests.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/requests/requests.change-username.request-sent'),
            'data' => UsernameRequestsResource::make($request),
        ]);
    }
}

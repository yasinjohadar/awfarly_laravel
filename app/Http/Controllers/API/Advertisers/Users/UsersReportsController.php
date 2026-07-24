<?php

namespace App\Http\Controllers\API\Advertisers\Users;

use App\Helpers\Filter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Users\Advertisers\Reports\ReportedAdvertisersResource;
use App\Http\Resources\Users\Customers\Reports\ReportedCustomersResource;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersReportsController extends Controller
{

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportUser(Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //set data
        $data = $request->only([
            'userType',
            'userId',
            'reportType',
            'reportReason',
        ]);

        //validate data
        $this->apiValidate($data, [
            'userType' => ['required', 'in:advertiser,customer'],
            'userId' => ['required'],
            'reportType' => ['nullable', 'in:Sexually Inappropriate,Abusive Content,Misleading or Scam,Offensive,Violence,Prohibited Content,Spam,False News,Other'],
            'reportReason' => ['nullable'],
        ]);


        $user = Auth::guard('advertiser-api')->user();

        if ($data['userType'] === 'advertiser' && $data['userId'] == $user->id) {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.reports.self-report'));
        }
        if ($data['userType'] === 'customer') {
            $reported = CustomerUser::query()
                ->where('id', $data['userId'])
                ->first();
        } else {
            $reported = AdvertiserUser::query()
                ->where('id', $data['userId'])
                ->first();
        }

        if (!$reported) {
            return $this->apiBadRequestResponse(__('api/auth/auth.no-user'));
        }

        if ($reported->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        $block = $user->block()
            ->where('blocked_type', $reported->class)
            ->where('blocked_id', $data['userId'])
            ->exists();

        $blocked = $user->blocked()
            ->where('blocker_type', $reported->class)
            ->where('blocker_id', $data['userId'])
            ->exists();

        if ($block || $blocked) {
            return $this->apiBadRequestResponse($data['userType'] === 'advertiser' ?
                __('api/advertisers/advertisers/advertisers.unavailable') :
                __('api/advertisers/customers/customers.unavailable'));
        }

        //check if user already reported
        $report = $reported->report()
            ->where('user_type', $user->class)
            ->where('user_id', $user->id)
            ->first();

        try {
            //create report
            if (!$report) {
                $input_data = [
                    'user_type' => $user->class,
                    'user_id' => $user->id,
                    'reason' => isset($data['reportReason']) ? Filter::RemoveHtml($data['reportReason']) : null,
                ];
                if (isset($data['reportType'])) {
                    $input_data['type'] = $data['reportType'];
                }
                $report = $reported->report()
                    ->create($input_data);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/advertisers/advertisers.something-wrong'));
        }
        DB::commit();
        $reported_type = $data['userType'] === 'customer' ? 'customers' : 'advertisers';
        return $this->apiResponse([
            'message' => __("api/advertisers/{$reported_type}/{$reported_type}.reports.report-added"),
            'data' => $data['userType'] === 'customer' ? ReportedCustomersResource::make($report) : ReportedAdvertisersResource::make($report),
        ]);
    }
}

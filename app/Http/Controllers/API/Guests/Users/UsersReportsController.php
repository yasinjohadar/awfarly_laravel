<?php

namespace App\Http\Controllers\API\Guests\Users;

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

        try {
            //create report
            $input_data = [
                'user_type' => null,
                'user_id' => null,
                'reason' => isset($data['reportReason']) ? Filter::RemoveHtml($data['reportReason']) : null,
            ];
            if (isset($data['reportType'])) {
                $input_data['type'] = $data['reportType'];
            }
            $report = $reported->report()
                ->create($input_data);
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

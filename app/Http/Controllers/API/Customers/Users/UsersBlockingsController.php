<?php

namespace App\Http\Controllers\API\Customers\Users;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\UsersBlockings\UsersBlockedResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UsersBlockingsController extends Controller
{
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getBlockedUsers(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('user.followings.pagination.limit', 10);

        $user = Auth::guard('customer-api')->user();

        $blocked_users = $user->block()
            ->whereHasMorph('blocked', '*', function ($q) {
                return $q->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(UsersBlockedResource::collection($blocked_users));
    }
}

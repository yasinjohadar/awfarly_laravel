<?php

namespace App\Http\Middleware;

use App\Http\Traits\APIResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class User
{
    use APIResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param Request $request Incoming request.
     * @param Closure $next
     * @param null $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::guard('customer-api')->check() && auth()->user()->token()->name == 'customer') {
            $type = 'customer';
        } elseif (Auth::guard('advertiser-api')->check() && auth()->user()->token()->name == 'advertiser') {
            $type = 'advertiser';
        } elseif (Auth::guard('admin')->check()) {
            $type = 'admin';
        } elseif (Auth::guard($guard)->check()) {
            $type = $guard;
        } else {
            $type = null;
        }

        //Handle type
        if (!is_null($type)) {
            //Set user
            $user = auth()->user();

            //Check account if is active or not
            if (isset($user->status) && in_array($user->status, ['pending', 'suspended', 'banned'])) {
                // If admin
                if ($type == 'admin') {
                    return abort(401);
                }

                // Delete tokens
                $user->tokens->each(function ($token, $key) {
                    $token->delete();
                });

                return $this->apiResponse(['type' => 'unauthorized']);
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

    /**
     * Attempt Login multi auth
     *
     * @param Request $request
     * @return mixed
     */
    protected function attemptLogin(Request $request)
    {
        //Attempt admin
        $auth = Auth::guard('admin')->attempt(
            $this->credentials($request), $request->has('remember')
        );
        //Log login for admin
        if ($auth) {
            //update last login at
            Auth::guard('admin')->user()->update([
                'last_login_at' => now(),
            ]);
        }

        return $auth;
    }

    /**
     * The user has logged out of the application.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    protected function loggedOut(Request $request): RedirectResponse
    {
        return redirect()->route('login');
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        Auth::logout();

        //Remove sessions
        $request->session()->invalidate();

        return redirect()->route('login');
    }
}

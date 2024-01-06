<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /* public function username()
    {
        return 'username';
    } */

    protected function authenticated(Request $request, $user)
    {
        $request->session()->flash('flash', __('Welcome back to the hood, we missed you!'));
    }

    protected function loggedOut(Request $request)
    {
        $request->session()->flash('flash', __('You are now logged out. See you soon!'));
    }
}

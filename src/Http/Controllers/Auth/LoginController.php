<?php

namespace Tsrgtm\Pageman\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && method_exists($user, 'canAccessPagemanAdmin') && $user->canAccessPagemanAdmin()) {
                return redirect()->route(config('pageman.auth.dashboard_redirect_route', '/'));
            }
            // If logged in but not a Pageman admin, they shouldn't be here via 'guest' middleware.
            // However, if they land here, log them out of the current session before showing Pageman login.
            // Or, redirect them to their default app dashboard if Pageman is not their primary interface.
            // For simplicity, we'll rely on the 'guest' middleware to handle already authenticated users.
        }
        return view('pageman::admin.login');
    }

    /**
     * Handle a login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            if ($user && method_exists($user, 'canAccessPagemanAdmin') && $user->canAccessPagemanAdmin()) {
                return redirect()->intended(config('pageman.auth.dashboard_redirect_route', '/'));
            }
            Auth::logout();
            return redirect()->back()->withErrors([
                'email' => 'You do not have permissions to access the admin dashboard.',
            ]);
        }

        return redirect()->back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Log the user out.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route(config('pageman.auth.login_route', 'pageman.auth.login'));
    }
}


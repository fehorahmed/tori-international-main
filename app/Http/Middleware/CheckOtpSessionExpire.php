<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOtpSessionExpire
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $otp_login = $request->session()->get('otp_login');
            $expireAt = $request->session()->get('session_expire_at');
            //dd($otp_login,now()->greaterThan($expireAt));
            if ($otp_login && $expireAt && now()->greaterThan($expireAt)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/admin/login')->with('message', 'Session expired, please login again.');
            }
        }
        
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BranchManagement\SisterConcern;

class CheckHeadOffice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $sisterConcern = SisterConcern::find($user->activeConcernId());
            
            if ($sisterConcern && $sisterConcern->is_head_office == 1) {
                return $next($request);
            }
        }
        abort(403, 'Unauthorized action. Head Office access required.');
    }
}

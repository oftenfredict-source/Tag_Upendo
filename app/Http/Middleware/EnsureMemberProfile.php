<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->hasMemberProfile()) {
            abort(403, __('Your login is not linked to a member profile. Please contact the church office.'));
        }

        return $next($request);
    }
}

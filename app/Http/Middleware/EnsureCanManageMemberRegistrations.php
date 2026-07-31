<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageMemberRegistrations
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->canManageMemberRegistrations()) {
            abort(403, __('You do not have permission to access this page.'));
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageServiceRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(403, __('You do not have permission to access this page.'));
        }

        if ($user->canManageServiceRequests()) {
            return $next($request);
        }

        if ($user->isSecretary()) {
            if ($user->hasMemberProfile()) {
                return redirect()
                    ->route('my.portal', ['tab' => 'requests'])
                    ->with('info', __('Use My Portal to submit and track your own service requests.'));
            }

            return redirect()
                ->route('dashboard')
                ->with('error', __('Your login is not linked to a member profile. Please contact the church office to submit service requests.'));
        }

        abort(403, __('You do not have permission to access this page.'));
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! auth()->check()) {
            return $response;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        if (! $response->isSuccessful() && ! $response->isRedirection()) {
            return $response;
        }

        $routeName = $request->route()?->getName();
        $description = $this->descriptionFor($routeName, $request);

        if ($description) {
            ActivityLogger::log($routeName ?? 'action', $description);
        }

        return $response;
    }

    protected function descriptionFor(?string $routeName, Request $request): ?string
    {
        return match ($routeName) {
            'members.store' => __('Added a new member'),
            'members.update' => __('Updated a member profile'),
            'members.archive' => __('Archived a member'),
            'members.restore' => __('Restored an archived member'),
            'members.generate-password' => __('Generated member login password'),
            'members.link-spouse' => __('Linked spouse to member'),
            'members.unlink-spouse' => __('Unlinked spouse from member'),
            'follow-ups.store' => __('Recorded a follow-up message'),
            'bulk-sms.store' => __('Sent bulk SMS'),
            'attendance.store' => __('Created attendance record'),
            'attendance.collect.save' => __('Saved service attendance'),
            'attendance.destroy' => __('Deleted attendance record'),
            'departments.store' => __('Created a department'),
            'departments.assign-member' => __('Assigned member to department'),
            'calendar.events.store' => __('Created calendar event'),
            'calendar.events.update' => __('Updated calendar event'),
            'calendar.events.destroy' => __('Deleted calendar event'),
            'services.update' => __('Updated church service'),
            'services.destroy' => __('Deleted church service'),
            'leadership.store' => __('Assigned service leadership'),
            'leadership.update' => __('Updated service leadership'),
            'leadership.destroy' => __('Removed service leadership'),
            'church-leaders.roles.store' => __('Created leadership role'),
            'church-leaders.assign' => __('Assigned church leader'),
            'church-leaders.unassign' => __('Removed church leader'),
            'offerings.store' => __('Recorded offering'),
            'tithes.store' => __('Recorded tithe'),
            'tithes.destroy' => __('Deleted tithe record'),
            'pledges.store' => __('Created pledge'),
            'pledges.pay' => __('Recorded pledge payment'),
            'expenses.store' => __('Recorded expense'),
            'assets.store' => __('Added church asset'),
            'settings.update' => __('Updated system settings'),
            'settings.users.store' => __('Created user account'),
            'settings.sessions.destroy' => __('Revoked user session'),
            'requests.update' => __('Updated service request'),
            'member.profile.update' => __('Updated personal profile'),
            'member.password.update' => __('Changed account password'),
            'member.requests.store' => __('Submitted service request'),
            'announcements.store' => __('Published announcement'),
            'announcements.update' => __('Updated announcement'),
            'announcements.destroy' => __('Deleted announcement'),
            default => null,
        };
    }
}

<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Member;
use App\Models\MemberRegistrationRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /** @return array{count: int, items: array<int, array<string, mixed>>} */
    public function forUser(User $user): array
    {
        $items = collect();

        if ($user->canManageServiceRequests() || $user->canManageMemberRegistrations()) {
            $items = $items->merge($this->staffNotifications($user));
        }

        if ($user->member_id && $user->canSubmitOwnServiceRequests()) {
            $items = $items->merge($this->memberNotifications($user));
        }

        $items = $items->sortByDesc(fn ($item) => $item['time']->timestamp)->values();

        return [
            'count' => $items->count(),
            'items' => $items->take(8)->all(),
        ];
    }

    public function notifyStaffOfRegistrationRequest(MemberRegistrationRequest $registrationRequest): void
    {
        // Notifications appear in the header bell for admin/pastor on next page load.
    }

    protected function staffNotifications(User $user): Collection
    {
        $items = collect();

        if ($user->canManageMemberRegistrations()) {
            MemberRegistrationRequest::where('status', 'pending')
                ->latest()
                ->take(5)
                ->get()
                ->each(function (MemberRegistrationRequest $registrationRequest) use ($items) {
                    $items->push([
                        'icon' => 'user-plus',
                        'icon_color' => 'warning',
                        'message' => __('New member registration: :name', ['name' => $registrationRequest->applicant_name]),
                        'meta' => __('Awaiting verification'),
                        'url' => route('member-registrations.show', $registrationRequest),
                        'time' => $registrationRequest->created_at,
                    ]);
                });
        }

        if (! $user->canManageServiceRequests()) {
            return $items;
        }

        ServiceRequest::with('member')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->each(function (ServiceRequest $request) use ($items) {
                $items->push([
                    'icon' => 'hand-paper-o',
                    'icon_color' => 'warning',
                    'message' => __('New service request from :name', ['name' => $request->member->name]),
                    'meta' => $request->subject,
                    'url' => route('requests.edit', $request),
                    'time' => $request->created_at,
                ]);
            });

        Event::whereBetween('start_at', [now()->startOfDay(), now()->endOfDay()])
            ->orderBy('start_at')
            ->take(3)
            ->get()
            ->each(function (Event $event) use ($items) {
                $items->push([
                    'icon' => 'calendar',
                    'icon_color' => 'primary',
                    'message' => __('Service today: :title', ['title' => $event->title]),
                    'meta' => $event->start_at->format('H:i'),
                    'url' => route('calendar.index'),
                    'time' => $event->start_at,
                ]);
            });

        $newMembers = Member::whereNull('parent_id')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($newMembers > 0) {
            $items->push([
                'icon' => 'user-plus',
                'icon_color' => 'success',
                'message' => __(':count new members this week', ['count' => $newMembers]),
                'meta' => __('View members'),
                'url' => route('members.index'),
                'time' => now(),
            ]);
        }

        return $items;
    }

    protected function memberNotifications(User $user): Collection
    {
        $items = collect();

        ServiceRequest::where('member_id', $user->member_id)
            ->where('updated_at', '>=', now()->subDays(14))
            ->where(function ($query) {
                $query->where('status', '!=', 'pending')
                    ->orWhereNotNull('admin_notes');
            })
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->each(function (ServiceRequest $request) use ($items, $user) {
                $message = filled($request->admin_notes)
                    ? __('Church office replied to your request: :subject', ['subject' => $request->subject])
                    : __('Your request was updated: :subject', ['subject' => $request->subject]);

                $meta = filled($request->admin_notes)
                    ? \Illuminate\Support\Str::limit($request->admin_notes, 80)
                    : ServiceRequest::statusLabel($request->status);

                $items->push([
                    'icon' => filled($request->admin_notes) ? 'comment' : 'check-circle',
                    'icon_color' => $request->status === 'completed' ? 'success' : (filled($request->admin_notes) ? 'primary' : 'info'),
                    'message' => $message,
                    'meta' => $meta,
                    'url' => route($user->memberPortalRouteName(), ['tab' => 'requests']),
                    'time' => $request->updated_at,
                ]);
            });

        return $items;
    }
}

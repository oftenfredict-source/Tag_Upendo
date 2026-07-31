<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Member;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->isMember()) {
            return app(MemberPortalController::class)->index(request());
        }

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $stats = [
            'members' => Member::whereNull('parent_id')->count(),
            'departments' => \App\Models\Department::count(),
            'leaders' => Member::whereNull('parent_id')->whereHas('leadershipRoles')->count(),
            'follow_ups' => \App\Models\FollowUp::count(),
            'new_members_month' => Member::whereNull('parent_id')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'offerings_month' => (float) \App\Models\Offering::whereBetween('collection_date', [$monthStart, $monthEnd])->sum('amount'),
            'expenses_month' => (float) \App\Models\Expense::whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount'),
            'upcoming_events' => Event::where('start_at', '>=', now())->count(),
        ];

        $recentMembers = Member::with('department')
            ->whereNull('parent_id')
            ->latest()
            ->take(6)
            ->get();

        $recentFollowUps = \App\Models\FollowUp::with('member')
            ->latest()
            ->take(6)
            ->get();

        $upcomingEvents = Event::with('leaderMember')
            ->where('start_at', '>=', now()->startOfDay())
            ->orderBy('start_at')
            ->take(5)
            ->get();

        $monthLabel = Carbon::now()->translatedFormat('F Y');
        $announcements = Announcement::feedFor(auth()->user(), 5);

        return view('dashboard', compact(
            'stats',
            'recentMembers',
            'recentFollowUps',
            'upcomingEvents',
            'monthLabel',
            'announcements'
        ));
    }
}

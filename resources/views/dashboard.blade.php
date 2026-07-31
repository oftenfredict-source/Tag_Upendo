@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-dashboard"></i> {{ __('Dashboard') }}</h1>
            <p>{{ __('Welcome back') }}, {{ auth()->user()->name ?? 'Administrator' }} — {{ $appChurchName ?? 'TAG Upendo' }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">{{ __('Dashboard') }}</li>
        </ul>
    </div>

    @include('partials.announcements-feed')

    {{-- Primary stats --}}
    <div class="row mb-3">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('members.index') }}" class="dash-stat-link">
                <div class="widget-small primary coloured-icon">
                    <i class="icon fa fa-users fa-3x"></i>
                    <div class="info">
                        <h4>{{ __('Members') }}</h4>
                        <p><b>{{ number_format($stats['members']) }}</b></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('church-leaders.index') }}" class="dash-stat-link">
                <div class="widget-small info coloured-icon">
                    <i class="icon fa fa-id-badge fa-3x"></i>
                    <div class="info">
                        <h4>{{ __('Leaders') }}</h4>
                        <p><b>{{ number_format($stats['leaders']) }}</b></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('departments.index') }}" class="dash-stat-link">
                <div class="widget-small warning coloured-icon">
                    <i class="icon fa fa-building fa-3x"></i>
                    <div class="info">
                        <h4>{{ __('Departments') }}</h4>
                        <p><b>{{ number_format($stats['departments']) }}</b></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ url('/bulk-sms/create') }}" class="dash-stat-link">
                <div class="widget-small danger coloured-icon">
                    <i class="icon fa fa-envelope fa-3x"></i>
                    <div class="info">
                        <h4>{{ __('Follow Ups') }}</h4>
                        <p><b>{{ number_format($stats['follow_ups']) }}</b></p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- This month snapshot --}}
    <div class="tile mb-4">
        <div class="tile-title-w-btn">
            <h3 class="title mb-0"><i class="fa fa-calendar"></i> {{ __('This month') }} — {{ $monthLabel }}</h3>
            <p class="mb-0">
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-bar-chart"></i> {{ __('Monthly Report') }}
                </a>
            </p>
        </div>
        <div class="tile-body">
            <div class="row text-center dash-month-row">
                <div class="col-6 col-md-3 mb-3 mb-md-0">
                    <div class="dash-month-stat">
                        <span class="dash-month-value text-primary">{{ number_format($stats['new_members_month']) }}</span>
                        <span class="dash-month-label">{{ __('New members') }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3 mb-md-0">
                    <div class="dash-month-stat">
                        <span class="dash-month-value text-success">TSH {{ number_format($stats['offerings_month'], 0) }}</span>
                        <span class="dash-month-label">{{ __('Offerings') }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="dash-month-stat">
                        <span class="dash-month-value text-danger">TSH {{ number_format($stats['expenses_month'], 0) }}</span>
                        <span class="dash-month-label">{{ __('Expenses') }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="dash-month-stat">
                        <span class="dash-month-value text-info">{{ number_format($stats['upcoming_events']) }}</span>
                        <span class="dash-month-label">{{ __('Upcoming events') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="row mb-4">
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ route('members.create') }}" class="dash-action">
                <i class="fa fa-user-plus"></i>
                <span>{{ __('Add Member') }}</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ route('church-leaders.index') }}" class="dash-action">
                <i class="fa fa-id-badge"></i>
                <span>{{ __('Church Leaders') }}</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ route('calendar.index') }}" class="dash-action">
                <i class="fa fa-calendar"></i>
                <span>{{ __('Calendar') }}</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ route('attendance.index') }}" class="dash-action">
                <i class="fa fa-check-square-o"></i>
                <span>{{ __('Attendance') }}</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ route('offerings.index') }}" class="dash-action">
                <i class="fa fa-money"></i>
                <span>{{ __('Offerings') }}</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ url('/bulk-sms/create') }}" class="dash-action">
                <i class="fa fa-paper-plane"></i>
                <span>{{ __('Send Bulk SMS') }}</span>
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Recent members --}}
        <div class="col-lg-4 mb-4">
            <div class="tile h-100">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">{{ __('Recently Added Members') }}</h3>
                    <p class="mb-0">
                        <a href="{{ route('members.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
                    </p>
                </div>
                <div class="tile-body">
                    @forelse($recentMembers as $member)
                        <div class="dash-list-item">
                            <img class="dash-avatar"
                                src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=940000&color=fff&size=64"
                                alt="">
                            <div class="dash-list-info">
                                <a href="{{ route('members.show', $member) }}" class="dash-list-name">{{ $member->name }}</a>
                                <span class="dash-list-meta">{{ $member->department->name ?? __('No department') }}</span>
                            </div>
                            <span class="dash-list-time">{{ $member->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">{{ __('No members found.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Upcoming events --}}
        <div class="col-lg-4 mb-4">
            <div class="tile h-100">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">{{ __('Upcoming events') }}</h3>
                    <p class="mb-0">
                        <a href="{{ route('calendar.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Calendar') }}</a>
                    </p>
                </div>
                <div class="tile-body">
                    @forelse($upcomingEvents as $event)
                        <div class="dash-event-item">
                            <div class="dash-event-date" style="background: {{ $event->color() }}">
                                <span class="dash-event-day">{{ $event->start_at->format('d') }}</span>
                                <span class="dash-event-month">{{ $event->start_at->format('M') }}</span>
                            </div>
                            <div class="dash-list-info">
                                <span class="dash-list-name">{{ $event->title }}</span>
                                <span class="dash-list-meta">
                                    {{ $event->start_at->format('H:i') }}
                                    @if($event->leaderMember)
                                        · {{ $event->leaderMember->name }}
                                    @elseif($event->leader)
                                        · {{ $event->leader }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">{{ __('No upcoming events.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent SMS --}}
        <div class="col-lg-4 mb-4">
            <div class="tile h-100">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">{{ __('Recent SMS Activity') }}</h3>
                    <p class="mb-0">
                        <a href="{{ route('settings.index', ['tab' => 'logs']) }}" class="btn btn-sm btn-outline-primary">{{ __('System Logs') }}</a>
                    </p>
                </div>
                <div class="tile-body">
                    @forelse($recentFollowUps as $log)
                        <div class="dash-list-item">
                            <div class="dash-sms-icon">
                                <i class="fa fa-envelope"></i>
                            </div>
                            <div class="dash-list-info">
                                <span class="dash-list-name">{{ $log->member->name ?? '—' }}</span>
                                <span class="dash-list-meta">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            @if($log->status == 'sent')
                                <span class="badge badge-success">{{ __('Sent') }}</span>
                            @elseif($log->status == 'pending')
                                <span class="badge badge-warning">{{ __('Pending') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ $log->status }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">{{ __('No SMS activity yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .dash-stat-link { display: block; text-decoration: none; color: inherit; }
    .dash-stat-link:hover .widget-small { box-shadow: 0 4px 12px rgba(0,0,0,.12); transform: translateY(-1px); }
    .dash-stat-link .widget-small { transition: box-shadow .2s, transform .2s; }

    .dash-month-stat {
        padding: 8px 4px;
    }
    .dash-month-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.3;
    }
    .dash-month-label {
        display: block;
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-top: 4px;
    }

    .dash-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 18px 10px;
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        text-decoration: none;
        color: #2c3e50;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        transition: box-shadow .2s, border-color .2s, color .2s;
        height: 100%;
        text-align: center;
        min-height: 96px;
    }
    .dash-action i {
        font-size: 22px;
        color: #940000;
    }
    .dash-action span {
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
    }
    .dash-action:hover {
        border-color: #940000;
        color: #700000;
        box-shadow: 0 4px 12px rgba(0,150,136,.15);
        text-decoration: none;
    }

    .dash-list-item,
    .dash-event-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .dash-list-item:last-child,
    .dash-event-item:last-child { border-bottom: 0; }

    .dash-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .dash-sms-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f5e6e6;
        color: #940000;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .dash-list-info { flex: 1; min-width: 0; }
    .dash-list-name {
        display: block;
        font-weight: 600;
        color: #2c3e50;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    a.dash-list-name:hover { color: #940000; text-decoration: none; }
    .dash-list-meta {
        display: block;
        font-size: 12px;
        color: #6c757d;
    }
    .dash-list-time {
        font-size: 11px;
        color: #95a5a6;
        white-space: nowrap;
    }

    .dash-event-date {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        color: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        line-height: 1.1;
    }
    .dash-event-day { font-size: 16px; font-weight: 700; }
    .dash-event-month { font-size: 10px; text-transform: uppercase; opacity: .9; }
</style>
@endpush

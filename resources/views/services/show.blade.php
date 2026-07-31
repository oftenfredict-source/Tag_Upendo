@extends('layouts.app')

@section('title', __('Church Service'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-institution"></i> {{ $service->theme ?: __('Church Service') }}</h1>
            <p>{{ $service->start_at->format('l, d F Y') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('services.index') }}">{{ __('Church Services') }}</a></li>
            <li class="breadcrumb-item">{{ __('View') }}</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-warning alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> {{ __('Back') }}
        </a>
        @if(auth()->user()->isFullStaff())
        <a href="{{ route('services.edit', $service) }}" class="btn btn-warning btn-sm">
            <i class="fa fa-pencil"></i> {{ __('Edit') }}
        </a>
        @endif
        <a href="{{ route('calendar.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-calendar"></i> {{ __('Calendar') }}
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="tile mb-4">
                <h3 class="tile-title">{{ __('Service details') }}</h3>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th width="30%">{{ __('Theme') }}</th>
                                    <td>{{ $service->theme ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <td>{{ $service->start_at->format('l, d F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Status') }}</th>
                                    <td>
                                        @php $groupStatus = \App\Models\Event::groupComputedStatus($sessions); @endphp
                                        <span class="badge badge-{{ \App\Models\Event::statusBadge($groupStatus) }}">
                                            {{ \App\Models\Event::statusLabel($groupStatus) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Preacher') }}</th>
                                    <td>{{ $service->preacherMember->name ?? $service->leaderMember->name ?? $service->leader ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Coordinator') }}</th>
                                    <td>{{ $service->coordinatorMember->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Church Elder') }}</th>
                                    <td>{{ $service->elderMember->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Venue') }}</th>
                                    <td>{{ $service->location ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Choir') }}</th>
                                    <td>{{ $service->choir ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Registered Members') }}</th>
                                    <td>{{ $service->registered_members_count ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Guests') }}</th>
                                    <td>{{ $service->guests_count ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tile mb-4">
                <h3 class="tile-title">{{ __('Sessions') }} ({{ __('First') }} / {{ __('Second') }})</h3>
                <div class="tile-body">
                    <div class="row">
                        @forelse($sessions as $session)
                            <div class="col-md-6 mb-3">
                                <div class="session-detail-card">
                                    <div class="session-detail-header {{ str_contains($session->service_type, 'Second') ? 'second' : 'first' }}">
                                        {{ str_contains($session->service_type, 'Second') ? __('Second Service') : (str_contains($session->service_type, 'First') ? __('First Service') : $session->service_type) }}
                                    </div>
                                    <div class="session-detail-body">
                                        <p class="mb-2">
                                            <i class="fa fa-clock-o"></i>
                                            <strong>{{ $session->start_at->format('H:i') }}</strong>
                                            @if($session->end_at)
                                                – <strong>{{ $session->end_at->format('H:i') }}</strong>
                                            @endif
                                        </p>
                                        <p class="mb-2">
                                            <span class="badge badge-{{ \App\Models\Event::statusBadge($session->computedStatus()) }}">
                                                {{ \App\Models\Event::statusLabel($session->computedStatus()) }}
                                            </span>
                                        </p>
                                        @php $canAttend = $session->canRecordAttendance(); @endphp
                                        @if($canAttend)
                                            @if($session->church_service_id)
                                                <a href="{{ route('attendance.show', $session->church_service_id) }}" class="btn btn-sm btn-success">
                                                    <i class="fa fa-check-square-o"></i> {{ __('Attendance') }}
                                                </a>
                                                <a href="{{ route('attendance.collect', $session->church_service_id) }}" class="btn btn-sm btn-outline-success">
                                                    <i class="fa fa-pencil"></i> {{ __('Edit') }}
                                                </a>
                                            @else
                                                <a href="{{ route('calendar.events.attendance', $session) }}" class="btn btn-sm btn-outline-success">
                                                    <i class="fa fa-check-square-o"></i> {{ __('Record Attendance') }}
                                                </a>
                                            @endif
                                        @else
                                            <button type="button" class="btn btn-sm btn-secondary" disabled
                                                title="{{ __('Attendance can only be recorded during or after the service.') }}">
                                                <i class="fa fa-lock"></i> {{ __('Record Attendance') }}
                                            </button>
                                            <br><small class="text-muted">{{ __('Available when service starts') }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted mb-0">—</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="tile mb-4">
                <h3 class="tile-title">{{ __('Scripture Readings') }}</h3>
                <div class="tile-body">
                    @if($service->scripture_readings)
                        <div class="service-text-block">{!! nl2br(e($service->scripture_readings)) !!}</div>
                    @else
                        <p class="text-muted mb-0">—</p>
                    @endif
                </div>
            </div>

            <div class="tile mb-4">
                <h3 class="tile-title">{{ __('Announcements') }}</h3>
                <div class="tile-body">
                    @if($service->announcements)
                        <div class="service-text-block">{!! nl2br(e($service->announcements)) !!}</div>
                    @else
                        <p class="text-muted mb-0">—</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .service-text-block {
        white-space: pre-wrap;
        line-height: 1.5;
        color: #2c3e50;
    }
    .session-detail-card {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        overflow: hidden;
        height: 100%;
    }
    .session-detail-header {
        padding: 10px 14px;
        color: #fff;
        font-weight: 600;
    }
    .session-detail-header.first { background: #940000; }
    .session-detail-header.second { background: #3f51b5; }
    .session-detail-body { padding: 14px; }
</style>
@endpush

@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-bar-chart"></i> Ripoti ya Mahudhurio</h1>
            <p>{{ $service->displayName() }} — {{ $service->service_date->format('d/m/Y') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item">Report</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-md-12">
            @if($service->canRecordAttendance())
                <a href="{{ route('attendance.collect', $service) }}" class="btn btn-primary">
                    <i class="fa fa-pencil"></i> Badilisha Mahudhurio
                </a>
            @else
                <button type="button" class="btn btn-secondary" disabled title="{{ __('Attendance can only be recorded during or after the service.') }}">
                    <i class="fa fa-lock"></i> Badilisha Mahudhurio
                </button>
            @endif
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Rudi
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-check fa-3x"></i>
                <div class="info">
                    <h4>Waliohudhuria</h4>
                    <p><b>{{ $service->attendances_count }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-times fa-3x"></i>
                <div class="info">
                    <h4>Hawakuja</h4>
                    <p><b>{{ $absentCount }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-percent fa-3x"></i>
                <div class="info">
                    <h4>Kiwango</h4>
                    <p><b>{{ $totalMembers > 0 ? round(($service->attendances_count / $totalMembers) * 100) : 0 }}%</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title">Waliohudhuria ({{ $presentMembers->count() }})</h3>
                <div class="tile-body">
                    @if($presentMembers->count())
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Jina</th>
                                        <th>Simu</th>
                                        <th>Idara</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($presentMembers as $member)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <a href="{{ route('members.show', $member) }}">{{ $member->name }}</a>
                                            </td>
                                            <td>{{ $member->phone_number }}</td>
                                            <td>{{ $member->department?->name ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Hakuna mahudhurio yaliyorekodiwa bado.
                            <a href="{{ route('attendance.collect', $service) }}">Weka sasa</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($service->notes)
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Maelezo</h3>
                    <div class="tile-body">{{ $service->notes }}</div>
                </div>
            </div>
        </div>
    @endif
@endsection

@extends('layouts.app')

@section('title', __('Service requests'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-inbox"></i> {{ __('Service requests') }}</h1>
            <p>{{ __('Member service requests — prayer, pastoral visits, counseling, and more.') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">{{ __('Service requests') }}</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-6 col-lg-3">
            <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-clock-o fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Pending') }}</h4>
                    <p><b>{{ number_format($stats['pending']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-spinner fa-3x"></i>
                <div class="info">
                    <h4>{{ __('In progress') }}</h4>
                    <p><b>{{ number_format($stats['in_progress']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-check fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Completed') }}</h4>
                    <p><b>{{ number_format($stats['completed']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-inbox fa-3x"></i>
                <div class="info">
                    <h4>{{ __('All requests') }}</h4>
                    <p><b>{{ number_format($stats['total']) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="tile">
        <div class="tile-body">
            <div class="mb-3">
                <a href="{{ route('requests.index') }}"
                    class="btn btn-sm {{ empty($status) ? 'btn-primary' : 'btn-outline-secondary' }}">{{ __('All') }}</a>
                <a href="{{ route('requests.index', ['status' => 'pending']) }}"
                    class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }}">{{ __('Pending') }}</a>
                <a href="{{ route('requests.index', ['status' => 'in_progress']) }}"
                    class="btn btn-sm {{ $status === 'in_progress' ? 'btn-info' : 'btn-outline-secondary' }}">{{ __('In progress') }}</a>
                <a href="{{ route('requests.index', ['status' => 'completed']) }}"
                    class="btn btn-sm {{ $status === 'completed' ? 'btn-success' : 'btn-outline-secondary' }}">{{ __('Completed') }}</a>
                <a href="{{ route('requests.index', ['status' => 'cancelled']) }}"
                    class="btn btn-sm {{ $status === 'cancelled' ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ __('Cancelled') }}</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('Member') }}</th>
                            <th>{{ __('Request type') }}</th>
                            <th>{{ __('Subject') }}</th>
                            <th>{{ __('Preferred date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Submitted') }}</th>
                            <th width="100">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr class="{{ $req->status === 'pending' ? 'table-warning' : '' }}">
                                <td>
                                    <a href="{{ route('members.show', $req->member) }}">{{ $req->member->name }}</a>
                                    @if($req->member->phone_number)
                                        <br><small class="text-muted"><i class="fa fa-phone"></i> {{ $req->member->phone_number }}</small>
                                    @endif
                                </td>
                                <td>{{ $req->typeLabel() }}</td>
                                <td>{{ $req->subject }}</td>
                                <td>{{ $req->preferred_date?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ \App\Models\ServiceRequest::statusBadge($req->status) }}">
                                        {{ \App\Models\ServiceRequest::statusLabel($req->status) }}
                                    </span>
                                </td>
                                <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('requests.edit', $req) }}" class="btn btn-warning btn-sm">
                                        <i class="fa fa-pencil"></i> {{ __('Edit') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                    {{ __('No member requests yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $requests->links() }}
        </div>
    </div>
@endsection

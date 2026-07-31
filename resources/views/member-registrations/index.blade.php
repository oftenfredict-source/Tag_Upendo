@extends('layouts.app')

@section('title', __('Member registrations'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-user-plus"></i> {{ __('Member registrations') }}</h1>
            <p>{{ __('Review and approve self-registration requests from the public link.') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">{{ __('Members') }}</a></li>
            <li class="breadcrumb-item">{{ __('Registrations') }}</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-clock-o fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Pending') }}</h4>
                    <p><b>{{ number_format($counts['pending']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-check fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Approved') }}</h4>
                    <p><b>{{ number_format($counts['approved']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-times fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Rejected') }}</h4>
                    <p><b>{{ number_format($counts['rejected']) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="tile">
        <div class="tile-body">
            <div class="mb-3">
                <a href="{{ route('member-registrations.index', ['status' => 'pending']) }}"
                    class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }}">{{ __('Pending') }}</a>
                <a href="{{ route('member-registrations.index', ['status' => 'approved']) }}"
                    class="btn btn-sm {{ $status === 'approved' ? 'btn-success' : 'btn-outline-secondary' }}">{{ __('Approved') }}</a>
                <a href="{{ route('member-registrations.index', ['status' => 'rejected']) }}"
                    class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-secondary' }}">{{ __('Rejected') }}</a>
                <a href="{{ route('member-registrations.index', ['status' => 'all']) }}"
                    class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">{{ __('All') }}</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Submitted') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $item)
                            <tr>
                                <td>{{ $item->applicant_name }}</td>
                                <td>{{ $item->applicant_phone ?: '—' }}</td>
                                <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-{{ \App\Models\MemberRegistrationRequest::statusBadge($item->status) }}">
                                        {{ \App\Models\MemberRegistrationRequest::statusLabel($item->status) }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('member-registrations.show', $item) }}" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i> {{ __('Review') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('No registration requests found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $requests->links() }}
        </div>
    </div>
@endsection

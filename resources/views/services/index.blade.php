@extends('layouts.app')

@section('title', __('Church Services'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-institution"></i> {{ __('Church Services') }}</h1>
            <p>{{ __('View all created church services') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('calendar.index') }}">{{ __('Calendar') }}</a></li>
            <li class="breadcrumb-item">{{ __('Church Services') }}</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-6 col-lg-3">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-institution fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Total Services') }}</h4>
                    <p><b>{{ number_format($stats['total']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-calendar fa-3x"></i>
                <div class="info">
                    <h4>{{ __('This month') }}</h4>
                    <p><b>{{ number_format($stats['this_month']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-clock-o fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Upcoming') }}</h4>
                    <p><b>{{ number_format($stats['upcoming']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-exchange fa-3x"></i>
                <div class="info">
                    <h4>{{ __('With 2 sessions') }}</h4>
                    <p><b>{{ number_format($stats['with_two']) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="tile">
        <div class="tile-title-w-btn">
            <h3 class="title mb-0">{{ __('All Services') }}</h3>
            <p class="mb-0">
                <a href="{{ route('calendar.index', ['open' => 1]) }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus"></i> {{ __('Create Church Service') }}
                </a>
            </p>
        </div>
        <div class="tile-body">
            <form method="GET" action="{{ route('services.index') }}" class="services-filter mb-4">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="control-label">{{ __('Search') }}</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                placeholder="{{ __('Theme, type, venue, preacher...') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="control-label">{{ __('From') }}</label>
                            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="control-label">{{ __('To') }}</label>
                            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="form-group mb-2 w-100">
                            <button type="submit" class="btn btn-primary btn-block" title="{{ __('Filter') }}">
                                <i class="fa fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover services-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Theme') }}</th>
                            <th>{{ __('Sessions') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Preacher') }}</th>
                            <th>{{ __('Venue') }}</th>
                            <th width="140">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serviceGroups as $i => $group)
                            <tr>
                                <td>{{ $serviceGroups->firstItem() + $i }}</td>
                                <td>
                                    <strong>{{ $group->date?->format('d/m/Y') }}</strong>
                                    <br><small class="text-muted">{{ $group->date?->format('l') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('services.show', $group->primary) }}">
                                        <strong>{{ $group->theme ?: __('Church Service') }}</strong>
                                    </a>
                                </td>
                                <td>
                                    @foreach($group->sessions as $session)
                                        <span class="badge badge-{{ str_contains($session->service_type, 'First') ? 'success' : (str_contains($session->service_type, 'Second') ? 'info' : 'secondary') }} mr-1 mb-1">
                                            {{ str_contains($session->service_type, 'First') ? __('First') : (str_contains($session->service_type, 'Second') ? __('Second') : $session->service_type) }}
                                            {{ $session->start_at->format('H:i') }}
                                            @if($session->end_at)–{{ $session->end_at->format('H:i') }}@endif
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge badge-{{ \App\Models\Event::statusBadge($group->status) }}">
                                        {{ \App\Models\Event::statusLabel($group->status) }}
                                    </span>
                                </td>
                                <td>{{ $group->preacher ?: '—' }}</td>
                                <td>{{ $group->location ?: '—' }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('services.show', $group->primary) }}" class="btn btn-sm btn-primary" title="{{ __('View') }}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->isFullStaff())
                                    <a href="{{ route('services.edit', $group->primary) }}" class="btn btn-sm btn-warning" title="{{ __('Edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <form action="{{ route('services.destroy', $group->primary) }}" method="POST" class="d-inline"
                                        data-swal-confirm="{{ __('Delete this church service?') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Remove') }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa fa-institution fa-3x d-block mb-3 opacity-25"></i>
                                    {{ __('No church services found.') }}
                                    <br>
                                    <a href="{{ route('calendar.index', ['open' => 1]) }}">{{ __('Create Church Service') }}</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $serviceGroups->links() }}
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .services-filter {
        background: #f8f9fa;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 14px 16px;
    }
    .services-table thead th {
        background: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
</style>
@endpush

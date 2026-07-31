@extends('layouts.app')

@section('title', __('Finance Dashboard'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-money"></i> {{ __('Finance Dashboard') }}</h1>
            <p>{{ __('Overview of offerings, tithes, expenses and pledges') }} — {{ $monthLabel }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">{{ __('Finance') }}</li>
            <li class="breadcrumb-item">{{ __('Dashboard') }}</li>
        </ul>
    </div>

    {{-- This month primary stats --}}
    <div class="row mb-3">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('tithes.index') }}" class="fin-stat-link">
                <div class="widget-small primary coloured-icon">
                    <i class="icon fa fa-percent fa-3x"></i>
                    <div class="info">
                        <h4>{{ __('Tithes') }}</h4>
                        <p><b>TSH {{ number_format($stats['tithes_month'], 0) }}</b></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('offerings.index') }}" class="fin-stat-link">
                <div class="widget-small info coloured-icon">
                    <i class="icon fa fa-heart fa-3x"></i>
                    <div class="info">
                        <h4>{{ __('Offerings') }}</h4>
                        <p><b>TSH {{ number_format($stats['offerings_month'], 0) }}</b></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('expenses.index') }}" class="fin-stat-link">
                <div class="widget-small danger coloured-icon">
                    <i class="icon fa fa-credit-card fa-3x"></i>
                    <div class="info">
                        <h4>{{ __('Expenses') }}</h4>
                        <p><b>TSH {{ number_format($stats['expenses_month'], 0) }}</b></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small {{ $stats['net_month'] >= 0 ? 'primary' : 'warning' }} coloured-icon">
                <i class="icon fa fa-balance-scale fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Net this month') }}</h4>
                    <p><b>TSH {{ number_format($stats['net_month'], 0) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Income snapshot + pledges --}}
    <div class="row mb-3">
        <div class="col-lg-8 mb-3 mb-lg-0">
            <div class="tile h-100">
                <h3 class="tile-title"><i class="fa fa-calendar"></i> {{ __('This month') }} — {{ $monthLabel }}</h3>
                <div class="tile-body">
                    <div class="row text-center">
                        <div class="col-6 col-md-4 mb-3">
                            <div class="fin-month-stat">
                                <span class="fin-month-value text-success">TSH {{ number_format($stats['income_month'], 0) }}</span>
                                <span class="fin-month-label">{{ __('Income') }} ({{ __('Tithes') }} + {{ __('Offerings') }})</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 mb-3">
                            <div class="fin-month-stat">
                                <span class="fin-month-value text-danger">TSH {{ number_format($stats['expenses_month'], 0) }}</span>
                                <span class="fin-month-label">{{ __('Expenses') }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <div class="fin-month-stat">
                                <span class="fin-month-value {{ $stats['net_month'] >= 0 ? 'text-primary' : 'text-warning' }}">
                                    TSH {{ number_format($stats['net_month'], 0) }}
                                </span>
                                <span class="fin-month-label">{{ __('Balance') }}</span>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="text-muted mb-2">{{ __('Offerings by category') }}</h6>
                            @if($offeringsByCategory->isEmpty())
                                <p class="text-muted mb-0">{{ __('No offerings recorded this month.') }}</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <tbody>
                                            @foreach($offeringsByCategory as $row)
                                                <tr>
                                                    <td>{{ $row->category ?: __('Other') }}</td>
                                                    <td class="text-right"><strong>TSH {{ number_format($row->total, 0) }}</strong></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">{{ __('Expenses by category') }}</h6>
                            @if($expensesByCategory->isEmpty())
                                <p class="text-muted mb-0">{{ __('No expenses recorded this month.') }}</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <tbody>
                                            @foreach($expensesByCategory as $row)
                                                <tr>
                                                    <td>{{ $row->category ?: __('Other') }}</td>
                                                    <td class="text-right text-danger"><strong>TSH {{ number_format($row->total, 0) }}</strong></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="tile mb-3">
                <h3 class="tile-title"><i class="fa fa-handshake-o"></i> {{ __('Pledges') }}</h3>
                <div class="tile-body">
                    <p class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">{{ __('Total pledged') }}</span>
                        <strong>TSH {{ number_format($stats['pledged_total'], 0) }}</strong>
                    </p>
                    <p class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">{{ __('Paid') }}</span>
                        <strong class="text-success">TSH {{ number_format($stats['pledged_paid'], 0) }}</strong>
                    </p>
                    <p class="mb-3 d-flex justify-content-between">
                        <span class="text-muted">{{ __('Remaining') }}</span>
                        <strong class="text-danger">TSH {{ number_format(max(0, $stats['pledged_remaining']), 0) }}</strong>
                    </p>
                    <a href="{{ route('pledges.index') }}" class="btn btn-sm btn-outline-primary btn-block">
                        {{ __('View pledges') }}
                    </a>
                </div>
            </div>

            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-database"></i> {{ __('All time') }}</h3>
                <div class="tile-body">
                    <p class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">{{ __('Tithes') }}</span>
                        <strong>TSH {{ number_format($stats['tithes_all'], 0) }}</strong>
                    </p>
                    <p class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">{{ __('Offerings') }}</span>
                        <strong>TSH {{ number_format($stats['offerings_all'], 0) }}</strong>
                    </p>
                    <p class="mb-0 d-flex justify-content-between">
                        <span class="text-muted">{{ __('Expenses') }}</span>
                        <strong class="text-danger">TSH {{ number_format($stats['expenses_all'], 0) }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="tile mb-4">
        <h3 class="tile-title">{{ __('Quick actions') }}</h3>
        <div class="tile-body">
            <a href="{{ route('tithes.index') }}" class="btn btn-primary btn-sm mr-2 mb-2">
                <i class="fa fa-plus"></i> {{ __('Record Tithe') }}
            </a>
            <a href="{{ route('offerings.index') }}" class="btn btn-info btn-sm mr-2 mb-2">
                <i class="fa fa-plus"></i> {{ __('Record offering') }}
            </a>
            <a href="{{ route('expenses.index') }}" class="btn btn-danger btn-sm mr-2 mb-2">
                <i class="fa fa-plus"></i> {{ __('Record expense') }}
            </a>
            <a href="{{ route('pledges.index') }}" class="btn btn-warning btn-sm mb-2">
                <i class="fa fa-plus"></i> {{ __('Add pledge') }}
            </a>
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="tile h-100">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">{{ __('Recent tithes') }}</h3>
                    <a href="{{ route('tithes.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
                </div>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Member') }}</th>
                                    <th class="text-right">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTithes as $tithe)
                                    <tr>
                                        <td>{{ $tithe->payment_date->format('d/m/Y') }}</td>
                                        <td>{{ $tithe->displayName() }}</td>
                                        <td class="text-right text-success">TSH {{ number_format($tithe->amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">{{ __('No tithe records yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="tile h-100">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">{{ __('Recent offerings') }}</h3>
                    <a href="{{ route('offerings.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
                </div>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th class="text-right">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOfferings as $offering)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($offering->collection_date)->format('d/m/Y') }}</td>
                                        <td>{{ $offering->category }}</td>
                                        <td class="text-right">TSH {{ number_format($offering->amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">{{ __('No offerings yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="tile h-100">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">{{ __('Recent expenses') }}</h3>
                    <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
                </div>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th class="text-right">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentExpenses as $expense)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                                        <td>{{ $expense->category }}</td>
                                        <td class="text-right text-danger">TSH {{ number_format($expense->amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">{{ __('No expenses yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="tile h-100">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">{{ __('Open pledges') }}</h3>
                    <a href="{{ route('pledges.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
                </div>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Remaining') }}</th>
                                    <th>{{ __('Due') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($openPledges as $pledge)
                                    <tr>
                                        <td>
                                            <strong>{{ $pledge->displayName() }}</strong>
                                            <br><small class="text-muted">{{ $pledge->pledge_for }}</small>
                                        </td>
                                        <td class="text-danger"><strong>TSH {{ number_format($pledge->remainingAmount(), 0) }}</strong></td>
                                        <td>{{ $pledge->due_date?->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">{{ __('No open pledges.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .fin-stat-link { display: block; text-decoration: none; color: inherit; }
    .fin-stat-link:hover { text-decoration: none; color: inherit; }
    .fin-stat-link .widget-small { transition: transform .15s ease, box-shadow .15s ease; }
    .fin-stat-link:hover .widget-small {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
    }
    .fin-month-stat { padding: 8px 4px; }
    .fin-month-value { display: block; font-size: 1.25rem; font-weight: 700; }
    .fin-month-label { display: block; font-size: .8rem; color: #6c757d; margin-top: 4px; }
</style>
@endpush

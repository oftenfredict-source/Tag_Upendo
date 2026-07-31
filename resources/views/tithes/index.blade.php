@extends('layouts.app')

@section('title', __('Tithes'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-percent"></i> {{ __('Tithes') }} ({{ __('Zaka') }})</h1>
            <p>{{ __('Record tithes collected from each member') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">{{ __('Finance') }}</li>
            <li class="breadcrumb-item">{{ __('Tithes') }}</li>
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
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-calendar fa-3x"></i>
                <div class="info">
                    <h4>{{ __('This month') }}</h4>
                    <p><b>TSH {{ number_format($stats['this_month'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-money fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Total collected') }}</h4>
                    <p><b>TSH {{ number_format($stats['total'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-list fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Records') }}</h4>
                    <p><b>{{ number_format($stats['count']) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-plus"></i> {{ __('Record Tithe') }}</h3>
                <div class="tile-body">
                    <form method="POST" action="{{ route('tithes.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">{{ __('Member') }} <span class="text-danger">*</span></label>
                            <select name="member_id" id="memberSelect" class="form-control" required style="width:100%">
                                <option value="">-- {{ __('Search name') }} --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}>
                                        {{ $m->name }}@if($m->phone_number) — {{ $m->phone_number }}@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Amount') }} (TSH) <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" name="amount" class="form-control" required
                                value="{{ old('amount') }}">
                            @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Payment date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control"
                                value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Notes') }}</label>
                            <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit">
                            <i class="fa fa-save"></i> {{ __('Save Tithe') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">{{ __('Tithe records') }}</h3>
                </div>
                <div class="tile-body">
                    <form method="GET" class="mb-3 p-3 rounded" style="background:#f8f9fa">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="member_id" id="filterMember" class="form-control" style="width:100%">
                                    <option value="">{{ __('All members') }}</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}" {{ request('member_id') == $m->id ? 'selected' : '' }}>
                                            {{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="from" class="form-control" value="{{ request('from') }}" title="{{ __('From') }}">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="to" class="form-control" value="{{ request('to') }}" title="{{ __('To') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block" title="{{ __('Filter') }}">
                                    <i class="fa fa-filter"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Member') }}</th>
                                    <th class="text-right">{{ __('Amount') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th width="70"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tithes as $tithe)
                                    <tr>
                                        <td>{{ $tithe->payment_date->format('d/m/Y') }}</td>
                                        <td><strong>{{ $tithe->displayName() }}</strong></td>
                                        <td class="text-right text-success">
                                            <strong>TSH {{ number_format($tithe->amount, 0) }}</strong>
                                        </td>
                                        <td>{{ $tithe->notes ?: '—' }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('tithes.destroy', $tithe) }}" method="POST" class="d-inline"
                                                data-swal-confirm="{{ __('Delete this tithe record?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Remove') }}">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">{{ __('No tithe records yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-center">{{ $tithes->links() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .select2-container { width: 100% !important; }
    .select2-container--default .select2-selection--single {
        height: 38px; border: 1px solid #ced4da; border-radius: 4px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px; padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    $('#memberSelect, #filterMember').select2({
        placeholder: @json(__('Search member...')),
        allowClear: true,
        width: '100%'
    });
})();
</script>
@endpush

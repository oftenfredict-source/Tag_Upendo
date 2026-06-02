@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-handshake-o"></i> Pledges (Ahadi)</h1>
            <p>Fuatilia ahadi, malipo yaliyofanyika, na kilichobaki</p>
        </div>
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
                <i class="icon fa fa-money fa-3x"></i>
                <div class="info">
                    <h4>Jumla ya Ahadi</h4>
                    <p><b>TSH {{ number_format($stats['total_pledged'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-check fa-3x"></i>
                <div class="info">
                    <h4>Imelipwa</h4>
                    <p><b>TSH {{ number_format($stats['total_paid'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-clock-o fa-3x"></i>
                <div class="info">
                    <h4>Kilichobaki</h4>
                    <p><b>TSH {{ number_format(max(0, $stats['total_remaining']), 0) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-plus"></i> Ongeza Pledge</h3>
                <div class="tile-body">
                    <form method="POST" action="{{ route('pledges.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">Mwanachama <span class="text-danger">*</span></label>
                            <select name="member_id" id="memberSelect" class="form-control" required style="width:100%">
                                <option value="">-- Tafuta jina --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}
                                        data-phone="{{ $m->phone_number }}">
                                        {{ $m->name }}@if($m->phone_number) — {{ $m->phone_number }}@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Ahadi Kwa <span class="text-danger">*</span></label>
                            <input type="text" name="pledge_for" class="form-control" required
                                value="{{ old('pledge_for') }}" placeholder="Ujenzi, viti, programu...">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Kiasi cha Ahadi (TSH) <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" name="amount" class="form-control" required
                                value="{{ old('amount') }}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Malipo ya Awali (si lazima)</label>
                            <input type="number" step="1" min="0" name="amount_paid" class="form-control"
                                value="{{ old('amount_paid', 0) }}" placeholder="0">
                            <small class="text-muted">Ikiwa ameshalipa sehemu tayari</small>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Mwisho wa Kulipa <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control"
                                value="{{ old('due_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Maelezo</label>
                            <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit">
                            <i class="fa fa-save"></i> Hifadhi Pledge
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">Orodha ya Pledges</h3>
                </div>
                <div class="tile-body">
                    <form method="GET" class="mb-3 p-3 rounded" style="background:#f8f9fa">
                        <div class="row">
                            <div class="col-md-5">
                                <select name="member_id" id="filterMember" class="form-control" style="width:100%">
                                    <option value="">Wanachama wote</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}" {{ request('member_id') == $m->id ? 'selected' : '' }}>
                                            {{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-control">
                                    <option value="">Hali zote</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Inasubiri</option>
                                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Sehemu</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Imekamilika</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Chuja</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered pledge-table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Mwanachama</th>
                                    <th>Ahadi</th>
                                    <th class="text-right">Jumla</th>
                                    <th class="text-right">Imelipwa</th>
                                    <th class="text-right">Kilichobaki</th>
                                    <th width="140">Maendeleo</th>
                                    <th width="90"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pledges as $pledge)
                                    <tr>
                                        <td>
                                            <strong>{{ $pledge->displayName() }}</strong>
                                            <br>
                                            <span class="badge badge-{{ \App\Models\Pledge::statusBadge($pledge->status) }}">
                                                {{ \App\Models\Pledge::statusLabel($pledge->status) }}
                                            </span>
                                            <br><small class="text-muted">Mwisho: {{ $pledge->due_date?->format('d/m/Y') }}</small>
                                        </td>
                                        <td>{{ $pledge->pledge_for }}</td>
                                        <td class="text-right">{{ number_format($pledge->amount, 0) }}</td>
                                        <td class="text-right text-success"><strong>{{ number_format($pledge->amount_paid, 0) }}</strong></td>
                                        <td class="text-right text-danger"><strong>{{ number_format($pledge->remainingAmount(), 0) }}</strong></td>
                                        <td>
                                            <div class="progress mb-1" style="height:10px">
                                                <div class="progress-bar bg-success" style="width:{{ $pledge->paidPercent() }}%"></div>
                                            </div>
                                            <small>
                                                <span class="text-success">{{ $pledge->paidPercent() }}%</span> imelipwa ·
                                                <span class="text-danger">{{ $pledge->remainingPercent() }}%</span> bado
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @if($pledge->status !== 'completed')
                                                <button type="button" class="btn btn-sm btn-primary btn-pay"
                                                    data-toggle="modal" data-target="#payModal"
                                                    data-id="{{ $pledge->id }}"
                                                    data-name="{{ $pledge->displayName() }}"
                                                    data-remaining="{{ $pledge->remainingAmount() }}"
                                                    data-action="{{ route('pledges.pay', $pledge) }}">
                                                    <i class="fa fa-money"></i>
                                                </button>
                                            @else
                                                <i class="fa fa-check-circle text-success fa-lg"></i>
                                            @endif
                                            @if($pledge->payments->isNotEmpty())
                                                <button type="button" class="btn btn-sm btn-light btn-history"
                                                    data-target="#history-{{ $pledge->id }}">
                                                    <i class="fa fa-history"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($pledge->payments->isNotEmpty())
                                        <tr id="history-{{ $pledge->id }}" class="pledge-history-row d-none">
                                            <td colspan="7" class="bg-light">
                                                <strong><i class="fa fa-list"></i> Historia ya Malipo:</strong>
                                                <ul class="mb-0 mt-2 pl-3">
                                                    @foreach($pledge->payments as $payment)
                                                        <li>
                                                            <strong>TSH {{ number_format($payment->amount, 0) }}</strong>
                                                            — {{ $payment->payment_date->format('d/m/Y') }}
                                                            @if($payment->notes)
                                                                <em class="text-muted">({{ $payment->notes }})</em>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Hakuna pledge bado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-center">{{ $pledges->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: rekodi malipo --}}
    <div class="modal fade" id="payModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="payForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa fa-money"></i> Rekodi Malipo</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Mwanachama: <strong id="payMemberName"></strong></p>
                        <p class="mb-3 text-danger">Kilichobaki: <strong id="payRemaining"></strong></p>
                        <div class="form-group">
                            <label>Kiasi Kinacholipwa Sasa (TSH) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="payAmount" class="form-control" min="1" step="1" required>
                            <small class="text-muted">Hii ndiyo kiasi kilichopungua sasa</small>
                        </div>
                        <div class="form-group">
                            <label>Tarehe ya Malipo</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group mb-0">
                            <label>Maelezo</label>
                            <input type="text" name="notes" class="form-control" placeholder="Si lazima">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Funga</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Hifadhi Malipo</button>
                    </div>
                </form>
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
    .pledge-table td { vertical-align: middle !important; }
    .pledge-history-row td { font-size: 13px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    $('#memberSelect, #filterMember').select2({
        placeholder: 'Tafuta mwanachama...',
        allowClear: true,
        width: '100%'
    });

    $('.btn-pay').on('click', function () {
        var remaining = parseFloat($(this).data('remaining')) || 0;
        $('#payForm').attr('action', $(this).data('action'));
        $('#payMemberName').text($(this).data('name'));
        $('#payRemaining').text('TSH ' + remaining.toLocaleString());
        $('#payAmount').attr('max', remaining).val('');
    });

    $('.btn-history').on('click', function () {
        $($(this).data('target')).toggleClass('d-none');
    });
})();
</script>
@endpush

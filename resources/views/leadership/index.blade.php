@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-user-circle"></i> Uongozi wa Ibada</h1>
            <p>{{ $monthLabel }} — unda ibada na weka viongozi</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('calendar.index') }}">Calendar</a></li>
            <li class="breadcrumb-item">Uongozi</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Mwezi --}}
    <div class="tile mb-3">
        <div class="tile-body py-3">
            <div class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <a href="{{ route('calendar.index') }}" class="btn btn-secondary">
                        <i class="fa fa-calendar"></i> Kalenda
                    </a>
                </div>
                <div class="col-md-4 text-center mb-2 mb-md-0">
                    <div class="btn-group">
                        <a href="{{ route('leadership.index', ['month' => $prev->month, 'year' => $prev->year]) }}" class="btn btn-outline-primary">
                            <i class="fa fa-chevron-left"></i>
                        </a>
                        <span class="btn btn-primary disabled px-4" style="opacity:1">{{ $monthLabel }}</span>
                        <a href="{{ route('leadership.index', ['month' => $next->month, 'year' => $next->year]) }}" class="btn btn-outline-primary">
                            <i class="fa fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <form method="GET" action="{{ route('leadership.index') }}" class="form-inline justify-content-md-end">
                        <select name="month" class="form-control mr-2 mb-2 mb-md-0">
                            @foreach(\App\Models\Event::monthNames() as $m => $name)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="year" class="form-control mr-2 mb-2 mb-md-0" style="width:88px" value="{{ $year }}">
                        <button type="submit" class="btn btn-primary mb-2 mb-md-0">Nenda</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 col-lg-4">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-calendar fa-3x"></i>
                <div class="info">
                    <h4>Ibada Mwezi Huu</h4>
                    <p><b>{{ $events->count() }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-user fa-3x"></i>
                <div class="info">
                    <h4>Zina Viongozi</h4>
                    <p><b>{{ $events->filter(fn ($e) => $e->leader || $e->leader_member_id)->count() }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="tile leadership-form-tile">
                <h3 class="tile-title"><i class="fa fa-plus text-primary"></i> Unda Ibada</h3>
                <div class="tile-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('leadership.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">

                        <div class="form-group">
                            <label class="control-label">Jina la Ibada <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required value="{{ old('title') }}"
                                placeholder="Bible Study, Prayer...">
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="control-label">Aina <span class="text-danger">*</span></label>
                                    <select name="event_type" class="form-control" required id="createEventType">
                                        @foreach($eventTypes as $key => $type)
                                            <option value="{{ $key }}" {{ old('event_type', 'service') === $key ? 'selected' : '' }}>
                                                {{ $type['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6" id="createServiceTypeWrap">
                                <div class="form-group">
                                    <label class="control-label">Aina ya Ibada</label>
                                    <select name="service_type" class="form-control">
                                        <option value="">--</option>
                                        @foreach($serviceTypes as $st)
                                            <option value="{{ $st }}">{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Tarehe & Muda <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_at" class="form-control" required value="{{ old('start_at') }}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Muda wa Mwisho</label>
                            <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at') }}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Kiongozi (mwanachama)</label>
                            <select name="leader_member_id" class="form-control leader-member-select">
                                <option value="">-- Chagua --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">au jina</label>
                            <input type="text" name="leader" class="form-control leader-text" value="{{ old('leader') }}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Mahali</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-save"></i> Hifadhi Ibada
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="tile">
                <h3 class="tile-title">Ibada za {{ $monthLabel }}</h3>
                <div class="tile-body">
                    @if($events->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa fa-calendar-o fa-4x mb-3 d-block opacity-25"></i>
                            <p class="mb-0">Hakuna ibada mwezi huu.<br>Unda ibada kushoto.</p>
                        </div>
                    @else
                        <form action="{{ route('leadership.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered leadership-table mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="110">Tarehe</th>
                                            <th>Ibada</th>
                                            <th width="240">Kiongozi</th>
                                            <th width="52"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($events as $event)
                                            <tr>
                                                <td class="align-middle">
                                                    <strong>{{ $event->start_at->format('d/m') }}</strong><br>
                                                    <small class="text-muted">{{ $event->start_at->format('H:i') }}</small>
                                                </td>
                                                <td class="align-middle">
                                                    <strong>{{ $event->title }}</strong>
                                                    <br><span class="badge badge-light">{{ $event->typeLabel() }}</span>
                                                </td>
                                                <td>
                                                    <select name="events[{{ $event->id }}][leader_member_id]" class="form-control form-control-sm mb-1 leader-member-select">
                                                        <option value="">Mwanachama</option>
                                                        @foreach($members as $m)
                                                            <option value="{{ $m->id }}" {{ $event->leader_member_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="text" name="events[{{ $event->id }}][leader]" class="form-control form-control-sm leader-text"
                                                        value="{{ $event->leader_member_id ? '' : $event->leader }}" placeholder="au andika jina">
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-event"
                                                        data-url="{{ route('leadership.destroy', $event) }}"
                                                        data-month="{{ $month }}" data-year="{{ $year }}">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-right mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fa fa-save"></i> Hifadhi Viongozi
                                </button>
                            </div>
                        </form>

                        <form id="deleteEventForm" method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="month" id="deleteMonth">
                            <input type="hidden" name="year" id="deleteYear">
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .leadership-form-tile { position: sticky; top: 70px; }
    .leadership-table thead th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .leadership-table td { vertical-align: middle !important; }
    @media (max-width: 991px) {
        .leadership-form-tile { position: static; }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    function toggleServiceType() {
        var el = document.getElementById('createServiceTypeWrap');
        if (!el) return;
        el.style.display = document.getElementById('createEventType').value === 'service' ? '' : 'none';
    }
    var typeSel = document.getElementById('createEventType');
    if (typeSel) {
        typeSel.addEventListener('change', toggleServiceType);
        toggleServiceType();
    }

    document.querySelectorAll('.leader-member-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var text = this.closest('td, .form-group') && this.closest('td, .form-group').querySelector('.leader-text');
            if (text && this.value) text.value = '';
        });
    });

    document.querySelectorAll('.btn-delete-event').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Futa ibada hii?')) return;
            var form = document.getElementById('deleteEventForm');
            form.action = this.getAttribute('data-url');
            document.getElementById('deleteMonth').value = this.getAttribute('data-month');
            document.getElementById('deleteYear').value = this.getAttribute('data-year');
            form.submit();
        });
    });
})();
</script>
@endpush

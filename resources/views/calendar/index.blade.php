@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-calendar"></i> Kalenda — Ibada & Matukio</h1>
            <p>Unda ibada na matukio wewe mwenyewe, kisha angalia mahudhurio yaliyorekodi</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">Calendar</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" id="btnAddEvent">
                <i class="fa fa-plus"></i> Ongeza Tukio / Ibada
            </button>
            <a href="{{ route('leadership.index') }}" class="btn btn-info">
                <i class="fa fa-user-circle"></i> Uongozi wa Ibada
            </a>
            <a href="{{ route('attendance.create') }}" class="btn btn-success">
                <i class="fa fa-check-square-o"></i> Rekodi Mahudhurio Sasa
            </a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex flex-wrap">
                @foreach($eventTypes as $key => $type)
                    <span class="badge mr-2 mb-2 p-2" style="background:{{ $type['color'] }}">{{ $type['label'] }}</span>
                @endforeach
                <span class="badge mr-2 mb-2 p-2" style="background:#28a745">📋 Mahudhurio yaliyorekodi</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <div id="churchCalendar"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="eventForm">
                    @csrf
                    <input type="hidden" id="eventId" value="">
                    <input type="hidden" id="formMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="eventModalTitle">Ongeza Tukio</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div id="formAlert" class="alert alert-danger d-none"></div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Kichwa / Jina <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="eventTitle" class="form-control" required
                                        placeholder="mf. Ibada ya Jumapili">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Aina <span class="text-danger">*</span></label>
                                    <select name="event_type" id="eventType" class="form-control" required>
                                        @foreach($eventTypes as $key => $type)
                                            <option value="{{ $key }}">{{ $type['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="serviceTypeWrap">
                                <div class="form-group">
                                    <label>Aina ya Ibada</label>
                                    <select name="service_type" id="serviceType" class="form-control">
                                        <option value="">-- Chagua --</option>
                                        @foreach($serviceTypes as $st)
                                            <option value="{{ $st }}">{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kiongozi (mwanachama)</label>
                                    <select name="leader_member_id" id="eventLeaderMember" class="form-control">
                                        <option value="">-- Chagua mwanachama --</option>
                                        @foreach($members as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>au andika jina la kiongozi</label>
                                    <input type="text" name="leader" id="eventLeader" class="form-control"
                                        placeholder="mf. mhubiri mgeni...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mahali</label>
                                    <input type="text" name="location" id="eventLocation" class="form-control"
                                        placeholder="Kanisa, ukumbi...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tarehe & Muda wa Kuanza <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="start_at" id="eventStart" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Muda wa Mwisho</label>
                                    <input type="datetime-local" name="end_at" id="eventEnd" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group pt-4">
                                    <label class="d-flex align-items-center">
                                        <input type="checkbox" name="all_day" id="eventAllDay" value="1" class="mr-2">
                                        Siku nzima
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Maelezo</label>
                                    <textarea name="description" id="eventDescription" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div>
                            <button type="button" class="btn btn-danger d-none" id="btnDeleteEvent">
                                <i class="fa fa-trash"></i> Futa
                            </button>
                            <a href="#" class="btn btn-success d-none" id="btnRecordAttendance">
                                <i class="fa fa-check-square-o"></i> Weka Mahudhurio
                            </a>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Funga</button>
                            <button type="submit" class="btn btn-primary" id="btnSaveEvent">Hifadhi</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<style>
    #churchCalendar { min-height: 650px; }
    .fc-event { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
(function () {
    var csrf = '{{ csrf_token() }}';
    var feedUrl = '{{ route('calendar.feed') }}';
    var storeUrl = '{{ route('calendar.events.store') }}';
    var showUrlTemplate = '{{ url('calendar/events') }}/__ID__';
    var updateUrlTemplate = '{{ url('calendar/events') }}/__ID__';
    var deleteUrlTemplate = '{{ url('calendar/events') }}/__ID__';
    var attendanceUrlTemplate = '{{ url('calendar/events') }}/__ID__/attendance';

    var calendarEl = document.getElementById('churchCalendar');
    var modal = $('#eventModal');
    var form = document.getElementById('eventForm');

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function toLocalInput(date) {
        if (!date) return '';
        var d = new Date(date);
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) +
            'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function toggleServiceType() {
        var isService = document.getElementById('eventType').value === 'service';
        document.getElementById('serviceTypeWrap').style.display = isService ? '' : 'none';
    }

    function resetForm() {
        form.reset();
        document.getElementById('eventId').value = '';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('eventModalTitle').textContent = 'Ongeza Tukio / Ibada';
        document.getElementById('btnDeleteEvent').classList.add('d-none');
        document.getElementById('btnRecordAttendance').classList.add('d-none');
        document.getElementById('formAlert').classList.add('d-none');
        toggleServiceType();
    }

    function openCreate(dateStr) {
        resetForm();
        if (dateStr) {
            document.getElementById('eventStart').value = dateStr.length <= 10 ? dateStr + 'T09:00' : toLocalInput(dateStr);
            document.getElementById('eventEnd').value = dateStr.length <= 10 ? dateStr + 'T11:00' : '';
        }
        modal.modal('show');
    }

    function fillForm(data) {
        document.getElementById('eventTitle').value = data.title || '';
        document.getElementById('eventLeaderMember').value = data.leader_member_id || '';
        document.getElementById('eventLeader').value = data.leader_member_id ? '' : (data.leader || '');
        document.getElementById('eventType').value = data.event_type || 'event';
        document.getElementById('serviceType').value = data.service_type || '';
        document.getElementById('eventLocation').value = data.location || '';
        document.getElementById('eventDescription').value = data.description || '';
        document.getElementById('eventStart').value = data.start_at || '';
        document.getElementById('eventEnd').value = data.end_at || '';
        document.getElementById('eventAllDay').checked = !!data.all_day;
        toggleServiceType();
    }

    function openEdit(eventId) {
        resetForm();
        document.getElementById('eventId').value = eventId;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('eventModalTitle').textContent = 'Hariri Tukio';
        document.getElementById('btnDeleteEvent').classList.remove('d-none');
        document.getElementById('btnRecordAttendance').classList.remove('d-none');
        document.getElementById('btnRecordAttendance').href = attendanceUrlTemplate.replace('__ID__', eventId);

        fetch(showUrlTemplate.replace('__ID__', eventId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) { fillForm(data); modal.modal('show'); });
    }

    document.getElementById('btnAddEvent').addEventListener('click', function () { openCreate(); });
    document.getElementById('eventType').addEventListener('change', toggleServiceType);
    document.getElementById('eventLeaderMember').addEventListener('change', function () {
        if (this.value) document.getElementById('eventLeader').value = '';
    });

    document.getElementById('eventAllDay').addEventListener('change', function () {
        if (this.checked) {
            var start = document.getElementById('eventStart').value;
            if (start) {
                document.getElementById('eventStart').value = start.substring(0, 10) + 'T00:00';
                document.getElementById('eventEnd').value = start.substring(0, 10) + 'T23:59';
            }
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var eventId = document.getElementById('eventId').value;
        var method = document.getElementById('formMethod').value;
        var url = eventId ? updateUrlTemplate.replace('__ID__', eventId) : storeUrl;

        var body = new FormData(form);
        if (method === 'PUT') {
            body.append('_method', 'PUT');
        }

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
            if (!res.ok) {
                var msg = res.data.message || (res.data.errors ? Object.values(res.data.errors).flat().join(' ') : 'Hitilafu');
                var alert = document.getElementById('formAlert');
                alert.textContent = msg;
                alert.classList.remove('d-none');
                return;
            }
            modal.modal('hide');
            calendar.refetchEvents();
        });
    });

    document.getElementById('btnDeleteEvent').addEventListener('click', function () {
        var eventId = document.getElementById('eventId').value;
        if (!eventId || !confirm('Futa tukio hili kutoka kalenda?')) return;

        fetch(deleteUrlTemplate.replace('__ID__', eventId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams({ '_method': 'DELETE' })
        })
        .then(function (r) { return r.json(); })
        .then(function () {
            modal.modal('hide');
            calendar.refetchEvents();
        });
    });

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        height: 'auto',
        locale: 'en',
        firstDay: 0,
        selectable: true,
        editable: false,
        eventSources: [{ url: feedUrl, method: 'GET' }],
        dateClick: function (info) {
            openCreate(info.dateStr);
        },
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            var props = info.event.extendedProps || {};
            if (props.source === 'attendance') {
                if (info.event.url) window.location.href = info.event.url;
                return;
            }
            if (props.source === 'event' && props.eventId) {
                openEdit(props.eventId);
            }
        }
    });

    calendar.render();
    toggleServiceType();
})();
</script>
@endpush

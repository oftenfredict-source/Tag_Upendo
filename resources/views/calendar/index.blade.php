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
            @if(auth()->user()->isFullStaff())
            <button type="button" class="btn btn-primary" id="btnAddEvent">
                <i class="fa fa-plus"></i> Ongeza Tukio / Ibada
            </button>
            @endif
            <a href="{{ route('services.index') }}" class="btn btn-secondary">
                <i class="fa fa-list"></i> {{ __('All Services') }}
            </a>
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

    {{-- Create Church Service Modal --}}
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content service-modal">
                <form id="eventForm">
                    @csrf
                    <input type="hidden" id="eventId" value="">
                    <input type="hidden" id="formMethod" value="POST">
                    <input type="hidden" name="event_type" id="eventType" value="service">
                    <input type="hidden" name="all_day" id="eventAllDay" value="0">

                    <div class="modal-header service-modal-header">
                        <h5 class="modal-title" id="eventModalTitle">
                            <i class="fa fa-institution"></i> Create Church Service
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body">
                        <div id="formAlert" class="alert alert-danger d-none"></div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-institution text-danger"></i> Service Type <span class="text-danger">*</span></label>
                                    <select name="service_type" id="serviceType" class="form-control" required>
                                        <option value="">-- Select Type --</option>
                                        <option value="Sunday Service">Ibada ya Jumapili — Sunday Service</option>
                                        <option value="Mid-week Service">Ibada ya Wiki — Mid-week Service</option>
                                        <option value="Prayer Meeting">Maombi — Prayer Meeting</option>
                                        <option value="Special Event">Tukio Maalum — Special Event</option>
                                        <option value="Other">Nyingine — Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-star text-warning"></i> Theme / Mada</label>
                                    <input type="text" name="theme" id="eventTheme" class="form-control" placeholder="Service theme">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-calendar text-info"></i> Date <span class="text-danger">*</span></label>
                                    <input type="date" name="service_date" id="serviceDate" class="form-control" required>
                                </div>
                            </div>

                            {{-- Single session (non-Sunday) --}}
                            <div class="col-md-4" id="singleStartWrap">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-clock-o text-success"></i> Start Time</label>
                                    <input type="time" name="start_time" id="startTime" class="form-control" value="09:00">
                                </div>
                            </div>
                            <div class="col-md-4" id="singleEndWrap">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-clock-o text-danger"></i> End Time</label>
                                    <input type="time" name="end_time" id="endTime" class="form-control" value="11:00">
                                </div>
                            </div>

                            {{-- Dual Sunday sessions --}}
                            <div class="col-md-12" id="sundaySessionsWrap" hidden>
                                <div class="alert alert-light border mb-3 py-2">
                                    <i class="fa fa-info-circle text-primary"></i>
                                    Ibada ya Jumapili: taarifa moja, vipindi viwili (First &amp; Second) — tofauti ni muda tu.
                                </div>
                                <input type="hidden" name="has_two_services" id="hasTwoServices" value="0">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="session-box">
                                            <h6 class="session-box-title"><i class="fa fa-clock-o text-success"></i> Ibada ya Kwanza (First)</h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label class="svc-label">Start</label>
                                                        <input type="time" name="first_start_time" id="firstStartTime" class="form-control" value="07:00">
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label class="svc-label">End</label>
                                                        <input type="time" name="first_end_time" id="firstEndTime" class="form-control" value="09:00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="session-box">
                                            <h6 class="session-box-title"><i class="fa fa-clock-o text-danger"></i> Ibada ya Pili (Second)</h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label class="svc-label">Start</label>
                                                        <input type="time" name="second_start_time" id="secondStartTime" class="form-control" value="10:00">
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label class="svc-label">End</label>
                                                        <input type="time" name="second_end_time" id="secondEndTime" class="form-control" value="12:00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-user text-danger"></i> Preacher</label>
                                    <select name="preacher_type" id="preacherType" class="form-control mb-2">
                                        <option value="">-- Select type --</option>
                                        <option value="pastor">Pastor</option>
                                        <option value="leader">Leader</option>
                                        <option value="member">Member</option>
                                        <option value="guest">Guest</option>
                                    </select>

                                    <div id="preacherPastorWrap" hidden>
                                        <select id="preacherPastorSelect" class="form-control preacher-pick">
                                            <option value="">-- Select Pastor --</option>
                                            @foreach($pastors as $m)
                                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="preacherLeaderWrap" hidden>
                                        <select id="preacherLeaderSelect" class="form-control preacher-pick">
                                            <option value="">-- Select Leader --</option>
                                            @foreach($leaders as $m)
                                                <option value="{{ $m->id }}">{{ $m->name }}@if($m->relationLoaded('leadershipRoles') && $m->leadershipRoles->isNotEmpty()) ({{ $m->leadershipRolesLabel() }})@endif</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="preacherMemberWrap" hidden class="position-relative">
                                        <input type="text" id="preacherMemberSearch" class="form-control" placeholder="Type at least 2 letters..." autocomplete="off">
                                        <div id="preacherMemberResults" class="member-search-results" hidden></div>
                                        <small class="text-muted" id="preacherMemberSelected"></small>
                                    </div>

                                    <div id="preacherGuestWrap" hidden>
                                        <input type="text" name="preacher_guest_name" id="preacherGuestName" class="form-control" placeholder="Guest preacher name">
                                    </div>

                                    <input type="hidden" name="preacher_member_id" id="preacherMemberId" value="">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-cogs text-danger"></i> Coordinator</label>
                                    <select name="coordinator_member_id" id="coordinatorMember" class="form-control">
                                        <option value="">-- Select Coordinator --</option>
                                        @foreach($members as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-user text-danger"></i> Church Elder</label>
                                    <select name="elder_member_id" id="elderMember" class="form-control">
                                        <option value="">-- Select Church Elder --</option>
                                        @forelse($elders as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @empty
                                            <option value="" disabled>No registered church elders</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-map-marker text-danger"></i> Venue</label>
                                    <input type="text" name="location" id="eventLocation" class="form-control" placeholder="Venue location">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-music text-warning"></i> Choir</label>
                                    <input type="text" name="choir" id="eventChoir" class="form-control" placeholder="Choir name">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-users text-primary"></i> Registered Members <small class="text-muted">(Optional)</small></label>
                                    <input type="number" name="registered_members_count" id="registeredMembers" class="form-control" min="0" placeholder="Count">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-user-plus text-danger"></i> Guests <small class="text-muted">(Optional)</small></label>
                                    <input type="number" name="guests_count" id="guestsCount" class="form-control" min="0" placeholder="Count">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="svc-label"><i class="fa fa-book text-danger"></i> Scripture Readings</label>
                                    <textarea name="scripture_readings" id="scriptureReadings" class="form-control" rows="3" placeholder="Enter scripture readings"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="svc-label"><i class="fa fa-bullhorn text-warning"></i> Announcements</label>
                                    <textarea name="announcements" id="eventAnnouncements" class="form-control" rows="3" placeholder="Write announcements to be read during this service (one per line)"></textarea>
                                </div>
                            </div>

                            <input type="hidden" name="title" id="eventTitle" value="Church Service">
                            <input type="hidden" name="description" id="eventDescription" value="">
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <div>
                            <button type="button" class="btn btn-danger d-none" id="btnDeleteEvent">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            <a href="#" class="btn btn-success d-none" id="btnRecordAttendance">
                                <i class="fa fa-check-square-o"></i> Record Attendance
                            </a>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="btnSaveEvent">
                                <i class="fa fa-save"></i> Save Service
                            </button>
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
    .service-modal-header {
        background: #4a148c;
        color: #fff;
        border-bottom: 0;
    }
    .service-modal-header .modal-title {
        font-weight: 600;
    }
    .service-modal .modal-body {
        padding: 1.25rem 1.5rem;
    }
    .service-modal .form-control {
        border-radius: 8px;
        border-color: #d0d5dd;
        box-shadow: 0 1px 2px rgba(16,24,40,.04);
        min-height: 40px;
    }
    .service-modal textarea.form-control {
        min-height: 90px;
    }
    .svc-label {
        font-weight: 600;
        color: #344054;
        margin-bottom: 6px;
        display: block;
    }
    .svc-label .fa { margin-right: 4px; }
    .session-box {
        border: 1px solid #e4e7ec;
        border-radius: 8px;
        padding: 12px;
        background: #f9fafb;
        margin-bottom: 1rem;
        height: calc(100% - 1rem);
    }
    .session-box-title {
        font-weight: 600;
        margin-bottom: 10px;
        color: #344054;
    }
    .member-search-results {
        position: absolute;
        z-index: 1050;
        left: 0;
        right: 0;
        top: 100%;
        max-height: 200px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
    }
    .member-search-results .msr-item {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        background: transparent;
        padding: 8px 12px;
        cursor: pointer;
    }
    .member-search-results .msr-item:hover {
        background: #f2f4f7;
    }
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

    function todayStr() {
        var d = new Date();
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function syncTitle() {
        var theme = document.getElementById('eventTheme').value.trim();
        var type = document.getElementById('serviceType').value;
        document.getElementById('eventTitle').value = theme || type || 'Church Service';
        document.getElementById('eventDescription').value = theme;
    }

    var memberSearchUrl = '{{ route('members.search') }}';
    var memberSearchTimer = null;

    function setPreacherMemberId(id) {
        document.getElementById('preacherMemberId').value = id || '';
    }

    function clearPreacherPicks() {
        setPreacherMemberId('');
        document.getElementById('preacherPastorSelect').value = '';
        document.getElementById('preacherLeaderSelect').value = '';
        document.getElementById('preacherMemberSearch').value = '';
        document.getElementById('preacherMemberSelected').textContent = '';
        document.getElementById('preacherGuestName').value = '';
        document.getElementById('preacherMemberResults').hidden = true;
        document.getElementById('preacherMemberResults').innerHTML = '';
    }

    function togglePreacherUi() {
        var type = document.getElementById('preacherType').value;
        document.getElementById('preacherPastorWrap').hidden = type !== 'pastor';
        document.getElementById('preacherLeaderWrap').hidden = type !== 'leader';
        document.getElementById('preacherMemberWrap').hidden = type !== 'member';
        document.getElementById('preacherGuestWrap').hidden = type !== 'guest';
        if (type !== 'guest') {
            document.getElementById('preacherGuestName').value = '';
        }
        if (type === 'guest') {
            setPreacherMemberId('');
        }
    }

    function applyPreacherType(type, memberId, guestName, memberLabel) {
        document.getElementById('preacherType').value = type || '';
        clearPreacherPicks();
        togglePreacherUi();

        if (type === 'guest') {
            document.getElementById('preacherGuestName').value = guestName || '';
            return;
        }

        if (!memberId) return;
        setPreacherMemberId(memberId);

        if (type === 'pastor') {
            document.getElementById('preacherPastorSelect').value = String(memberId);
        } else if (type === 'leader') {
            document.getElementById('preacherLeaderSelect').value = String(memberId);
        } else if (type === 'member') {
            document.getElementById('preacherMemberSearch').value = memberLabel || '';
            document.getElementById('preacherMemberSelected').textContent = memberLabel ? ('Selected: ' + memberLabel) : '';
        }
    }

    function searchMembers(q) {
        var box = document.getElementById('preacherMemberResults');
        if (!q || q.length < 2) {
            box.hidden = true;
            box.innerHTML = '';
            return;
        }

        fetch(memberSearchUrl + '?q=' + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (rows) {
            if (!rows.length) {
                box.innerHTML = '<div class="px-3 py-2 text-muted">No members found</div>';
                box.hidden = false;
                return;
            }
            box.innerHTML = rows.map(function (m) {
                return '<button type="button" class="msr-item" data-id="' + m.id + '" data-name="' + m.name.replace(/"/g, '&quot;') + '">' + m.name + '</button>';
            }).join('');
            box.hidden = false;
        });
    }

    function isSundayCreate() {
        var isEdit = !!document.getElementById('eventId').value;
        return !isEdit && document.getElementById('serviceType').value === 'Sunday Service';
    }

    function toggleServiceTypeUi() {
        var sunday = isSundayCreate();
        document.getElementById('sundaySessionsWrap').hidden = !sunday;
        document.getElementById('singleStartWrap').hidden = sunday;
        document.getElementById('singleEndWrap').hidden = sunday;
        document.getElementById('hasTwoServices').value = sunday ? '1' : '0';
        syncTitle();
    }

    function resetForm() {
        form.reset();
        document.getElementById('eventId').value = '';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('eventType').value = 'service';
        document.getElementById('eventAllDay').value = '0';
        document.getElementById('eventModalTitle').innerHTML = '<i class="fa fa-institution"></i> Create Church Service';
        document.getElementById('btnDeleteEvent').classList.add('d-none');
        document.getElementById('btnRecordAttendance').classList.add('d-none');
        document.getElementById('formAlert').classList.add('d-none');
        document.getElementById('serviceDate').value = todayStr();
        document.getElementById('serviceType').value = '';
        document.getElementById('startTime').value = '09:00';
        document.getElementById('endTime').value = '11:00';
        document.getElementById('firstStartTime').value = '07:00';
        document.getElementById('firstEndTime').value = '09:00';
        document.getElementById('secondStartTime').value = '10:00';
        document.getElementById('secondEndTime').value = '12:00';
        clearPreacherPicks();
        togglePreacherUi();
        toggleServiceTypeUi();
        syncTitle();
    }

    function openCreate(dateStr) {
        resetForm();
        if (dateStr) {
            document.getElementById('serviceDate').value = dateStr.substring(0, 10);
        }
        modal.modal('show');
    }

    function fillForm(data) {
        var st = data.service_type || '';
        var typeSelect = document.getElementById('serviceType');
        if (st === 'First Service (Sunday)' || st === 'Second Service (Sunday)') {
            if (!Array.from(typeSelect.options).some(function (o) { return o.value === st; })) {
                var opt = document.createElement('option');
                opt.value = st;
                opt.textContent = st;
                typeSelect.appendChild(opt);
            }
            typeSelect.value = st;
        } else {
            typeSelect.value = st;
        }

        document.getElementById('eventTheme').value = data.theme || '';
        document.getElementById('serviceDate').value = data.service_date || '';
        document.getElementById('startTime').value = data.start_time || '09:00';
        document.getElementById('endTime').value = data.end_time || '11:00';
        document.getElementById('firstStartTime').value = data.start_time || '07:00';
        document.getElementById('firstEndTime').value = data.end_time || '09:00';
        document.getElementById('hasTwoServices').value = '0';
        toggleServiceTypeUi();
        applyPreacherType(
            data.preacher_type || '',
            data.preacher_member_id || data.leader_member_id || '',
            data.leader || '',
            data.preacher_name || data.leader || ''
        );
        document.getElementById('coordinatorMember').value = data.coordinator_member_id || '';
        document.getElementById('elderMember').value = data.elder_member_id || '';
        document.getElementById('eventLocation').value = data.location || '';
        document.getElementById('eventChoir').value = data.choir || '';
        document.getElementById('registeredMembers').value = data.registered_members_count != null ? data.registered_members_count : '';
        document.getElementById('guestsCount').value = data.guests_count != null ? data.guests_count : '';
        document.getElementById('scriptureReadings').value = data.scripture_readings || '';
        document.getElementById('eventAnnouncements').value = data.announcements || '';
        document.getElementById('eventType').value = data.event_type || 'service';
        syncTitle();
    }

    function openEdit(eventId) {
        resetForm();
        document.getElementById('eventId').value = eventId;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('eventModalTitle').innerHTML = '<i class="fa fa-institution"></i> Edit Church Service';
        document.getElementById('btnDeleteEvent').classList.remove('d-none');
        document.getElementById('btnRecordAttendance').classList.remove('d-none');
        document.getElementById('btnRecordAttendance').href = attendanceUrlTemplate.replace('__ID__', eventId);

        fetch(showUrlTemplate.replace('__ID__', eventId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) { fillForm(data); modal.modal('show'); });
    }

    var btnAddEvent = document.getElementById('btnAddEvent');
    if (btnAddEvent) {
        btnAddEvent.addEventListener('click', function () { openCreate(); });
    }
    document.getElementById('serviceType').addEventListener('change', toggleServiceTypeUi);
    document.getElementById('eventTheme').addEventListener('input', syncTitle);
    document.getElementById('preacherType').addEventListener('change', function () {
        clearPreacherPicks();
        togglePreacherUi();
    });
    document.getElementById('preacherPastorSelect').addEventListener('change', function () {
        setPreacherMemberId(this.value);
    });
    document.getElementById('preacherLeaderSelect').addEventListener('change', function () {
        setPreacherMemberId(this.value);
    });
    document.getElementById('preacherMemberSearch').addEventListener('input', function () {
        var q = this.value.trim();
        setPreacherMemberId('');
        document.getElementById('preacherMemberSelected').textContent = '';
        clearTimeout(memberSearchTimer);
        memberSearchTimer = setTimeout(function () { searchMembers(q); }, 250);
    });
    document.getElementById('preacherMemberResults').addEventListener('click', function (e) {
        var btn = e.target.closest('.msr-item');
        if (!btn) return;
        setPreacherMemberId(btn.getAttribute('data-id'));
        document.getElementById('preacherMemberSearch').value = btn.getAttribute('data-name');
        document.getElementById('preacherMemberSelected').textContent = 'Selected: ' + btn.getAttribute('data-name');
        document.getElementById('preacherMemberResults').hidden = true;
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#preacherMemberWrap')) {
            document.getElementById('preacherMemberResults').hidden = true;
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        syncTitle();

        var eventId = document.getElementById('eventId').value;
        var method = document.getElementById('formMethod').value;
        var url = eventId ? updateUrlTemplate.replace('__ID__', eventId) : storeUrl;

        var body = new FormData(form);
        if (method === 'PUT') {
            body.append('_method', 'PUT');
            body.set('start_time', document.getElementById('startTime').value);
            body.set('end_time', document.getElementById('endTime').value);
            body.delete('has_two_services');
            body.delete('first_start_time');
            body.delete('first_end_time');
            body.delete('second_start_time');
            body.delete('second_end_time');
        } else if (!isSundayCreate()) {
            body.set('has_two_services', '0');
            body.delete('first_start_time');
            body.delete('first_end_time');
            body.delete('second_start_time');
            body.delete('second_end_time');
        } else {
            body.set('has_two_services', '1');
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
                var alertEl = document.getElementById('formAlert');
                alertEl.textContent = msg;
                alertEl.classList.remove('d-none');
                return;
            }
            modal.modal('hide');
            calendar.refetchEvents();
        });
    });

    document.getElementById('btnDeleteEvent').addEventListener('click', function () {
        var eventId = document.getElementById('eventId').value;
        if (!eventId) return;

        swalConfirm({
            title: @json(__('Confirm')),
            text: @json(__('Delete this church service from the calendar?'))
        }).then(function (result) {
            if (!result.isConfirmed) return;

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
                // Open group detail page instead of editing one session
                window.location.href = '{{ url('services') }}/' + props.eventId;
            }
        }
    });

    calendar.render();

    var params = new URLSearchParams(window.location.search);
    if (params.get('open') === '1') {
        openCreate(params.get('date') || todayStr());
        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
})();
</script>
@endpush

@extends('layouts.app')

@section('title', __('Edit') . ' — ' . __('Church Service'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-pencil"></i> {{ __('Edit Church Service') }}</h1>
            <p>{{ $service->theme ?: __('Church Service') }} — {{ $service->start_at->format('d/m/Y') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('services.index') }}">{{ __('Church Services') }}</a></li>
            <li class="breadcrumb-item">{{ __('Edit') }}</li>
        </ul>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('services.update', $service) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <div class="tile mb-4">
                    <h3 class="tile-title">{{ __('Service details') }}</h3>
                    <div class="tile-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="control-label">{{ __('Theme') }} / Mada</label>
                                    <input type="text" name="theme" class="form-control" value="{{ old('theme', $service->theme) }}" placeholder="{{ __('Theme') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="service_date" class="form-control" required
                                        value="{{ old('service_date', $service->start_at->format('Y-m-d')) }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ __('Preacher') }}</label>
                                    @php
                                        $currentPreacherId = old('preacher_member_id', $service->preacher_member_id ?: $service->leader_member_id);
                                        $currentType = old('preacher_type', $preacherType);
                                        $currentGuest = old('preacher_guest_name', $currentType === 'guest' ? $service->leader : '');
                                        $currentName = $service->preacherMember->name ?? ($currentType === 'member' ? $service->leader : '');
                                    @endphp
                                    <select name="preacher_type" id="preacherType" class="form-control mb-2">
                                        <option value="">-- Select type --</option>
                                        <option value="pastor" @selected($currentType === 'pastor')>Pastor</option>
                                        <option value="leader" @selected($currentType === 'leader')>Leader</option>
                                        <option value="member" @selected($currentType === 'member')>Member</option>
                                        <option value="guest" @selected($currentType === 'guest')>Guest</option>
                                    </select>

                                    <div id="preacherPastorWrap" @if($currentType !== 'pastor') hidden @endif>
                                        <select id="preacherPastorSelect" class="form-control">
                                            <option value="">-- Select Pastor --</option>
                                            @foreach($pastors as $m)
                                                <option value="{{ $m->id }}" @selected($currentType === 'pastor' && (string)$currentPreacherId === (string)$m->id)>{{ $m->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="preacherLeaderWrap" @if($currentType !== 'leader') hidden @endif>
                                        <select id="preacherLeaderSelect" class="form-control">
                                            <option value="">-- Select Leader --</option>
                                            @foreach($leaders as $m)
                                                <option value="{{ $m->id }}" @selected($currentType === 'leader' && (string)$currentPreacherId === (string)$m->id)>{{ $m->name }}@if($m->relationLoaded('leadershipRoles') && $m->leadershipRoles->isNotEmpty()) ({{ $m->leadershipRolesLabel() }})@endif</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="preacherMemberWrap" class="position-relative" @if($currentType !== 'member') hidden @endif>
                                        <input type="text" id="preacherMemberSearch" class="form-control" placeholder="Type at least 2 letters..." autocomplete="off"
                                            value="{{ $currentType === 'member' ? $currentName : '' }}">
                                        <div id="preacherMemberResults" class="member-search-results" hidden></div>
                                        <small class="text-muted" id="preacherMemberSelected">
                                            @if($currentType === 'member' && $currentName) Selected: {{ $currentName }} @endif
                                        </small>
                                    </div>

                                    <div id="preacherGuestWrap" @if($currentType !== 'guest') hidden @endif>
                                        <input type="text" name="preacher_guest_name" id="preacherGuestName" class="form-control" placeholder="Guest preacher name" value="{{ $currentGuest }}">
                                    </div>

                                    <input type="hidden" name="preacher_member_id" id="preacherMemberId" value="{{ $currentType !== 'guest' ? $currentPreacherId : '' }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ __('Coordinator') }}</label>
                                    <select name="coordinator_member_id" class="form-control">
                                        <option value="">-- {{ __('Coordinator') }} --</option>
                                        @foreach($members as $m)
                                            <option value="{{ $m->id }}" @selected(old('coordinator_member_id', $service->coordinator_member_id) == $m->id)>{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ __('Church Elder') }}</label>
                                    <select name="elder_member_id" class="form-control">
                                        <option value="">-- {{ __('Church Elder') }} --</option>
                                        @forelse($elders as $m)
                                            <option value="{{ $m->id }}" @selected(old('elder_member_id', $service->elder_member_id) == $m->id)>{{ $m->name }}</option>
                                        @empty
                                            <option value="" disabled>No registered church elders</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ __('Venue') }}</label>
                                    <input type="text" name="location" class="form-control" value="{{ old('location', $service->location) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ __('Choir') }}</label>
                                    <input type="text" name="choir" class="form-control" value="{{ old('choir', $service->choir) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ __('Registered Members') }}</label>
                                    <input type="number" name="registered_members_count" class="form-control" min="0"
                                        value="{{ old('registered_members_count', $service->registered_members_count) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ __('Guests') }}</label>
                                    <input type="number" name="guests_count" class="form-control" min="0"
                                        value="{{ old('guests_count', $service->guests_count) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tile mb-4">
                    <h3 class="tile-title">{{ __('Sessions') }}</h3>
                    <div class="tile-body">
                        <div class="row">
                            @foreach($sessions as $i => $session)
                                <div class="col-md-{{ $sessions->count() > 1 ? '6' : '12' }} mb-3">
                                    <div class="session-edit-card">
                                        <div class="session-edit-header {{ str_contains($session->service_type, 'Second') ? 'second' : 'first' }}">
                                            {{ str_contains($session->service_type, 'Second') ? __('Second Service') : (str_contains($session->service_type, 'First') ? __('First Service') : ($session->service_type ?: __('Church Service'))) }}
                                        </div>
                                        <div class="session-edit-body">
                                            <input type="hidden" name="sessions[{{ $i }}][id]" value="{{ $session->id }}">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label class="control-label">{{ __('Start') }}</label>
                                                        <input type="time" name="sessions[{{ $i }}][start_time]" class="form-control" required
                                                            value="{{ old('sessions.'.$i.'.start_time', $session->start_at->format('H:i')) }}">
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label class="control-label">{{ __('End') }}</label>
                                                        <input type="time" name="sessions[{{ $i }}][end_time]" class="form-control"
                                                            value="{{ old('sessions.'.$i.'.end_time', $session->end_at?->format('H:i')) }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="tile mb-4">
                    <h3 class="tile-title">{{ __('Scripture Readings') }}</h3>
                    <div class="tile-body">
                        <textarea name="scripture_readings" class="form-control" rows="5">{{ old('scripture_readings', $service->scripture_readings) }}</textarea>
                    </div>
                </div>
                <div class="tile mb-4">
                    <h3 class="tile-title">{{ __('Announcements') }}</h3>
                    <div class="tile-body">
                        <textarea name="announcements" class="form-control" rows="5">{{ old('announcements', $service->announcements) }}</textarea>
                    </div>
                </div>
                <div class="mb-4">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa fa-save"></i> {{ __('Save changes') }}
                    </button>
                    <a href="{{ route('services.show', $service) }}" class="btn btn-secondary btn-block">
                        <i class="fa fa-times"></i> {{ __('Cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<style>
    .session-edit-card {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        overflow: hidden;
        height: 100%;
    }
    .session-edit-header {
        padding: 10px 14px;
        color: #fff;
        font-weight: 600;
    }
    .session-edit-header.first { background: #940000; }
    .session-edit-header.second { background: #3f51b5; }
    .session-edit-body { padding: 14px; background: #f9fafb; }
    .member-search-results {
        position: absolute;
        z-index: 20;
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
    .member-search-results .msr-item:hover { background: #f2f4f7; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var memberSearchUrl = @json(route('members.search'));
    var timer = null;

    function setId(id) {
        document.getElementById('preacherMemberId').value = id || '';
    }

    function toggleUi() {
        var type = document.getElementById('preacherType').value;
        document.getElementById('preacherPastorWrap').hidden = type !== 'pastor';
        document.getElementById('preacherLeaderWrap').hidden = type !== 'leader';
        document.getElementById('preacherMemberWrap').hidden = type !== 'member';
        document.getElementById('preacherGuestWrap').hidden = type !== 'guest';
        if (type === 'guest') setId('');
        if (type !== 'guest') document.getElementById('preacherGuestName').value = document.getElementById('preacherGuestName').value;
    }

    document.getElementById('preacherType').addEventListener('change', function () {
        setId('');
        document.getElementById('preacherPastorSelect').value = '';
        document.getElementById('preacherLeaderSelect').value = '';
        document.getElementById('preacherMemberSearch').value = '';
        document.getElementById('preacherMemberSelected').textContent = '';
        document.getElementById('preacherGuestName').value = '';
        toggleUi();
    });

    document.getElementById('preacherPastorSelect').addEventListener('change', function () { setId(this.value); });
    document.getElementById('preacherLeaderSelect').addEventListener('change', function () { setId(this.value); });

    document.getElementById('preacherMemberSearch').addEventListener('input', function () {
        var q = this.value.trim();
        setId('');
        document.getElementById('preacherMemberSelected').textContent = '';
        clearTimeout(timer);
        timer = setTimeout(function () {
            var box = document.getElementById('preacherMemberResults');
            if (q.length < 2) { box.hidden = true; box.innerHTML = ''; return; }
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
        }, 250);
    });

    document.getElementById('preacherMemberResults').addEventListener('click', function (e) {
        var btn = e.target.closest('.msr-item');
        if (!btn) return;
        setId(btn.getAttribute('data-id'));
        document.getElementById('preacherMemberSearch').value = btn.getAttribute('data-name');
        document.getElementById('preacherMemberSelected').textContent = 'Selected: ' + btn.getAttribute('data-name');
        document.getElementById('preacherMemberResults').hidden = true;
    });
})();
</script>
@endpush

@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-check-square"></i> Weka Mahudhurio</h1>
            <p>{{ $service->displayName() }} — {{ $service->service_date->format('d/m/Y') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item">Collect</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('attendance.collect.save', $service) }}" method="POST" id="attendanceForm">
        @csrf

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="control-label"><i class="fa fa-search"></i> Tafuta mwanachama</label>
                                <input type="text" id="memberSearch" class="form-control"
                                    placeholder="Andika jina au simu...">
                            </div>
                            <div class="col-md-3">
                                <label class="control-label">Idara</label>
                                <select id="deptFilter" class="form-control">
                                    <option value="">Idara zote</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5 text-md-right">
                                <button type="button" class="btn btn-success btn-sm" id="selectAllBtn">
                                    <i class="fa fa-check-square"></i> Chagua Wote
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="clearAllBtn">
                                    <i class="fa fa-square-o"></i> Futa Zote
                                </button>
                                <span class="badge badge-primary ml-2 p-2" id="presentCount">
                                    {{ count($presentIds) }} / {{ $members->count() }} wapo
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Weka alama kwa waliohudhuria</h3>
                    <div class="tile-body">
                        <div class="row" id="memberList">
                            @foreach($members as $member)
                                <div class="col-md-4 col-lg-3 mb-2 member-item"
                                    data-name="{{ strtolower($member->name) }}"
                                    data-phone="{{ strtolower($member->phone_number) }}"
                                    data-dept="{{ $member->department_id ?? '' }}">
                                    <label class="d-flex align-items-center p-2 border rounded member-label"
                                        style="cursor:pointer;margin:0;{{ in_array($member->id, $presentIds) ? 'background:#f5e6e6;border-color:#940000!important;' : '' }}">
                                        <input type="checkbox" name="present[]" value="{{ $member->id }}"
                                            class="present-check mr-2"
                                            {{ in_array($member->id, $presentIds) ? 'checked' : '' }}>
                                        <span>
                                            <strong>{{ $member->name }}</strong>
                                            @if($member->department)
                                                <br><small class="text-muted">{{ $member->department->name }}</small>
                                            @endif
                                            @if($member->parent_id)
                                                <br><small class="badge badge-light">Mtoto</small>
                                            @endif
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @if($members->isEmpty())
                            <p class="text-center text-muted">Hakuna wanachama. <a href="{{ route('members.create') }}">Sajili wanachama kwanza</a>.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa fa-save"></i> Hifadhi Mahudhurio
                </button>
                <a href="{{ route('attendance.show', $service) }}" class="btn btn-info">Angalia Ripoti</a>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Rudi</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var searchInput = document.getElementById('memberSearch');
    var deptFilter = document.getElementById('deptFilter');
    var items = document.querySelectorAll('.member-item');
    var checks = document.querySelectorAll('.present-check');
    var countEl = document.getElementById('presentCount');
    var total = checks.length;

    function updateCount() {
        var n = document.querySelectorAll('.present-check:checked').length;
        countEl.textContent = n + ' / ' + total + ' wapo';
    }

    function highlightLabel(checkbox) {
        var label = checkbox.closest('.member-label');
        if (!label) return;
        label.style.background = checkbox.checked ? '#f5e6e6' : '';
        label.style.borderColor = checkbox.checked ? '#940000' : '';
    }

    function filterList() {
        var q = (searchInput.value || '').toLowerCase().trim();
        var dept = deptFilter.value;
        items.forEach(function (item) {
            var name = item.getAttribute('data-name') || '';
            var phone = item.getAttribute('data-phone') || '';
            var itemDept = item.getAttribute('data-dept') || '';
            var matchSearch = !q || name.indexOf(q) !== -1 || phone.indexOf(q) !== -1;
            var matchDept = !dept || itemDept === dept;
            item.style.display = matchSearch && matchDept ? '' : 'none';
        });
    }

    checks.forEach(function (cb) {
        cb.addEventListener('change', function () {
            highlightLabel(cb);
            updateCount();
        });
        highlightLabel(cb);
    });

    searchInput.addEventListener('input', filterList);
    deptFilter.addEventListener('change', filterList);

    document.getElementById('selectAllBtn').addEventListener('click', function () {
        items.forEach(function (item) {
            if (item.style.display === 'none') return;
            var cb = item.querySelector('.present-check');
            cb.checked = true;
            highlightLabel(cb);
        });
        updateCount();
    });

    document.getElementById('clearAllBtn').addEventListener('click', function () {
        checks.forEach(function (cb) {
            cb.checked = false;
            highlightLabel(cb);
        });
        updateCount();
    });

    updateCount();
})();
</script>
@endpush

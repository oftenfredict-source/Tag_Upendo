@extends('layouts.app')

@section('title', __('Add child'))

@section('content')
    @php
        $minChildDob = now()->subYears(\App\Models\Member::MAX_CHILD_AGE)->format('Y-m-d');
        $maxChildDob = now()->format('Y-m-d');
        $defaultMode = old('parent_mode', $parent ? 'member' : 'member');
    @endphp

    <div class="app-title">
        <div>
            <h1><i class="fa fa-child"></i> {{ __('Add child') }}</h1>
            <p>
                @if($parent)
                    {{ __('Register a child for') }} <strong>{{ $parent->name }}</strong>
                @else
                    {{ __('Register a new child aged 0 to 18 years') }}
                @endif
            </p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.children') }}">{{ __('Children') }}</a></li>
            <li class="breadcrumb-item">{{ __('Add child') }}</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="tile">
                <h3 class="tile-title">{{ __('Child registration') }}</h3>
                <div class="tile-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('members.store') }}" method="POST" id="addChildForm">
                        @csrf
                        <input type="hidden" name="is_child" value="1">

                        <div class="spouse-section mb-4">
                            <div class="spouse-section-header">
                                <h6 class="mb-0"><i class="fa fa-user"></i> {{ __('Parent / Guardian') }}</h6>
                            </div>
                            <div class="spouse-section-body">
                                <div class="form-group mb-3">
                                    <label class="control-label d-block">{{ __('Is the parent/guardian a church member?') }} <span class="text-danger">*</span></label>
                                    <div class="mt-1">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="parent_mode" value="member"
                                                    id="parentModeMember"
                                                    {{ $defaultMode === 'member' ? 'checked' : '' }}>
                                                {{ __('Yes — select from member list') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="parent_mode" value="external"
                                                    id="parentModeExternal"
                                                    {{ $defaultMode === 'external' ? 'checked' : '' }}>
                                                {{ __('No — enter guardian details') }}
                                            </label>
                                        </div>
                                    </div>
                                    @error('parent_mode')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div id="parentMemberWrap" @if($defaultMode !== 'member') hidden @endif>
                                    <div class="form-group mb-0">
                                        <label class="control-label">{{ __('Select parent (member)') }} <span class="text-danger">*</span></label>
                                        <select class="form-control" name="parent_id" id="parentMemberSelect">
                                            <option value="">-- {{ __('Select') }} --</option>
                                            @foreach($parents as $p)
                                                <option value="{{ $p->id }}"
                                                    {{ (string) old('parent_id', $parent->id ?? '') === (string) $p->id ? 'selected' : '' }}>
                                                    {{ $p->name }}@if($p->phone_number) — {{ $p->phone_number }}@endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('parent_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                        <p class="text-muted small mt-2 mb-0">
                                            {{ __('Contact and address details will be copied from the selected member.') }}
                                        </p>
                                    </div>
                                </div>

                                <div id="parentExternalWrap" @if($defaultMode !== 'external') hidden @endif>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="control-label">{{ __('Guardian full name') }} <span class="text-danger">*</span></label>
                                                <input class="form-control" type="text" name="guardian_name" id="guardianNameInput"
                                                    value="{{ old('guardian_name') }}"
                                                    placeholder="{{ __('Parent or guardian name') }}">
                                                @error('guardian_name')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="control-label">{{ __('Guardian phone number') }} <span class="text-danger">*</span></label>
                                                <input class="form-control" type="text" name="guardian_phone" id="guardianPhoneInput"
                                                    value="{{ old('guardian_phone') }}"
                                                    placeholder="{{ __('Phone') }}">
                                                @error('guardian_phone')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="spouse-section mb-4">
                            <div class="spouse-section-header">
                                <h6 class="mb-0"><i class="fa fa-child"></i> {{ __('Child information') }}</h6>
                            </div>
                            <div class="spouse-section-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Full name') }} <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="name" value="{{ old('name') }}" required
                                                placeholder="{{ __('Child full name') }}" autofocus>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Gender') }}</label>
                                            <select class="form-control" name="gender">
                                                <option value="">-- {{ __('Select') }} --</option>
                                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Date of Birth') }}</label>
                                            <input class="form-control" type="date" name="date_of_birth" id="childDateOfBirth"
                                                value="{{ old('date_of_birth') }}"
                                                min="{{ $minChildDob }}"
                                                max="{{ $maxChildDob }}">
                                            <small class="text-muted">{{ __('Children are aged 0 to 18 years') }}</small>
                                            @error('date_of_birth')
                                                <small class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="control-label">{{ __('Age') }}</label>
                                            <p class="form-control-plaintext mb-0 text-primary font-weight-bold" id="childAgeDisplay">—</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap">
                            <button class="btn btn-primary mr-2 mb-2" type="submit">
                                <i class="fa fa-check-circle"></i> {{ __('Save child') }}
                            </button>
                            <a href="{{ $parent ? route('members.show', $parent) : route('members.children') }}" class="btn btn-secondary mb-2">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .spouse-section {
        border: 1px solid #e8e8e8;
        border-radius: 6px;
        overflow: hidden;
    }
    .spouse-section-header {
        background: #f8f9fa;
        padding: 10px 15px;
        border-bottom: 1px solid #e8e8e8;
    }
    .spouse-section-body {
        padding: 15px;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var maxChildAge = {{ \App\Models\Member::MAX_CHILD_AGE }};
    var yearsLabel = @json(__(':count years', ['count' => '__AGE__']));
    var yearLabel = @json(__(':count year', ['count' => '__AGE__']));
    var tooOldLabel = @json(__('Child must be 18 years or younger'));

    var modeMember = document.getElementById('parentModeMember');
    var modeExternal = document.getElementById('parentModeExternal');
    var memberWrap = document.getElementById('parentMemberWrap');
    var externalWrap = document.getElementById('parentExternalWrap');
    var parentSelect = document.getElementById('parentMemberSelect');
    var guardianName = document.getElementById('guardianNameInput');
    var guardianPhone = document.getElementById('guardianPhoneInput');
    var dobInput = document.getElementById('childDateOfBirth');
    var ageDisplay = document.getElementById('childAgeDisplay');

    function ageFromDate(dateStr) {
        if (!dateStr) return null;
        var dob = new Date(dateStr + 'T00:00:00');
        if (isNaN(dob.getTime())) return null;
        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        var monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) age--;
        return age >= 0 ? age : null;
    }

    function toggleParentMode() {
        var isMember = modeMember && modeMember.checked;
        if (memberWrap) memberWrap.hidden = !isMember;
        if (externalWrap) externalWrap.hidden = isMember;

        if (parentSelect) {
            parentSelect.required = isMember;
            if (!isMember) parentSelect.value = '';
        }
        if (guardianName) guardianName.required = !isMember;
        if (guardianPhone) guardianPhone.required = !isMember;
    }

    function updateAge() {
        if (!ageDisplay || !dobInput) return;
        var age = ageFromDate(dobInput.value);
        if (age === null) {
            ageDisplay.textContent = '—';
            ageDisplay.classList.remove('text-danger');
            ageDisplay.classList.add('text-primary');
            return;
        }
        if (age > maxChildAge) {
            ageDisplay.textContent = tooOldLabel;
            ageDisplay.classList.add('text-danger');
            ageDisplay.classList.remove('text-primary');
            return;
        }
        ageDisplay.classList.remove('text-danger');
        ageDisplay.classList.add('text-primary');
        ageDisplay.textContent = age === 1
            ? yearLabel.replace('__AGE__', '1')
            : yearsLabel.replace('__AGE__', String(age));
    }

    if (modeMember) modeMember.addEventListener('change', toggleParentMode);
    if (modeExternal) modeExternal.addEventListener('change', toggleParentMode);
    if (dobInput) dobInput.addEventListener('change', updateAge);

    toggleParentMode();
    updateAge();
})();
</script>
@endpush

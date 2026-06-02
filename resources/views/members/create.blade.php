@extends('layouts.app')

@php
    $errorStep = 1;
    if ($errors->has('phone_number') || $errors->has('email') || $errors->has('emergency_contact_name') || $errors->has('emergency_contact_phone')) {
        $errorStep = 3;
    } elseif ($errors->has('member_type') || $errors->has('date_joined') || $errors->has('is_baptized') || $errors->has('baptism_date') || $errors->has('department_id') || $errors->has('notes')) {
        $errorStep = 4;
    } elseif ($errors->has('birth_mkoa') || $errors->has('birth_wilaya') || $errors->has('residence_mkoa') || $errors->has('residence_wilaya') || $errors->has('address')) {
        $errorStep = 2;
    }
@endphp

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-user-plus"></i> Add Member</h1>
            <p>Register a new church member — hatua <span id="stepIndicatorText">1</span> kati ya 4</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Members</a></li>
            <li class="breadcrumb-item">Add</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="tile">
                <h3 class="tile-title">Member Registration Form</h3>
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

                    <div class="member-wizard-nav mb-4">
                        <div class="member-wizard-step active" data-step-nav="1">
                            <span class="step-num">1</span>
                            <span class="step-label">Taarifa Binafsi</span>
                        </div>
                        <div class="member-wizard-step" data-step-nav="2">
                            <span class="step-num">2</span>
                            <span class="step-label">Mahali</span>
                        </div>
                        <div class="member-wizard-step" data-step-nav="3">
                            <span class="step-num">3</span>
                            <span class="step-label">Mawasiliano</span>
                        </div>
                        <div class="member-wizard-step" data-step-nav="4">
                            <span class="step-num">4</span>
                            <span class="step-label">Kanisa</span>
                        </div>
                    </div>

                    <form action="{{ route('members.store') }}" method="POST" id="memberWizardForm" novalidate>
                        @csrf

                        {{-- Step 1: Personal --}}
                        <div class="wizard-panel" data-step="1">
                            <h5 class="mb-3 text-muted"><i class="fa fa-user"></i> Hatua 1: Taarifa za Kibinafsi</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Full Name <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="name" value="{{ old('name') }}" required
                                            placeholder="Jina kamili">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Gender</label>
                                        <select class="form-control" name="gender">
                                            <option value="">-- Chagua --</option>
                                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Date of Birth</label>
                                        <input class="form-control" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Marital Status</label>
                                        <select class="form-control" name="marital_status">
                                            <option value="">-- Chagua --</option>
                                            <option value="single" {{ old('marital_status') === 'single' ? 'selected' : '' }}>Single</option>
                                            <option value="married" {{ old('marital_status') === 'married' ? 'selected' : '' }}>Married</option>
                                            <option value="widowed" {{ old('marital_status') === 'widowed' ? 'selected' : '' }}>Widowed</option>
                                            <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Occupation</label>
                                        <input class="form-control" type="text" name="occupation" value="{{ old('occupation') }}"
                                            placeholder="e.g. Teacher, Business">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Location --}}
                        <div class="wizard-panel" data-step="2" hidden>
                            <h5 class="mb-3 text-muted"><i class="fa fa-map-marker"></i> Hatua 2: Mahali pa Kuzaliwa & Makazi</h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="text-muted mb-2">Mahali pa Kuzaliwa</h6>
                                </div>
                                @include('partials.region-district-select', [
                                    'mkoaName' => 'birth_mkoa',
                                    'wilayaName' => 'birth_wilaya',
                                    'mkoaId' => 'birth_mkoa',
                                    'wilayaId' => 'birth_wilaya',
                                    'oldMkoa' => old('birth_mkoa'),
                                    'oldWilaya' => old('birth_wilaya'),
                                ])
                                <div class="col-md-12 mt-2">
                                    <h6 class="text-muted mb-2">Makazi ya Sasa</h6>
                                </div>
                                @include('partials.region-district-select', [
                                    'mkoaName' => 'residence_mkoa',
                                    'wilayaName' => 'residence_wilaya',
                                    'mkoaId' => 'residence_mkoa',
                                    'wilayaId' => 'residence_wilaya',
                                    'oldMkoa' => old('residence_mkoa'),
                                    'oldWilaya' => old('residence_wilaya'),
                                ])
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">Mtaa / Kata / Maelezo</label>
                                        <input class="form-control" type="text" name="address" value="{{ old('address') }}"
                                            placeholder="Mtaa, kata, au eneo maalum">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Contact --}}
                        <div class="wizard-panel" data-step="3" hidden>
                            <h5 class="mb-3 text-muted"><i class="fa fa-phone"></i> Hatua 3: Mawasiliano</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Phone Number <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="phone_number" id="phoneNumberInput"
                                            value="{{ old('phone_number') }}" required
                                            placeholder="Nambari ya simu">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Email</label>
                                        <input class="form-control" type="email" name="email" value="{{ old('email') }}"
                                            placeholder="Barua pepe (si lazima)">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Emergency Contact Name</label>
                                        <input class="form-control" type="text" name="emergency_contact_name"
                                            value="{{ old('emergency_contact_name') }}" placeholder="Jina la ndugu">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Emergency Contact Phone</label>
                                        <input class="form-control" type="text" name="emergency_contact_phone"
                                            value="{{ old('emergency_contact_phone') }}" placeholder="Simu ya dharura">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 4: Church --}}
                        <div class="wizard-panel" data-step="4" hidden>
                            <h5 class="mb-3 text-muted"><i class="fa fa-institution"></i> Hatua 4: Uanachama wa Kanisa</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Member Type <span class="text-danger">*</span></label>
                                        <select class="form-control" name="member_type" required>
                                            <option value="member" {{ old('member_type', 'member') === 'member' ? 'selected' : '' }}>Full Member</option>
                                            <option value="visitor" {{ old('member_type') === 'visitor' ? 'selected' : '' }}>Visitor</option>
                                            <option value="new_convert" {{ old('member_type') === 'new_convert' ? 'selected' : '' }}>New Convert</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Department / Ministry</label>
                                        <select class="form-control" name="department_id">
                                            <option value="">-- Hakuna Idara --</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                                    {{ $dept->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Date Joined Church</label>
                                        <input class="form-control" type="date" name="date_joined" value="{{ old('date_joined') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Je, amebatizwa? <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio" name="is_baptized" value="1"
                                                        {{ old('is_baptized') === '1' ? 'checked' : '' }}>
                                                    Ndiyo
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio" name="is_baptized" value="0"
                                                        {{ old('is_baptized', '0') === '0' ? 'checked' : '' }}>
                                                    Hapana
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" id="baptismDateWrap" hidden>
                                    <div class="form-group">
                                        <label class="control-label">Tarehe ya Ubatizo <small class="text-muted">(si lazima)</small></label>
                                        <input class="form-control" type="date" name="baptism_date" id="baptismDateInput"
                                            value="{{ old('baptism_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">Notes</label>
                                        <textarea class="form-control" name="notes" rows="3"
                                            placeholder="Maelezo ya ziada">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <a href="{{ route('members.index') }}" class="btn btn-secondary mb-2">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                            <div class="mb-2">
                                <button type="button" class="btn btn-outline-secondary" id="wizardPrevBtn" hidden>
                                    <i class="fa fa-arrow-left"></i> Rudi
                                </button>
                                <button type="button" class="btn btn-primary" id="wizardNextBtn">
                                    Endelea <i class="fa fa-arrow-right"></i>
                                </button>
                                <button type="submit" class="btn btn-success" id="wizardSubmitBtn" hidden>
                                    <i class="fa fa-check-circle"></i> Register Member
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .member-wizard-nav {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }
    .member-wizard-step {
        flex: 1;
        min-width: 120px;
        text-align: center;
        padding: 12px 8px;
        border-radius: 4px;
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    .member-wizard-step .step-num {
        display: inline-block;
        width: 28px;
        height: 28px;
        line-height: 26px;
        border-radius: 50%;
        background: #dee2e6;
        color: #495057;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .member-wizard-step .step-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
    }
    .member-wizard-step.active {
        border-color: #009688;
        background: #e0f2f1;
        color: #00796b;
    }
    .member-wizard-step.active .step-num {
        background: #009688;
        color: #fff;
    }
    .member-wizard-step.done {
        border-color: #28a745;
        background: #f1f9f3;
        color: #155724;
    }
    .member-wizard-step.done .step-num {
        background: #28a745;
        color: #fff;
    }
    .wizard-panel[hidden] {
        display: none !important;
    }
    .form-control.is-invalid-step {
        border-color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var tzLocations = @json(config('tanzania_locations.regions'));

    function toggleBaptismDate() {
        var baptized = document.querySelector('input[name="is_baptized"]:checked');
        var wrap = document.getElementById('baptismDateWrap');
        var input = document.getElementById('baptismDateInput');
        var isYes = baptized && baptized.value === '1';

        if (wrap) wrap.hidden = !isYes;
        if (input && !isYes) {
            input.removeAttribute('required');
            input.value = '';
        }
    }

    document.querySelectorAll('input[name="is_baptized"]').forEach(function (radio) {
        radio.addEventListener('change', toggleBaptismDate);
    });
    toggleBaptismDate();

    document.querySelectorAll('.tz-mkoa-select').forEach(function (mkoaSelect) {
        var wilayaSelect = document.getElementById(mkoaSelect.getAttribute('data-wilaya-target'));
        if (!wilayaSelect) return;

        function fillWilaya() {
            var region = mkoaSelect.value;
            var savedWilaya = mkoaSelect.getAttribute('data-old-wilaya') || '';

            wilayaSelect.innerHTML = '<option value="">-- Chagua Wilaya --</option>';

            if (!region || !tzLocations[region]) {
                wilayaSelect.innerHTML = '<option value="">-- Chagua Mkoa kwanza --</option>';
                return;
            }

            tzLocations[region].forEach(function (district) {
                var opt = document.createElement('option');
                opt.value = district;
                opt.textContent = district;
                if (district === savedWilaya) {
                    opt.selected = true;
                }
                wilayaSelect.appendChild(opt);
            });
        }

        mkoaSelect.addEventListener('change', function () {
            mkoaSelect.setAttribute('data-old-wilaya', '');
            fillWilaya();
        });

        if (mkoaSelect.value) {
            fillWilaya();
        }
    });
})();

(function () {
    var totalSteps = 4;
    var currentStep = {{ $errorStep }};
    var form = document.getElementById('memberWizardForm');
    var panels = form.querySelectorAll('.wizard-panel');
    var navSteps = document.querySelectorAll('[data-step-nav]');
    var prevBtn = document.getElementById('wizardPrevBtn');
    var nextBtn = document.getElementById('wizardNextBtn');
    var submitBtn = document.getElementById('wizardSubmitBtn');
    var stepText = document.getElementById('stepIndicatorText');

    function showStep(step) {
        currentStep = step;
        panels.forEach(function (panel) {
            panel.hidden = parseInt(panel.getAttribute('data-step'), 10) !== step;
        });
        navSteps.forEach(function (el) {
            var n = parseInt(el.getAttribute('data-step-nav'), 10);
            el.classList.remove('active', 'done');
            if (n === step) el.classList.add('active');
            else if (n < step) el.classList.add('done');
        });
        prevBtn.hidden = step === 1;
        nextBtn.hidden = step === totalSteps;
        submitBtn.hidden = step !== totalSteps;
        if (stepText) stepText.textContent = step;
    }

    function validateStep(step) {
        var panel = form.querySelector('.wizard-panel[data-step="' + step + '"]');
        var fields = panel.querySelectorAll('input, select, textarea');
        var valid = true;
        fields.forEach(function (field) {
            field.classList.remove('is-invalid-step');
            if (!field.checkValidity()) {
                field.classList.add('is-invalid-step');
                valid = false;
            }
        });
        if (!valid) {
            var first = panel.querySelector('.is-invalid-step');
            if (first) first.focus();
        }
        return valid;
    }

    nextBtn.addEventListener('click', function () {
        if (!validateStep(currentStep)) return;
        if (currentStep < totalSteps) showStep(currentStep + 1);
    });

    prevBtn.addEventListener('click', function () {
        if (currentStep > 1) showStep(currentStep - 1);
    });

    form.addEventListener('submit', function (e) {
        if (!validateStep(currentStep)) {
            e.preventDefault();
            return;
        }
        for (var s = 1; s <= totalSteps; s++) {
            if (!validateStep(s)) {
                e.preventDefault();
                showStep(s);
                return;
            }
        }
    });

    showStep(currentStep);
})();
</script>
@endpush

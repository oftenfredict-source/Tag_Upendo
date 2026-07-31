@extends('layouts.public-form')

@section('title', __('Member Registration'))

@php
    $errorStep = 1;
    $spouseErrors = $errors->hasAny([
        'spouse_is_member', 'spouse_mode', 'existing_spouse_id',
        'spouse_name', 'spouse_phone_number', 'spouse_email', 'spouse_gender',
        'spouse_date_of_birth', 'spouse_occupation', 'spouse_member_type',
        'spouse_department_id', 'spouse_is_baptized', 'spouse_baptism_date', 'spouse_date_joined',
        'spouse_birth_mkoa', 'spouse_birth_wilaya',
    ]);
    if ($spouseErrors || $errors->has('name') || $errors->has('gender') || $errors->has('marital_status') || $errors->has('date_of_birth') || $errors->has('occupation')) {
        $errorStep = 1;
    } elseif ($errors->has('phone_number') || $errors->has('email') || $errors->has('emergency_contact_name') || $errors->has('emergency_contact_phone')) {
        $errorStep = 3;
    } elseif ($errors->has('member_type') || $errors->has('date_joined') || $errors->has('is_baptized') || $errors->has('baptism_date') || $errors->has('department_id') || $errors->has('notes')) {
        $errorStep = 4;
    } elseif ($errors->has('birth_mkoa') || $errors->has('birth_wilaya') || $errors->has('residence_mkoa') || $errors->has('residence_wilaya') || $errors->has('address') || $errors->has('spouse_birth_mkoa') || $errors->has('spouse_birth_wilaya')) {
        $errorStep = 2;
    }
@endphp

@section('content')
    <div class="public-form-page__head mb-3">
        <h2 class="mb-1"><i class="fa fa-user-plus"></i> {{ __('Member Registration') }}</h2>
        <p class="text-muted mb-0">{{ __('Register a new church member') }} — {{ __('step') }} <span id="stepIndicatorText">1</span> {{ __('of') }} 5</p>
    </div>

    <div class="tile public-form-tile">
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
                            <span class="step-label">{{ __('Personal') }}</span>
                        </div>
                        <div class="member-wizard-step" data-step-nav="2">
                            <span class="step-num">2</span>
                            <span class="step-label">{{ __('Location') }}</span>
                        </div>
                        <div class="member-wizard-step" data-step-nav="3">
                            <span class="step-num">3</span>
                            <span class="step-label">{{ __('Contact') }}</span>
                        </div>
                        <div class="member-wizard-step" data-step-nav="4">
                            <span class="step-num">4</span>
                            <span class="step-label">{{ __('Church') }}</span>
                        </div>
                        <div class="member-wizard-step" data-step-nav="5">
                            <span class="step-num">5</span>
                            <span class="step-label">{{ __('Summary') }}</span>
                        </div>
                    </div>

                    <form action="{{ route('register.store', $code) }}" method="POST" id="memberWizardForm" novalidate>
                        @csrf
                        <input type="hidden" name="spouse_mode" value="new">

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
                                        <select class="form-control" name="gender" id="primaryGenderSelect">
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
                                        <label class="control-label">{{ __('Marital Status') }}</label>
                                        <select class="form-control" name="marital_status" id="maritalStatusSelect">
                                            <option value="">-- {{ __('Select') }} --</option>
                                            <option value="single" {{ old('marital_status') === 'single' ? 'selected' : '' }}>{{ __('Single') }}</option>
                                            <option value="married" {{ old('marital_status') === 'married' ? 'selected' : '' }}>{{ __('Married') }}</option>
                                            <option value="widowed" {{ old('marital_status') === 'widowed' ? 'selected' : '' }}>{{ __('Widowed') }}</option>
                                            <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>{{ __('Divorced') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Occupation') }}</label>
                                        <input class="form-control" type="text" name="occupation" value="{{ old('occupation') }}"
                                            placeholder="e.g. Teacher, Business">
                                    </div>
                                </div>
                            </div>

                            {{-- Spouse section (shown when married) --}}
                            <div id="spouseSection" class="spouse-section mt-3" @if(old('marital_status') !== 'married') hidden @endif>
                                <div class="spouse-section-header">
                                    <h6 class="mb-0"><i class="fa fa-heart"></i> {{ __('Spouse information') }}</h6>
                                </div>
                                <div class="spouse-section-body">
                                    <div class="form-group mb-3">
                                        <label class="control-label d-block">{{ __('Is the spouse also a church member?') }} <span class="text-danger">*</span></label>
                                        <div class="mt-1">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio" name="spouse_is_member" value="1"
                                                        id="spouseIsMemberYes"
                                                        {{ old('spouse_is_member') === '1' ? 'checked' : '' }}>
                                                    {{ __('Yes') }}
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio" name="spouse_is_member" value="0"
                                                        id="spouseIsMemberNo"
                                                        {{ old('spouse_is_member', '0') === '0' ? 'checked' : '' }}>
                                                    {{ __('No') }}
                                                </label>
                                            </div>
                                        </div>
                                        @error('spouse_is_member')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div id="spouseMemberOptions" @if(old('spouse_is_member') !== '1') hidden @endif>
                                        <div id="spouseNewFields">
                                            <p class="text-muted small mb-3">{{ __('Fill spouse details — both will be registered and linked as a couple.') }}</p>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Spouse full name') }} <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="text" name="spouse_name" id="spouseNameInput"
                                                            value="{{ old('spouse_name') }}" placeholder="{{ __('Spouse full name') }}">
                                                        @error('spouse_name')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Gender') }}</label>
                                                        <input type="hidden" name="spouse_gender" id="spouseGenderInput" value="{{ old('spouse_gender') }}">
                                                        <p class="form-control-plaintext mb-0" id="spouseGenderHint">
                                                            <span class="text-muted">{{ __('Select primary member gender first') }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Phone') }} <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="text" name="spouse_phone_number" id="spousePhoneInput"
                                                            value="{{ old('spouse_phone_number') }}" placeholder="{{ __('Phone') }}">
                                                        @error('spouse_phone_number')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Email') }}</label>
                                                        <input class="form-control" type="email" name="spouse_email"
                                                            value="{{ old('spouse_email') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Date of Birth') }}</label>
                                                        <input class="form-control" type="date" name="spouse_date_of_birth"
                                                            value="{{ old('spouse_date_of_birth') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Occupation') }}</label>
                                                        <input class="form-control" type="text" name="spouse_occupation"
                                                            value="{{ old('spouse_occupation') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Member Type') }} <span class="text-danger">*</span></label>
                                                        <select class="form-control" name="spouse_member_type" id="spouseMemberType">
                                                            <option value="member" {{ old('spouse_member_type', 'member') === 'member' ? 'selected' : '' }}>{{ __('Full Member') }}</option>
                                                            <option value="visitor" {{ old('spouse_member_type') === 'visitor' ? 'selected' : '' }}>{{ __('Visitor') }}</option>
                                                            <option value="new_convert" {{ old('spouse_member_type') === 'new_convert' ? 'selected' : '' }}>{{ __('New Convert') }}</option>
                                                        </select>
                                                        @error('spouse_member_type')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Department') }}</label>
                                                        <select class="form-control" name="spouse_department_id">
                                                            <option value="">-- {{ __('Select') }} --</option>
                                                            @foreach($departments as $dept)
                                                                <option value="{{ $dept->id }}" {{ old('spouse_department_id') == $dept->id ? 'selected' : '' }}>
                                                                    {{ $dept->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Baptized?') }} <span class="text-danger">*</span></label>
                                                        <div class="mt-2">
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input class="form-check-input" type="radio" name="spouse_is_baptized" value="1"
                                                                        {{ old('spouse_is_baptized') === '1' ? 'checked' : '' }}>
                                                                    {{ __('Yes') }}
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input class="form-check-input" type="radio" name="spouse_is_baptized" value="0"
                                                                        {{ old('spouse_is_baptized', '0') === '0' ? 'checked' : '' }}>
                                                                    {{ __('No') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                        @error('spouse_is_baptized')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6" id="spouseBaptismDateWrap" @if(old('spouse_is_baptized') !== '1') hidden @endif>
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Baptism date') }}</label>
                                                        <input class="form-control" type="date" name="spouse_baptism_date"
                                                            value="{{ old('spouse_baptism_date') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Date Joined Church') }}</label>
                                                        <input class="form-control" type="date" name="spouse_date_joined"
                                                            value="{{ old('spouse_date_joined') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                <i class="fa fa-info-circle"></i>
                                                {{ __('Residence will be copied from the primary member. Spouse birth place is filled in step 2.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Location --}}
                        <div class="wizard-panel" data-step="2" hidden>
                            <h5 class="mb-3 text-muted"><i class="fa fa-map-marker"></i> Hatua 2: Mahali pa Kuzaliwa & Makazi</h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="text-muted mb-2">{{ __('Place of birth') }} — {{ __('Primary member') }}</h6>
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
                                    <h6 class="text-muted mb-2">{{ __('Current residence') }}</h6>
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
                                        <label class="control-label">{{ __('Street / Ward / Details') }}</label>
                                        <input class="form-control" type="text" name="address" value="{{ old('address') }}"
                                            placeholder="Mtaa, kata, au eneo maalum">
                                    </div>
                                </div>
                            </div>

                            <div id="spouseLocationSection" class="spouse-section mt-4" hidden>
                                <div class="spouse-section-header">
                                    <h6 class="mb-0"><i class="fa fa-heart"></i> {{ __('Spouse place of birth') }}</h6>
                                </div>
                                <div class="spouse-section-body">
                                    <p class="text-muted small mb-3">{{ __('Enter the spouse birth region and district.') }}</p>
                                    <div class="row">
                                        @include('partials.region-district-select', [
                                            'mkoaName' => 'spouse_birth_mkoa',
                                            'wilayaName' => 'spouse_birth_wilaya',
                                            'mkoaId' => 'spouse_birth_mkoa',
                                            'wilayaId' => 'spouse_birth_wilaya',
                                            'oldMkoa' => old('spouse_birth_mkoa'),
                                            'oldWilaya' => old('spouse_birth_wilaya'),
                                        ])
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

                        {{-- Step 5: Summary --}}
                        <div class="wizard-panel" data-step="5" hidden>
                            <h5 class="mb-3 text-muted"><i class="fa fa-list-alt"></i> {{ __('Step 5: Review Summary') }}</h5>
                            <p class="text-muted mb-3">{{ __('Please review all information before registering.') }}</p>

                            <div class="summary-card mb-3">
                                <div class="summary-card-header">{{ __('Primary member') }}</div>
                                <div class="summary-card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless summary-table mb-0">
                                                <tr><th>{{ __('Name') }}</th><td id="sum-name">—</td></tr>
                                                <tr><th>{{ __('Gender') }}</th><td id="sum-gender">—</td></tr>
                                                <tr><th>{{ __('Date of Birth') }}</th><td id="sum-dob">—</td></tr>
                                                <tr><th>{{ __('Marital Status') }}</th><td id="sum-marital">—</td></tr>
                                                <tr><th>{{ __('Occupation') }}</th><td id="sum-occupation">—</td></tr>
                                                <tr><th>{{ __('Phone') }}</th><td id="sum-phone">—</td></tr>
                                                <tr><th>{{ __('Email') }}</th><td id="sum-email">—</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless summary-table mb-0">
                                                <tr><th>{{ __('Place of birth') }}</th><td id="sum-birth">—</td></tr>
                                                <tr><th>{{ __('Current residence') }}</th><td id="sum-residence">—</td></tr>
                                                <tr><th>{{ __('Street / Ward / Details') }}</th><td id="sum-address">—</td></tr>
                                                <tr><th>{{ __('Emergency Contact Name') }}</th><td id="sum-emergency-name">—</td></tr>
                                                <tr><th>{{ __('Emergency Contact Phone') }}</th><td id="sum-emergency-phone">—</td></tr>
                                                <tr><th>{{ __('Member Type') }}</th><td id="sum-type">—</td></tr>
                                                <tr><th>{{ __('Department') }}</th><td id="sum-department">—</td></tr>
                                                <tr><th>{{ __('Date Joined Church') }}</th><td id="sum-joined">—</td></tr>
                                                <tr><th>{{ __('Baptized?') }}</th><td id="sum-baptized">—</td></tr>
                                                <tr><th>{{ __('Baptism date') }}</th><td id="sum-baptism-date">—</td></tr>
                                                <tr><th>{{ __('Notes') }}</th><td id="sum-notes">—</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="summarySpouseCard" class="summary-card mb-0" hidden>
                                <div class="summary-card-header">{{ __('Spouse information') }}</div>
                                <div class="summary-card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless summary-table mb-0">
                                                <tr><th>{{ __('Spouse registration') }}</th><td id="sum-spouse-mode">—</td></tr>
                                                <tr><th>{{ __('Name') }}</th><td id="sum-spouse-name">—</td></tr>
                                                <tr><th>{{ __('Gender') }}</th><td id="sum-spouse-gender">—</td></tr>
                                                <tr><th>{{ __('Date of Birth') }}</th><td id="sum-spouse-dob">—</td></tr>
                                                <tr><th>{{ __('Phone') }}</th><td id="sum-spouse-phone">—</td></tr>
                                                <tr><th>{{ __('Email') }}</th><td id="sum-spouse-email">—</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless summary-table mb-0">
                                                <tr><th>{{ __('Occupation') }}</th><td id="sum-spouse-occupation">—</td></tr>
                                                <tr><th>{{ __('Member Type') }}</th><td id="sum-spouse-type">—</td></tr>
                                                <tr><th>{{ __('Department') }}</th><td id="sum-spouse-department">—</td></tr>
                                                <tr><th>{{ __('Baptized?') }}</th><td id="sum-spouse-baptized">—</td></tr>
                                                <tr><th>{{ __('Baptism date') }}</th><td id="sum-spouse-baptism-date">—</td></tr>
                                                <tr><th>{{ __('Date Joined Church') }}</th><td id="sum-spouse-joined">—</td></tr>
                                                <tr><th>{{ __('Spouse place of birth') }}</th><td id="sum-spouse-birth">—</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <a href="{{ route('login') }}" class="btn btn-secondary mb-2">
                                <i class="fa fa-times"></i> {{ __('Cancel') }}
                            </a>
                            <div class="mb-2">
                                <button type="button" class="btn btn-outline-secondary" id="wizardPrevBtn" hidden>
                                    <i class="fa fa-arrow-left"></i> {{ __('Back') }}
                                </button>
                                <button type="button" class="btn btn-primary" id="wizardNextBtn">
                                    {{ __('Continue') }} <i class="fa fa-arrow-right"></i>
                                </button>
                                <button type="submit" class="btn btn-success" id="wizardSubmitBtn" hidden>
                                    <i class="fa fa-check-circle"></i> {{ __('Submit registration request') }}
                                </button>
                            </div>
                        </div>
                    </form>
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
        border-color: #940000;
        background: #f5e6e6;
        color: #700000;
    }
    .member-wizard-step.active .step-num {
        background: #940000;
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
    .spouse-section {
        border: 1px solid #f5e6e6;
        border-radius: 8px;
        overflow: hidden;
        background: #fafefe;
    }
    .spouse-section-header {
        background: linear-gradient(135deg, #940000 0%, #700000 100%);
        color: #fff;
        padding: 10px 14px;
    }
    .spouse-section-body {
        padding: 14px 16px;
    }
    .summary-card {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }
    .summary-card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e8e8e8;
        padding: 10px 14px;
        font-weight: 600;
        color: #2c3e50;
    }
    .summary-card-body {
        padding: 8px 14px 12px;
    }
    .summary-table th {
        width: 42%;
        color: #6c757d;
        font-weight: 500;
        padding: 4px 8px 4px 0;
        vertical-align: top;
    }
    .summary-table td {
        padding: 4px 0;
        font-weight: 600;
        color: #2c3e50;
        word-break: break-word;
    }
    .member-wizard-step {
        min-width: 90px;
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

    function toggleSpouseBaptismDate() {
        var baptized = document.querySelector('input[name="spouse_is_baptized"]:checked');
        var wrap = document.getElementById('spouseBaptismDateWrap');
        var isYes = baptized && baptized.value === '1';
        if (wrap) wrap.hidden = !isYes;
    }

    document.querySelectorAll('input[name="spouse_is_baptized"]').forEach(function (radio) {
        radio.addEventListener('change', toggleSpouseBaptismDate);
    });
    toggleSpouseBaptismDate();

    function setRequired(el, on) {
        if (!el) return;
        if (on) el.setAttribute('required', 'required');
        else el.removeAttribute('required');
    }

    function syncSpouseUi() {
        var marital = document.getElementById('maritalStatusSelect');
        var spouseSection = document.getElementById('spouseSection');
        var spouseOptions = document.getElementById('spouseMemberOptions');
        var existingWrap = document.getElementById('spouseExistingWrap');
        var newFields = document.getElementById('spouseNewFields');
        var isMarried = marital && marital.value === 'married';
        var isMemberYes = document.getElementById('spouseIsMemberYes');
        var modeExisting = document.getElementById('spouseModeExisting');
        var spouseIsMember = isMarried && isMemberYes && isMemberYes.checked;
        var useExisting = spouseIsMember && modeExisting && modeExisting.checked;

        if (spouseSection) spouseSection.hidden = !isMarried;
        if (spouseOptions) spouseOptions.hidden = !spouseIsMember;
        if (existingWrap) existingWrap.hidden = !useExisting;
        if (newFields) newFields.hidden = !spouseIsMember || useExisting;

        var spouseLocation = document.getElementById('spouseLocationSection');
        if (spouseLocation) spouseLocation.hidden = !(spouseIsMember && !useExisting);

        setRequired(document.getElementById('existingSpouseSelect'), useExisting);
        setRequired(document.getElementById('spouseNameInput'), spouseIsMember && !useExisting);
        setRequired(document.getElementById('spousePhoneInput'), spouseIsMember && !useExisting);
        setRequired(document.getElementById('spouseMemberType'), spouseIsMember && !useExisting);

        var spouseBaptizedRadios = document.querySelectorAll('input[name="spouse_is_baptized"]');
        spouseBaptizedRadios.forEach(function (r) {
            if (spouseIsMember && !useExisting) {
                // leave as-is; one should be checked
            } else {
                r.removeAttribute('required');
            }
        });
    }

    var maritalSelect = document.getElementById('maritalStatusSelect');
    if (maritalSelect) maritalSelect.addEventListener('change', syncSpouseUi);
    document.querySelectorAll('input[name="spouse_is_member"], input[name="spouse_mode"]').forEach(function (el) {
        el.addEventListener('change', syncSpouseUi);
    });
    syncSpouseUi();

    function syncSpouseGender() {
        var primary = document.getElementById('primaryGenderSelect');
        var hidden = document.getElementById('spouseGenderInput');
        var hint = document.getElementById('spouseGenderHint');
        if (!primary || !hidden || !hint) return;

        var gender = primary.value;
        var spouseGender = gender === 'male' ? 'female' : (gender === 'female' ? 'male' : '');
        hidden.value = spouseGender;

        if (spouseGender === 'female') {
            hint.innerHTML = '<strong class="text-success">' + @json(__('Female')) + '</strong> <small class="text-muted">(' + @json(__('Auto-set from primary member')) + ')</small>';
        } else if (spouseGender === 'male') {
            hint.innerHTML = '<strong class="text-success">' + @json(__('Male')) + '</strong> <small class="text-muted">(' + @json(__('Auto-set from primary member')) + ')</small>';
        } else {
            hint.innerHTML = '<span class="text-muted">' + @json(__('Select primary member gender first')) + '</span>';
        }
    }

    var primaryGender = document.getElementById('primaryGenderSelect');
    if (primaryGender) primaryGender.addEventListener('change', syncSpouseGender);
    syncSpouseGender();

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
    var totalSteps = 5;
    var currentStep = {{ $errorStep }};
    var form = document.getElementById('memberWizardForm');
    var panels = form.querySelectorAll('.wizard-panel');
    var navSteps = document.querySelectorAll('[data-step-nav]');
    var prevBtn = document.getElementById('wizardPrevBtn');
    var nextBtn = document.getElementById('wizardNextBtn');
    var submitBtn = document.getElementById('wizardSubmitBtn');
    var stepText = document.getElementById('stepIndicatorText');

    var labels = {
        male: @json(__('Male')),
        female: @json(__('Female')),
        single: @json(__('Single')),
        married: @json(__('Married')),
        widowed: @json(__('Widowed')),
        divorced: @json(__('Divorced')),
        member: @json(__('Full Member')),
        visitor: @json(__('Visitor')),
        new_convert: @json(__('New Convert')),
        yes: @json(__('Yes')),
        no: @json(__('No')),
        empty: '—',
        registerNew: @json(__('Register new spouse')),
        linkExisting: @json(__('Link existing member')),
        notChurchMember: @json(__('No'))
    };

    function val(name) {
        var el = form.elements.namedItem(name);
        if (!el) return '';
        if (el instanceof RadioNodeList || (el.length && el[0] && el[0].type === 'radio')) {
            var checked = form.querySelector('input[name="' + name + '"]:checked');
            return checked ? checked.value : '';
        }
        return (el.value || '').trim();
    }

    function selectText(name) {
        var el = form.elements.namedItem(name);
        if (!el || !el.options || el.selectedIndex < 0) return '';
        return (el.options[el.selectedIndex].text || '').trim();
    }

    function display(value, map) {
        if (!value) return labels.empty;
        if (map && map[value]) return map[value];
        return value;
    }

    function place(mkoa, wilaya) {
        if (!mkoa && !wilaya) return labels.empty;
        if (mkoa && wilaya) return mkoa + ' / ' + wilaya;
        return mkoa || wilaya;
    }

    function setText(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text || labels.empty;
    }

    function buildSummary() {
        setText('sum-name', val('name'));
        setText('sum-gender', display(val('gender'), { male: labels.male, female: labels.female }));
        setText('sum-dob', val('date_of_birth'));
        setText('sum-marital', display(val('marital_status'), {
            single: labels.single, married: labels.married, widowed: labels.widowed, divorced: labels.divorced
        }));
        setText('sum-occupation', val('occupation'));
        setText('sum-phone', val('phone_number'));
        setText('sum-email', val('email'));
        setText('sum-birth', place(val('birth_mkoa'), val('birth_wilaya')));
        setText('sum-residence', place(val('residence_mkoa'), val('residence_wilaya')));
        setText('sum-address', val('address'));
        setText('sum-emergency-name', val('emergency_contact_name'));
        setText('sum-emergency-phone', val('emergency_contact_phone'));
        setText('sum-type', display(val('member_type'), {
            member: labels.member, visitor: labels.visitor, new_convert: labels.new_convert
        }));
        var dept = selectText('department_id');
        setText('sum-department', (!dept || dept.indexOf('--') === 0) ? labels.empty : dept);
        setText('sum-joined', val('date_joined'));
        var baptized = val('is_baptized');
        setText('sum-baptized', baptized === '1' ? labels.yes : (baptized === '0' ? labels.no : labels.empty));
        setText('sum-baptism-date', baptized === '1' ? val('baptism_date') : labels.empty);
        setText('sum-notes', val('notes'));

        var spouseCard = document.getElementById('summarySpouseCard');
        var isMarried = val('marital_status') === 'married';
        var spouseIsMember = val('spouse_is_member') === '1';

        if (!isMarried || !spouseIsMember) {
            if (spouseCard) spouseCard.hidden = true;
            return;
        }

        if (spouseCard) spouseCard.hidden = false;
        var mode = val('spouse_mode');
        setText('sum-spouse-mode', mode === 'existing' ? labels.linkExisting : labels.registerNew);

        if (mode === 'existing') {
            var existing = selectText('existing_spouse_id');
            setText('sum-spouse-name', (!existing || existing.indexOf('--') === 0) ? labels.empty : existing);
            setText('sum-spouse-gender', labels.empty);
            setText('sum-spouse-dob', labels.empty);
            setText('sum-spouse-phone', labels.empty);
            setText('sum-spouse-email', labels.empty);
            setText('sum-spouse-occupation', labels.empty);
            setText('sum-spouse-type', labels.empty);
            setText('sum-spouse-department', labels.empty);
            setText('sum-spouse-baptized', labels.empty);
            setText('sum-spouse-baptism-date', labels.empty);
            setText('sum-spouse-joined', labels.empty);
            setText('sum-spouse-birth', labels.empty);
            return;
        }

        setText('sum-spouse-name', val('spouse_name'));
        setText('sum-spouse-gender', display(val('spouse_gender'), { male: labels.male, female: labels.female }));
        setText('sum-spouse-dob', val('spouse_date_of_birth'));
        setText('sum-spouse-phone', val('spouse_phone_number'));
        setText('sum-spouse-email', val('spouse_email'));
        setText('sum-spouse-occupation', val('spouse_occupation'));
        setText('sum-spouse-type', display(val('spouse_member_type'), {
            member: labels.member, visitor: labels.visitor, new_convert: labels.new_convert
        }));
        var spouseDept = selectText('spouse_department_id');
        setText('sum-spouse-department', (!spouseDept || spouseDept.indexOf('--') === 0) ? labels.empty : spouseDept);
        var spouseBaptized = val('spouse_is_baptized');
        setText('sum-spouse-baptized', spouseBaptized === '1' ? labels.yes : (spouseBaptized === '0' ? labels.no : labels.empty));
        setText('sum-spouse-baptism-date', spouseBaptized === '1' ? val('spouse_baptism_date') : labels.empty);
        setText('sum-spouse-joined', val('spouse_date_joined'));
        setText('sum-spouse-birth', place(val('spouse_birth_mkoa'), val('spouse_birth_wilaya')));
    }

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
        if (step === 5) buildSummary();
    }

    function validateStep(step) {
        if (step === 5) return true;
        var panel = form.querySelector('.wizard-panel[data-step="' + step + '"]');
        var fields = panel.querySelectorAll('input, select, textarea');
        var valid = true;
        fields.forEach(function (field) {
            field.classList.remove('is-invalid-step');
            if (field.disabled || field.closest('[hidden]')) return;
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
        for (var s = 1; s <= 4; s++) {
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

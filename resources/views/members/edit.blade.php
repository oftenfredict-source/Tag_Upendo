@extends('layouts.app')

@section('title', __('Edit Member'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-pencil"></i> {{ __('Edit Member') }}</h1>
            <p>{{ $member->name }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">{{ __('Members') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('members.show', $member) }}">{{ __('View') }}</a></li>
            <li class="breadcrumb-item">{{ __('Edit') }}</li>
        </ul>
    </div>

    <div class="tile">
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

            <form action="{{ route('members.update', $member) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $member->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('Member Type') }} <span class="text-danger">*</span></label>
                            <select name="member_type" class="form-control" required>
                                <option value="member" @selected(old('member_type', $member->member_type) === 'member')>{{ __('Full Member') }}</option>
                                <option value="visitor" @selected(old('member_type', $member->member_type) === 'visitor')>{{ __('Visitor') }}</option>
                                <option value="new_convert" @selected(old('member_type', $member->member_type) === 'new_convert')>{{ __('New Convert') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ __('Phone') }}</label>
                            <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $member->phone_number) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ __('Gender') }}</label>
                            <select name="gender" class="form-control">
                                <option value="">--</option>
                                <option value="male" @selected(old('gender', $member->gender) === 'male')>{{ __('Male') }}</option>
                                <option value="female" @selected(old('gender', $member->gender) === 'female')>{{ __('Female') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ __('Date of Birth') }}</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $member->date_of_birth?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ __('Marital Status') }}</label>
                            <select name="marital_status" class="form-control">
                                <option value="">--</option>
                                @foreach(['single', 'married', 'widowed', 'divorced'] as $status)
                                    <option value="{{ $status }}" @selected(old('marital_status', $member->marital_status) === $status)>{{ __(ucfirst($status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ __('Department') }}</label>
                            <select name="department_id" class="form-control">
                                <option value="">--</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('department_id', $member->department_id) == $dept->id)>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ __('Date Joined Church') }}</label>
                            <input type="date" name="date_joined" class="form-control" value="{{ old('date_joined', $member->date_joined?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ __('Occupation') }}</label>
                            <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $member->occupation) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ __('Baptized?') }} <span class="text-danger">*</span></label>
                            <div class="mt-2">
                                <label class="mr-3"><input type="radio" name="is_baptized" value="1" @checked(old('is_baptized', $member->is_baptized ? '1' : '0') === '1')> {{ __('Yes') }}</label>
                                <label><input type="radio" name="is_baptized" value="0" @checked(old('is_baptized', $member->is_baptized ? '1' : '0') === '0')> {{ __('No') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ __('Baptism date') }}</label>
                            <input type="date" name="baptism_date" class="form-control" value="{{ old('baptism_date', $member->baptism_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        @include('partials.region-district-select', [
                            'mkoaName' => 'residence_mkoa',
                            'wilayaName' => 'residence_wilaya',
                            'mkoaId' => 'residence_mkoa',
                            'wilayaId' => 'residence_wilaya',
                            'oldMkoa' => old('residence_mkoa', $member->residence_mkoa),
                            'oldWilaya' => old('residence_wilaya', $member->residence_wilaya),
                        ])
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('Street / Ward / Details') }}</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $member->address) }}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Emergency Contact Name') }}</label>
                            <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $member->emergency_contact_name) }}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Emergency Contact Phone') }}</label>
                            <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $member->emergency_contact_phone) }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label class="control-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $member->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('members.show', $member) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var tzLocations = @json(config('tanzania_locations.regions'));
    document.querySelectorAll('.tz-mkoa-select').forEach(function (mkoaSelect) {
        var wilayaSelect = document.getElementById(mkoaSelect.getAttribute('data-wilaya-target'));
        if (!wilayaSelect) return;
        function fillWilaya() {
            var region = mkoaSelect.value;
            var savedWilaya = mkoaSelect.getAttribute('data-old-wilaya') || '';
            wilayaSelect.innerHTML = '<option value="">--</option>';
            if (!region || !tzLocations[region]) return;
            tzLocations[region].forEach(function (district) {
                var opt = document.createElement('option');
                opt.value = district;
                opt.textContent = district;
                if (district === savedWilaya) opt.selected = true;
                wilayaSelect.appendChild(opt);
            });
        }
        mkoaSelect.addEventListener('change', function () {
            mkoaSelect.setAttribute('data-old-wilaya', '');
            fillWilaya();
        });
        if (mkoaSelect.value) fillWilaya();
    });
})();
</script>
@endpush

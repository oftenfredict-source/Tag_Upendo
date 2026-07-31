@php
    $oldChildren = old('children', []);
    if (! is_array($oldChildren) || count($oldChildren) === 0) {
        $oldChildren = [['name' => '', 'gender' => '', 'date_of_birth' => '']];
    }
    $minChildDob = now()->subYears(\App\Models\Member::MAX_CHILD_AGE)->format('Y-m-d');
    $maxChildDob = now()->format('Y-m-d');
@endphp

<div id="childrenSection" class="spouse-section mt-3">
    <div class="spouse-section-header">
        <h6 class="mb-0"><i class="fa fa-child"></i> {{ __('Children') }}</h6>
    </div>
    <div class="spouse-section-body">
        <div class="form-group mb-3">
            <label class="control-label d-block">{{ __('Do you have children to register?') }}</label>
            <div class="mt-1">
                <div class="form-check form-check-inline">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="has_children" value="1"
                            id="hasChildrenYes"
                            {{ old('has_children') === '1' ? 'checked' : '' }}>
                        {{ __('Yes') }}
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="has_children" value="0"
                            id="hasChildrenNo"
                            {{ old('has_children', '0') === '0' ? 'checked' : '' }}>
                        {{ __('No') }}
                    </label>
                </div>
            </div>
            @error('has_children')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div id="childrenListWrap" @if(old('has_children') !== '1') hidden @endif>
            <p class="text-muted small mb-3">{{ __('Enter each child\'s name, gender and date of birth. Age is calculated automatically.') }}</p>

            <div id="childrenRows">
                @foreach($oldChildren as $index => $child)
                    @php
                        $childDob = $child['date_of_birth'] ?? '';
                        if ($childDob === '' && ! empty($child['birth_year'])) {
                            $childDob = $child['birth_year'].'-01-01';
                        }
                    @endphp
                    <div class="child-row border rounded p-3 mb-2" data-child-row>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-muted small child-row-label">{{ __('Child') }} <span class="child-row-num">{{ $loop->iteration }}</span></strong>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-child-row" @if($loop->first && count($oldChildren) === 1) hidden @endif>
                                <i class="fa fa-times"></i> {{ __('Remove') }}
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="control-label">{{ __('Full name') }}</label>
                                    <input class="form-control child-name-input" type="text"
                                        name="children[{{ $index }}][name]"
                                        value="{{ $child['name'] ?? '' }}"
                                        placeholder="{{ __('Child full name') }}">
                                    @error('children.'.$index.'.name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="control-label">{{ __('Gender') }}</label>
                                    <select class="form-control" name="children[{{ $index }}][gender]">
                                        <option value="">-- {{ __('Select') }} --</option>
                                        <option value="male" {{ ($child['gender'] ?? '') === 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                        <option value="female" {{ ($child['gender'] ?? '') === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="control-label">{{ __('Date of Birth') }}</label>
                                    <input class="form-control child-dob-input" type="date"
                                        name="children[{{ $index }}][date_of_birth]"
                                        value="{{ $childDob }}"
                                        min="{{ $minChildDob }}"
                                        max="{{ $maxChildDob }}">
                                    @error('children.'.$index.'.date_of_birth')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="control-label">{{ __('Age') }}</label>
                                    <p class="form-control-plaintext mb-0 child-age-display text-primary font-weight-bold">—</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-sm btn-outline-primary" id="addChildRowBtn">
                <i class="fa fa-plus"></i> {{ __('Add another child') }}
            </button>
        </div>
    </div>
</div>

<template id="childRowTemplate">
    <div class="child-row border rounded p-3 mb-2" data-child-row>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-muted small child-row-label">{{ __('Child') }} <span class="child-row-num">1</span></strong>
            <button type="button" class="btn btn-sm btn-outline-danger remove-child-row">
                <i class="fa fa-times"></i> {{ __('Remove') }}
            </button>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="control-label">{{ __('Full name') }}</label>
                    <input class="form-control child-name-input" type="text" name="children[__INDEX__][name]"
                        placeholder="{{ __('Child full name') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-2">
                    <label class="control-label">{{ __('Gender') }}</label>
                    <select class="form-control" name="children[__INDEX__][gender]">
                        <option value="">-- {{ __('Select') }} --</option>
                        <option value="male">{{ __('Male') }}</option>
                        <option value="female">{{ __('Female') }}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-2">
                    <label class="control-label">{{ __('Date of Birth') }}</label>
                    <input class="form-control child-dob-input" type="date"
                        name="children[__INDEX__][date_of_birth]"
                        min="{{ $minChildDob }}"
                        max="{{ $maxChildDob }}">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-2">
                    <label class="control-label">{{ __('Age') }}</label>
                    <p class="form-control-plaintext mb-0 child-age-display text-primary font-weight-bold">—</p>
                </div>
            </div>
        </div>
    </div>
</template>

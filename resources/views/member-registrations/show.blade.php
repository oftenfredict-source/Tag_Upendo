@extends('layouts.app')

@section('title', __('Review registration'))

@php
    $p = $payload;
    $deptName = isset($p['department_id']) ? ($departments[$p['department_id']] ?? '—') : '—';
    $spouseDeptName = isset($p['spouse_department_id']) ? ($departments[$p['spouse_department_id']] ?? '—') : '—';

    $genderLabel = fn ($g) => match ($g) {
        'male' => __('Male'),
        'female' => __('Female'),
        default => '—',
    };
    $maritalLabel = fn ($m) => match ($m) {
        'single' => __('Single'),
        'married' => __('Married'),
        'widowed' => __('Widowed'),
        'divorced' => __('Divorced'),
        default => '—',
    };
    $memberTypeLabel = fn ($t) => match ($t) {
        'member' => __('Full Member'),
        'visitor' => __('Visitor'),
        'new_convert' => __('New Convert'),
        default => '—',
    };
    $yesNo = fn ($v) => ($v === true || $v === 1 || $v === '1') ? __('Yes') : __('No');
    $place = fn ($mkoa, $wilaya) => trim(($mkoa ?? '') . ($mkoa && $wilaya ? ' / ' : '') . ($wilaya ?? '')) ?: '—';
@endphp

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-user-plus"></i> {{ __('Review registration') }}</h1>
            <p>{{ $registrationRequest->applicant_name }} — {{ $registrationRequest->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('member-registrations.index') }}">{{ __('Member registrations') }}</a></li>
            <li class="breadcrumb-item">{{ __('Review') }}</li>
        </ul>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">{{ __('Applicant details') }}</h3>
                    <p class="mb-0">
                        <span class="badge badge-{{ \App\Models\MemberRegistrationRequest::statusBadge($registrationRequest->status) }}">
                            {{ \App\Models\MemberRegistrationRequest::statusLabel($registrationRequest->status) }}
                        </span>
                    </p>
                </div>
                <div class="tile-body">
                    <h5 class="text-muted mb-3">{{ __('Personal information') }}</h5>
                    <table class="table table-sm table-borderless">
                        <tr><th width="35%">{{ __('Name') }}</th><td>{{ $p['name'] ?? '—' }}</td></tr>
                        <tr><th>{{ __('Gender') }}</th><td>{{ $genderLabel($p['gender'] ?? null) }}</td></tr>
                        <tr><th>{{ __('Date of Birth') }}</th><td>{{ $p['date_of_birth'] ?? '—' }}</td></tr>
                        <tr><th>{{ __('Marital Status') }}</th><td>{{ $maritalLabel($p['marital_status'] ?? null) }}</td></tr>
                        <tr><th>{{ __('Occupation') }}</th><td>{{ $p['occupation'] ?? '—' }}</td></tr>
                        <tr><th>{{ __('Phone') }}</th><td>{{ $p['phone_number'] ?? '—' }}</td></tr>
                        <tr><th>{{ __('Email') }}</th><td>{{ $p['email'] ?? '—' }}</td></tr>
                        <tr><th>{{ __('Place of birth') }}</th><td>{{ $place($p['birth_mkoa'] ?? null, $p['birth_wilaya'] ?? null) }}</td></tr>
                        <tr><th>{{ __('Current residence') }}</th><td>{{ $place($p['residence_mkoa'] ?? null, $p['residence_wilaya'] ?? null) }}</td></tr>
                        <tr><th>{{ __('Street / Ward / Details') }}</th><td>{{ $p['address'] ?? '—' }}</td></tr>
                        <tr><th>{{ __('Emergency Contact Name') }}</th><td>{{ $p['emergency_contact_name'] ?? '—' }}</td></tr>
                        <tr><th>{{ __('Emergency Contact Phone') }}</th><td>{{ $p['emergency_contact_phone'] ?? '—' }}</td></tr>
                        <tr><th>{{ __('Member Type') }}</th><td>{{ $memberTypeLabel($p['member_type'] ?? null) }}</td></tr>
                        <tr><th>{{ __('Department') }}</th><td>{{ $deptName }}</td></tr>
                        <tr><th>{{ __('Date Joined Church') }}</th><td>{{ $p['date_joined'] ?? '—' }}</td></tr>
                        <tr><th>{{ __('Baptized?') }}</th><td>{{ $yesNo($p['is_baptized'] ?? false) }}</td></tr>
                        <tr><th>{{ __('Baptism date') }}</th><td>{{ $p['baptism_date'] ?? '—' }}</td></tr>
                        <tr><th>{{ __('Notes') }}</th><td>{{ $p['notes'] ?? '—' }}</td></tr>
                    </table>

                    @if(($p['marital_status'] ?? null) === 'married' && ($p['spouse_is_member'] ?? null) === '1')
                        <hr>
                        <h5 class="text-muted mb-3">{{ __('Spouse information') }}</h5>
                        <table class="table table-sm table-borderless">
                            <tr><th width="35%">{{ __('Name') }}</th><td>{{ $p['spouse_name'] ?? '—' }}</td></tr>
                            <tr><th>{{ __('Phone') }}</th><td>{{ $p['spouse_phone_number'] ?? '—' }}</td></tr>
                            <tr><th>{{ __('Email') }}</th><td>{{ $p['spouse_email'] ?? '—' }}</td></tr>
                            <tr><th>{{ __('Date of Birth') }}</th><td>{{ $p['spouse_date_of_birth'] ?? '—' }}</td></tr>
                            <tr><th>{{ __('Occupation') }}</th><td>{{ $p['spouse_occupation'] ?? '—' }}</td></tr>
                            <tr><th>{{ __('Member Type') }}</th><td>{{ $memberTypeLabel($p['spouse_member_type'] ?? null) }}</td></tr>
                            <tr><th>{{ __('Department') }}</th><td>{{ $spouseDeptName }}</td></tr>
                            <tr><th>{{ __('Baptized?') }}</th><td>{{ $yesNo($p['spouse_is_baptized'] ?? false) }}</td></tr>
                            <tr><th>{{ __('Baptism date') }}</th><td>{{ $p['spouse_baptism_date'] ?? '—' }}</td></tr>
                            <tr><th>{{ __('Date Joined Church') }}</th><td>{{ $p['spouse_date_joined'] ?? '—' }}</td></tr>
                            <tr><th>{{ __('Spouse place of birth') }}</th><td>{{ $place($p['spouse_birth_mkoa'] ?? null, $p['spouse_birth_wilaya'] ?? null) }}</td></tr>
                        </table>
                    @endif

                    @php
                        $registrationChildren = \App\Services\MemberRegistrationService::normalizeChildrenInput($p);
                    @endphp
                    @if(count($registrationChildren))
                        <hr>
                        <h5 class="text-muted mb-3">{{ __('Children') }}</h5>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Gender') }}</th>
                                    <th>{{ __('Date of Birth') }}</th>
                                    <th>{{ __('Age') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registrationChildren as $child)
                                    @php
                                        $childAge = ! empty($child['date_of_birth'])
                                            ? \App\Services\MemberRegistrationService::childAgeFromDate($child['date_of_birth'])
                                            : null;
                                        $childDobDisplay = ! empty($child['date_of_birth'])
                                            ? \Carbon\Carbon::parse($child['date_of_birth'])->format('d/m/Y')
                                            : '—';
                                    @endphp
                                    <tr>
                                        <td>{{ $child['name'] }}</td>
                                        <td>{{ $genderLabel($child['gender'] ?? null) }}</td>
                                        <td>{{ $childDobDisplay }}</td>
                                        <td>
                                            @if($childAge !== null)
                                                @if($childAge === 1)
                                                    {{ __(':count year', ['count' => 1]) }}
                                                @else
                                                    {{ __(':count years', ['count' => $childAge]) }}
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($registrationRequest->isPending())
                <div class="tile">
                    <h3 class="tile-title">{{ __('Pastor verification') }}</h3>
                    <div class="tile-body">
                        <p class="text-muted">{{ __('After verification, the member will be added to the official member list with a login account.') }}</p>

                        <form action="{{ route('member-registrations.approve', $registrationRequest) }}" method="POST" class="mb-3"
                            onsubmit="return confirm(@json(__('Approve this registration and add the member to the list?')));">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fa fa-check"></i> {{ __('Approve & register member') }}
                            </button>
                        </form>

                        <form action="{{ route('member-registrations.reject', $registrationRequest) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="control-label">{{ __('Rejection reason') }} <small class="text-muted">({{ __('optional') }})</small></label>
                                <textarea class="form-control" name="rejection_reason" rows="3"
                                    placeholder="{{ __('Reason for rejection (internal)') }}"></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger btn-block"
                                onclick="return confirm(@json(__('Reject this registration request?')));">
                                <i class="fa fa-times"></i> {{ __('Reject request') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="tile">
                    <h3 class="tile-title">{{ __('Review outcome') }}</h3>
                    <div class="tile-body">
                        <p><strong>{{ __('Status') }}:</strong>
                            {{ \App\Models\MemberRegistrationRequest::statusLabel($registrationRequest->status) }}
                        </p>
                        @if($registrationRequest->reviewer)
                            <p><strong>{{ __('Reviewed by') }}:</strong> {{ $registrationRequest->reviewer->name }}</p>
                            <p><strong>{{ __('Reviewed at') }}:</strong> {{ $registrationRequest->reviewed_at?->format('d/m/Y H:i') }}</p>
                        @endif
                        @if($registrationRequest->rejection_reason)
                            <p><strong>{{ __('Rejection reason') }}:</strong><br>{{ $registrationRequest->rejection_reason }}</p>
                        @endif
                        @if($registrationRequest->member)
                            <a href="{{ route('members.show', $registrationRequest->member) }}" class="btn btn-primary btn-block mt-3">
                                <i class="fa fa-user"></i> {{ __('View member profile') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

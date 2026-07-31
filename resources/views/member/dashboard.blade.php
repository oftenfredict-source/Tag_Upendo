@extends('layouts.app')

@section('title', __('My Dashboard'))

@section('content')
    @php
        $activeTab = $tab ?? 'overview';
        $portalRoute = $portalRouteName ?? auth()->user()->memberPortalRouteName();
    @endphp

    <div class="app-title">
        <div>
            <h1><i class="fa fa-user"></i> {{ __('My Dashboard') }}</h1>
            <p>{{ __('Welcome') }}, {{ $user->name }} — {{ $appChurchName ?? 'TAG Upendo' }}</p>
        </div>
    </div>

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">{{ session('info') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(!$member)
        <div class="alert alert-warning">
            {{ __('Your login is not linked to a member profile. Please contact the church office.') }}
        </div>
    @else
    <div class="tile">
        <div class="tile-body">
            <ul class="nav nav-tabs member-portal-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" href="{{ route($portalRoute, ['tab' => 'overview']) }}">
                        <i class="fa fa-home"></i> {{ __('Overview') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'contributions' ? 'active' : '' }}" href="{{ route($portalRoute, ['tab' => 'contributions']) }}">
                        <i class="fa fa-money"></i> {{ __('My contributions') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'leaders' ? 'active' : '' }}" href="{{ route($portalRoute, ['tab' => 'leaders']) }}">
                        <i class="fa fa-id-badge"></i> {{ __('Church leaders') }}
                    </a>
                </li>
                @if(auth()->user()->isMember() || auth()->user()->isSecretary())
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'requests' ? 'active' : '' }}" href="{{ route($portalRoute, ['tab' => 'requests']) }}">
                        <i class="fa fa-hand-paper-o"></i> {{ __('Service requests') }}
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'account' ? 'active' : '' }}" href="{{ route($portalRoute, ['tab' => 'account']) }}">
                        <i class="fa fa-cog"></i> {{ __('My account') }}
                    </a>
                </li>
            </ul>

            {{-- Overview --}}
            @if($activeTab === 'overview')
                @include('partials.announcements-feed')

                <div class="row mb-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="widget-small primary coloured-icon">
                            <i class="icon fa fa-id-card fa-3x"></i>
                            <div class="info">
                                <h4>{{ __('Member ID') }}</h4>
                                <p><b>{{ $member->member_code }}</b></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="widget-small info coloured-icon">
                            <i class="icon fa fa-building fa-3x"></i>
                            <div class="info">
                                <h4>{{ __('Department') }}</h4>
                                <p><b>{{ $member->department->name ?? __('No department') }}</b></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="widget-small warning coloured-icon">
                            <i class="icon fa fa-money fa-3x"></i>
                            <div class="info">
                                <h4>{{ __('Tithes paid') }}</h4>
                                <p><b>TSH {{ number_format($contributionStats['tithes_total'], 0) }}</b></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="widget-small danger coloured-icon">
                            <i class="icon fa fa-handshake-o fa-3x"></i>
                            <div class="info">
                                <h4>{{ __('Pledges remaining') }}</h4>
                                <p><b>TSH {{ number_format($contributionStats['pledges_remaining'], 0) }}</b></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <h4><i class="fa fa-user"></i> {{ __('My information') }}</h4>
                        <table class="table table-bordered">
                            <tr><th width="35%">{{ __('Name') }}</th><td>{{ $member->name }}</td></tr>
                            <tr><th>{{ __('Phone') }}</th><td>{{ $member->phone_number ?? '—' }}</td></tr>
                            <tr><th>{{ __('Email') }}</th><td>{{ $member->email ?? '—' }}</td></tr>
                            <tr><th>{{ __('Gender') }}</th><td>{{ $member->gender ? ucfirst($member->gender) : '—' }}</td></tr>
                            <tr><th>{{ __('Marital status') }}</th><td>{{ ucfirst($member->marital_status ?? '—') }}</td></tr>
                            <tr><th>{{ __('Date joined') }}</th><td>{{ $member->date_joined?->format('d/m/Y') ?? '—' }}</td></tr>
                            <tr><th>{{ __('Baptized') }}</th><td>{{ $member->is_baptized ? __('Yes') : __('No') }}</td></tr>
                            @if($member->spouse)
                                <tr><th>{{ __('Spouse') }}</th><td>{{ $member->spouse->name }}</td></tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <h4><i class="fa fa-map-marker"></i> {{ __('Location & contact') }}</h4>
                        <table class="table table-bordered">
                            <tr><th width="35%">{{ __('Residence') }}</th>
                                <td>{{ collect([$member->residence_mkoa, $member->residence_wilaya, $member->address])->filter()->implode(', ') ?: '—' }}</td></tr>
                            <tr><th>{{ __('Occupation') }}</th><td>{{ $member->occupation ?? '—' }}</td></tr>
                            <tr><th>{{ __('Emergency contact') }}</th>
                                <td>{{ $member->emergency_contact_name ?? '—' }} {{ $member->emergency_contact_phone ? '('.$member->emergency_contact_phone.')' : '' }}</td></tr>
                        </table>
                        <a href="{{ route($portalRoute, ['tab' => 'account']) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-pencil"></i> {{ __('Update phone & password') }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Contributions --}}
            @if($activeTab === 'contributions')
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-light text-center">
                            <small class="text-muted d-block">{{ __('Total tithes') }}</small>
                            <strong class="text-success">TSH {{ number_format($contributionStats['tithes_total'], 0) }}</strong>
                            <small class="d-block text-muted">{{ $contributionStats['tithes_count'] }} {{ __('records') }}</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-light text-center">
                            <small class="text-muted d-block">{{ __('Pledges paid') }}</small>
                            <strong class="text-primary">TSH {{ number_format($contributionStats['pledges_paid'], 0) }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-light text-center">
                            <small class="text-muted d-block">{{ __('Pledges remaining') }}</small>
                            <strong class="text-danger">TSH {{ number_format($contributionStats['pledges_remaining'], 0) }}</strong>
                        </div>
                    </div>
                </div>

                <h4 class="mb-3"><i class="fa fa-book"></i> {{ __('Tithes') }}</h4>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th class="text-right">{{ __('Amount') }}</th>
                                <th>{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tithes as $tithe)
                                <tr>
                                    <td>{{ $tithe->payment_date->format('d/m/Y') }}</td>
                                    <td class="text-right"><strong>TSH {{ number_format($tithe->amount, 0) }}</strong></td>
                                    <td>{{ $tithe->notes ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">{{ __('No tithe records yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h4 class="mb-3"><i class="fa fa-handshake-o"></i> {{ __('Pledges') }}</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Pledge for') }}</th>
                                <th class="text-right">{{ __('Total') }}</th>
                                <th class="text-right">{{ __('Paid') }}</th>
                                <th class="text-right">{{ __('Remaining') }}</th>
                                <th>{{ __('Due') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pledges as $pledge)
                                <tr>
                                    <td>{{ $pledge->pledge_for }}</td>
                                    <td class="text-right">{{ number_format($pledge->amount, 0) }}</td>
                                    <td class="text-right text-success">{{ number_format($pledge->amount_paid, 0) }}</td>
                                    <td class="text-right text-danger">{{ number_format($pledge->remainingAmount(), 0) }}</td>
                                    <td>{{ $pledge->due_date?->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ \App\Models\Pledge::statusBadge($pledge->status) }}">
                                            {{ \App\Models\Pledge::statusLabel($pledge->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No pledges yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Leaders --}}
            @if($activeTab === 'leaders')
                <p class="text-muted mb-4">{{ __('Church leadership team — contact them when you need spiritual support.') }}</p>
                @forelse($leaderRoles as $role)
                    <div class="mb-4">
                        <h5 class="mb-2"><i class="fa fa-star text-warning"></i> {{ $role->label() }}</h5>
                        <div class="row">
                            @foreach($role->members as $leader)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="p-3 border rounded h-100">
                                        <strong>{{ $leader->name }}</strong>
                                        @if($leader->phone_number)
                                            <br><small><i class="fa fa-phone"></i> {{ $leader->phone_number }}</small>
                                        @endif
                                        @if($leader->department)
                                            <br><small class="text-muted">{{ $leader->department->name }}</small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4">{{ __('No leaders listed yet.') }}</p>
                @endforelse
            @endif

            {{-- Service requests (members only) --}}
            @if($activeTab === 'requests' && auth()->user()->canSubmitOwnServiceRequests())
                <div class="row">
                    <div class="col-lg-5 mb-4">
                        <h4 class="mb-3"><i class="fa fa-plus"></i> {{ __('New request') }}</h4>
                        <form method="POST" action="{{ route('member.requests.store') }}">
                            @csrf
                            <div class="form-group">
                                <label>{{ __('Request type') }} <span class="text-danger">*</span></label>
                                <select name="request_type" class="form-control" required>
                                    @foreach(\App\Models\ServiceRequest::types() as $value => $label)
                                        <option value="{{ $value }}" {{ old('request_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Subject') }} <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" required value="{{ old('subject') }}"
                                    placeholder="{{ __('Brief summary of your request') }}">
                            </div>
                            <div class="form-group">
                                <label>{{ __('Message') }} <span class="text-danger">*</span></label>
                                <textarea name="message" rows="4" class="form-control" required placeholder="{{ __('Describe your need...') }}">{{ old('message') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Preferred date') }} ({{ __('Optional') }})</label>
                                <input type="date" name="preferred_date" class="form-control" value="{{ old('preferred_date') }}" min="{{ date('Y-m-d') }}">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-paper-plane"></i> {{ __('Submit request') }}
                            </button>
                        </form>
                    </div>
                    <div class="col-lg-7">
                        <h4 class="mb-3"><i class="fa fa-list"></i> {{ __('My requests') }}</h4>
                        @forelse($serviceRequests as $req)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $req->subject }}</strong>
                                        <br><small class="text-muted">{{ $req->typeLabel() }} · {{ $req->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <span class="badge badge-{{ \App\Models\ServiceRequest::statusBadge($req->status) }}">
                                        {{ \App\Models\ServiceRequest::statusLabel($req->status) }}
                                    </span>
                                </div>
                                <p class="mb-1">{{ $req->message }}</p>
                                @if($req->preferred_date)
                                    <p class="mb-1"><small class="text-muted">{{ __('Preferred date') }}: {{ $req->preferred_date->format('d/m/Y') }}</small></p>
                                @endif
                                @if($req->admin_notes)
                                    <div class="alert alert-info mb-0 mt-2 py-2 px-3">
                                        <strong><i class="fa fa-reply"></i> {{ __('Church office reply') }}</strong>
                                        <span class="text-muted small d-block mb-1">{{ __('Updated') }}: {{ $req->updated_at->format('d/m/Y H:i') }}</span>
                                        {{ $req->admin_notes }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted text-center py-4">{{ __('You have not submitted any requests yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Account --}}
            @if($activeTab === 'account')
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <h4 class="mb-3"><i class="fa fa-phone"></i> {{ __('Update phone number') }}</h4>
                        <form method="POST" action="{{ route('member.profile.update') }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label>{{ __('Phone') }} <span class="text-danger">*</span></label>
                                <input type="text" name="phone_number" class="form-control" required
                                    value="{{ old('phone_number', $member->phone_number) }}" placeholder="255...">
                            </div>
                            <div class="form-group">
                                <label>{{ __('Email') }}</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email', $member->email) }}">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> {{ __('Save changes') }}
                            </button>
                        </form>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <h4 class="mb-3"><i class="fa fa-lock"></i> {{ __('Change password') }}</h4>
                        <form method="POST" action="{{ route('member.password.update') }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label>{{ __('Current password') }} <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                            </div>
                            <div class="form-group">
                                <label>{{ __('New password') }} <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
                            </div>
                            <div class="form-group">
                                <label>{{ __('Confirm new password') }} <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="fa fa-key"></i> {{ __('Change password') }}
                            </button>
                        </form>
                        <p class="text-muted mt-3 mb-0">
                            <i class="fa fa-info-circle"></i>
                            {{ __('Your login username is') }}: <code>{{ $member->member_code }}</code>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif
@endsection

@push('styles')
<style>
    .member-portal-tabs .nav-link { font-weight: 500; }
    .member-portal-tabs .nav-link.active { color: #940000; border-bottom: 2px solid #940000; }
</style>
@endpush

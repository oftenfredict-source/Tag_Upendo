@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1>
                <i class="fa fa-{{ $member->parent_id ? 'child' : 'user' }}"></i>
                {{ $member->parent_id ? 'Wasifu wa Mtoto' : 'Member Profile' }}
            </h1>
            <p>{{ $member->name }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Members</a></li>
            @if($member->parent)
                <li class="breadcrumb-item"><a href="{{ route('members.show', $member->parent) }}">{{ $member->parent->name }}</a></li>
            @endif
            <li class="breadcrumb-item">View</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-none" data-session-flash>{{ session('success') }}</div>
    @endif

    @if(session('new_member_accounts'))
        <div class="d-none" data-session-flash></div>
    @endif

    @if($member->isArchived())
        <div class="alert alert-secondary">
            <i class="fa fa-archive"></i>
            <strong>{{ __('Archived member') }}</strong> — {{ $member->archive_reason }}
            @if(auth()->user()->isAdmin())
                <form action="{{ route('members.restore', $member) }}" method="POST" class="d-inline ml-2"
                    onsubmit="return confirm(@json(__('Restore this member to the active list?')));">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fa fa-undo"></i> {{ __('Restore') }}
                    </button>
                </form>
            @endif
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-12">
            @if($member->parent)
                <a href="{{ route('members.show', $member->parent) }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> {{ __('Back to parent') }}
                </a>
            @elseif($member->hasExternalGuardian())
                <a href="{{ route('members.children') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> {{ __('Children') }}
                </a>
            @elseif(!auth()->user()->isMember())
                <a href="{{ route('members.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to List
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> {{ __('My Dashboard') }}
                </a>
            @endif
            @if(auth()->user()->isAdmin() && !$member->isArchived())
                <a href="{{ route('members.edit', $member) }}" class="btn btn-info">
                    <i class="fa fa-pencil"></i> {{ __('Edit') }}
                </a>
            @endif
            @if(auth()->user()->isAdmin() && !$member->parent_id && !$member->isArchived())
                <a href="{{ route('follow-ups.create', $member) }}" class="btn btn-info">
                    <i class="fa fa-envelope"></i> Follow Up / SMS
                </a>
                <a href="{{ route('members.children.create', ['parent_id' => $member->id]) }}" class="btn btn-primary">
                    <i class="fa fa-child"></i> {{ __('Add child') }}
                </a>
            @endif
            @if(auth()->user()->isAdmin() && $member->isChild() && !$member->parent_id)
                <a href="{{ route('members.children') }}" class="btn btn-secondary">
                    <i class="fa fa-child"></i> {{ __('Children') }}
                </a>
            @endif
        </div>
    </div>

    @if($member->isChild())
        {{-- WASIFU WA MTOTO --}}
        <div class="alert alert-info">
            <strong>{{ $member->name }}</strong> — {{ __('Child profile (ages 0–18)') }}
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="tile" style="border-left: 4px solid #940000;">
                    <h3 class="tile-title"><i class="fa fa-pencil"></i> {{ __('Child information') }}</h3>
                    <div class="tile-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="45%">{{ __('Name') }}</th>
                                <td><strong>{{ $member->name }}</strong></td>
                            </tr>
                            <tr>
                                <th>{{ __('Gender') }}</th>
                                <td>{{ $member->gender ? ucfirst($member->gender) : '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Date of Birth') }}</th>
                                <td>{{ $member->date_of_birth ? $member->date_of_birth->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Age') }}</th>
                                <td>
                                    @if($member->age !== null)
                                        @if($member->age === 1)
                                            {{ __(':count year', ['count' => 1]) }}
                                        @else
                                            {{ __(':count years', ['count' => $member->age]) }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tile" style="border-left: 4px solid #6c757d;">
                    <h3 class="tile-title"><i class="fa fa-home"></i> {{ __('Parent / Guardian') }}</h3>
                    <div class="tile-body">
                        @if($member->parent)
                            <p class="mb-2">
                                <a href="{{ route('members.show', $member->parent) }}" class="btn btn-sm btn-outline-primary">
                                    {{ $member->parent->name }}
                                </a>
                                @if($member->parent->spouse)
                                    &amp;
                                    <a href="{{ route('members.show', $member->parent->spouse) }}" class="btn btn-sm btn-outline-primary">
                                        {{ $member->parent->spouse->name }}
                                    </a>
                                @endif
                            </p>
                            <small class="text-muted">{{ __('Church member parent(s)') }}</small>
                        @elseif($member->guardian_name)
                            <p class="mb-1"><strong>{{ $member->guardian_name }}</strong></p>
                            @if($member->guardian_phone)
                                <p class="mb-0 text-muted"><i class="fa fa-phone"></i> {{ $member->guardian_phone }}</p>
                            @endif
                            <small class="text-muted d-block mt-2">{{ __('Guardian is not a church member') }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($member->parent)
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title text-muted"><i class="fa fa-copy"></i> Ilichukuliwa Kutoka kwa Mzazi ({{ $member->parent->name }})</h3>
                    <div class="tile-body">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Taarifa</th>
                                    <th>Thamani (kutoka mzazi)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Simu</td>
                                    <td>{{ $member->phone_number }} <span class="badge badge-secondary">ya mzazi</span></td>
                                </tr>
                                <tr>
                                    <td>Idara</td>
                                    <td>{{ $member->department ? $member->department->name : '-' }} <span class="badge badge-secondary">ya mzazi</span></td>
                                </tr>
                                <tr>
                                    <td>Makazi (Mkoa / Wilaya)</td>
                                    <td>
                                        @if($member->residence_mkoa || $member->residence_wilaya)
                                            {{ $member->residence_mkoa ?? '-' }}, {{ $member->residence_wilaya ?? '-' }}
                                        @else
                                            - <small class="text-muted">(mzazi hana makazi yaliyojazwa)</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Aina ya Mwanachama</td>
                                    <td>Full Member <span class="badge badge-secondary">chaguo-msingi</span></td>
                                </tr>
                                <tr>
                                    <td>Hali ya Ndoa</td>
                                    <td>Single <span class="badge badge-secondary">chaguo-msingi kwa mtoto</span></td>
                                </tr>
                                <tr>
                                    <td>Amebatizwa?</td>
                                    <td>Hapana <span class="badge badge-secondary">chaguo-msingi</span></td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="text-muted small mt-3 mb-0">
                            <i class="fa fa-calendar"></i> Imesajiliwa: {{ $member->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

    @else
        {{-- WASIFU WA MTU MZKUU --}}
        <div class="row" id="familia">
            <div class="col-md-12">
                <div class="tile" style="border-left: 4px solid #940000;">
                    <h3 class="tile-title text-primary">
                        <i class="fa fa-heart"></i> Familia — Wanaoishi & Kusali Pamoja
                    </h3>
                    <div class="tile-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted"><i class="fa fa-users"></i> Mwenzi (Wanandoa)</h6>
                                @if($member->spouse)
                                    <div class="p-3 mb-3" style="background:#f5e6e6;border-radius:6px;">
                                        <strong>{{ $member->spouse->name }}</strong>
                                        <br><small>{{ $member->spouse->phone_number }}</small>
                                        <div class="mt-2">
                                            <a href="{{ route('members.show', $member->spouse) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-eye"></i> Angalia
                                            </a>
                                            <form action="{{ route('members.unlink-spouse', $member) }}" method="POST" class="d-inline"
                                                data-swal-confirm="{{ __('Remove this marriage link?') }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Ondoa</button>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <form action="{{ route('members.link-spouse', $member) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label>Chagua mwenzi (mwanachama mwingine)</label>
                                            <select name="spouse_id" class="form-control" required>
                                                <option value="">-- Chagua Mwenzi --</option>
                                                @forelse($eligibleSpouses as $candidate)
                                                    <option value="{{ $candidate->id }}">
                                                        {{ $candidate->name }} — {{ $candidate->phone_number }}
                                                    </option>
                                                @empty
                                                    <option value="" disabled>Sajili mwanachama mwingine kwanza</option>
                                                @endforelse
                                            </select>
                                            @error('spouse_id')
                                                <small class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa fa-link"></i> Unganisha Wanandoa
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <h6 class="text-muted"><i class="fa fa-child"></i> Watoto</h6>
                                @if($familyChildren->count())
                                    @if($member->spouse)
                                        <p class="text-muted small">{{ __('Family children') }} ({{ $member->name }} &amp; {{ $member->spouse->name }})</p>
                                    @endif
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Gender') }}</th>
                                                <th>{{ __('Date of Birth') }}</th>
                                                <th>{{ __('Age') }}</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($familyChildren as $child)
                                                <tr>
                                                    <td>{{ $child->name }}</td>
                                                    <td>{{ $child->gender === 'male' ? __('Male') : ($child->gender === 'female' ? __('Female') : '-') }}</td>
                                                    <td>{{ $child->date_of_birth ? $child->date_of_birth->format('d/m/Y') : '-' }}</td>
                                                    <td>
                                                        @if($child->age !== null)
                                                            @if($child->age === 1)
                                                                {{ __(':count year', ['count' => 1]) }}
                                                            @else
                                                                {{ __(':count years', ['count' => $child->age]) }}
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('members.show', $child) }}" class="btn btn-sm btn-info">Angalia</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted">Hakuna watoto bado.</p>
                                @endif
                                <a href="{{ route('members.children.create', ['parent_id' => $member->id]) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> {{ __('Add child') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title">Personal Information</h3>
                    <div class="tile-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Full Name</th>
                                <td>{{ $member->name }}</td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td>{{ $member->gender ? ucfirst($member->gender) : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Date of Birth</th>
                                <td>{{ $member->date_of_birth ? $member->date_of_birth->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Marital Status</th>
                                <td>{{ $member->marital_status ? ucfirst($member->marital_status) : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Mahali pa Kuzaliwa</th>
                                <td>
                                    @if($member->birth_mkoa || $member->birth_wilaya)
                                        {{ $member->birth_mkoa ?? '-' }}, {{ $member->birth_wilaya ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Makazi ya Sasa</th>
                                <td>
                                    @if($member->residence_mkoa || $member->residence_wilaya || $member->address)
                                        {{ $member->residence_mkoa ?? '-' }}, {{ $member->residence_wilaya ?? '-' }}
                                        @if($member->address)
                                            <br><small class="text-muted">{{ $member->address }}</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Occupation</th>
                                <td>{{ $member->occupation ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title">Contact</h3>
                    <div class="tile-body">
                        <table class="table table-borderless">
                            @if($member->member_code)
                            <tr>
                                <th width="40%">{{ __('Member ID') }}</th>
                                <td><code>{{ $member->member_code }}</code></td>
                            </tr>
                            @endif
                            <tr>
                                <th width="40%">Phone</th>
                                <td>{{ $member->phone_number }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $member->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Emergency Contact</th>
                                <td>
                                    @if($member->emergency_contact_name || $member->emergency_contact_phone)
                                        {{ $member->emergency_contact_name ?? '-' }}
                                        @if($member->emergency_contact_phone)
                                            ({{ $member->emergency_contact_phone }})
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title">Church Membership</h3>
                    <div class="tile-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Member Type</th>
                                <td>
                                    @if($member->member_type === 'visitor')
                                        <span class="badge badge-info">Visitor</span>
                                    @elseif($member->member_type === 'new_convert')
                                        <span class="badge badge-warning">New Convert</span>
                                    @else
                                        <span class="badge badge-success">Full Member</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Department</th>
                                <td>{{ $member->department ? $member->department->name : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Majukumu / Uongozi</th>
                                <td>
                                    @if($member->leadershipRoles->isNotEmpty())
                                        @foreach($member->leadershipRoles as $r)
                                            <span class="badge badge-primary mr-1">{{ $r->shortLabel() }}</span>
                                        @endforeach
                                        <br><a href="{{ route('church-leaders.index') }}" class="small">Badilisha viongozi</a>
                                    @else
                                        — <a href="{{ route('church-leaders.index') }}">Weka jukumu</a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Date Joined</th>
                                <td>{{ $member->date_joined ? $member->date_joined->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Amebatizwa?</th>
                                <td>{{ $member->is_baptized ? 'Ndiyo' : 'Hapana' }}</td>
                            </tr>
                            @if($member->is_baptized)
                            <tr>
                                <th>Tarehe ya Ubatizo</th>
                                <td>{{ $member->baptism_date ? $member->baptism_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Registered On</th>
                                <td>{{ $member->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title">Notes</h3>
                    <div class="tile-body">
                        <p>{{ $member->notes ?: 'No notes recorded.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

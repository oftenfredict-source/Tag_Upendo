@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-id-badge"></i> Viongozi wa Kanisa</h1>
            <p>Orodha ya viongozi na majukumu yao</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Members</a></li>
            <li class="breadcrumb-item">Viongozi</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Takwimu --}}
    <div class="row mb-3">
        <div class="col-md-6 col-lg-3">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-users fa-3x"></i>
                <div class="info">
                    <h4>Viongozi</h4>
                    <p><b>{{ $stats['leaders'] }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-star fa-3x"></i>
                <div class="info">
                    <h4>Majukumu</h4>
                    <p><b>{{ $stats['roles'] }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-check-circle fa-3x"></i>
                <div class="info">
                    <h4>Yamejazwa</h4>
                    <p><b>{{ $stats['filled_roles'] }}</b> / {{ $stats['roles'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-link fa-3x"></i>
                <div class="info">
                    <h4>Uteuzi</h4>
                    <p><b>{{ $stats['assignments'] }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Sidebar: fomu --}}
        <div class="col-xl-4 mb-4">
            <div class="tile leaders-sidebar-tile">
                <h3 class="tile-title"><i class="fa fa-user-plus text-primary"></i> Weka Jukumu</h3>
                <div class="tile-body">
                    <form action="{{ route('church-leaders.assign') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">Mwanachama <span class="text-danger">*</span></label>
                            <select name="member_id" class="form-control" required>
                                <option value="">-- Chagua --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}>
                                        {{ $m->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Jukumu <span class="text-danger">*</span></label>
                            <select name="leadership_role_id" class="form-control" required>
                                <option value="">-- Chagua --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('leadership_role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->shortLabel() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Tarehe ya Kuteuliwa</label>
                            <input type="date" name="assigned_at" class="form-control" value="{{ old('assigned_at', date('Y-m-d')) }}">
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">Maelezo</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Si lazima">
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-check"></i> Weka Jukumu
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- Maudhui kuu --}}
        <div class="col-xl-8">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">Viongozi kwa Jukumu</h3>
                    <p class="mb-0">
                        <a href="#orodhaKamili" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-list"></i> Orodha kamili
                        </a>
                    </p>
                </div>
                <div class="tile-body">
                    <div class="row">
                        @foreach($roles as $role)
                            <div class="col-md-6 mb-4">
                                <div class="role-card h-100 {{ $role->members->isEmpty() ? 'role-card-empty' : '' }}">
                                    <div class="role-card-header">
                                        <span class="role-icon"><i class="fa fa-star"></i></span>
                                        <div>
                                            <h5 class="role-title mb-0">{{ $role->shortLabel() }}</h5>
                                            <small class="text-muted">{{ $role->name }}</small>
                                        </div>
                                        <span class="badge badge-pill {{ $role->members->count() ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $role->members->count() }}
                                        </span>
                                    </div>
                                    <div class="role-card-body">
                                        @forelse($role->members as $member)
                                            <div class="leader-item">
                                                <img class="leader-avatar"
                                                    src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=009688&color=fff&size=64"
                                                    alt="">
                                                <div class="leader-info">
                                                    <a href="{{ route('members.show', $member) }}" class="leader-name">{{ $member->name }}</a>
                                                    @if($member->phone_number)
                                                        <span class="leader-meta"><i class="fa fa-phone"></i> {{ $member->phone_number }}</span>
                                                    @endif
                                                    @if($member->pivot->assigned_at)
                                                        <span class="leader-meta">Tangu {{ \Carbon\Carbon::parse($member->pivot->assigned_at)->format('d/m/Y') }}</span>
                                                    @endif
                                                </div>
                                                <form action="{{ route('church-leaders.unassign', [$member, $role]) }}" method="POST"
                                                    onsubmit="return confirm('Ondoa jukumu hili?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light text-danger" title="Ondoa">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @empty
                                            <p class="text-muted text-center py-3 mb-0">
                                                <i class="fa fa-user-times fa-2x d-block mb-2 opacity-50"></i>
                                                Hakuna kiongozi bado
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="tile" id="orodhaKamili">
                <h3 class="tile-title">Orodha Kamili ya Viongozi</h3>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered leaders-table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Mwanachama</th>
                                    <th>Simu</th>
                                    <th>Idara</th>
                                    <th>Majukumu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allLeaders as $i => $leader)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img class="leader-avatar-sm mr-2"
                                                    src="https://ui-avatars.com/api/?name={{ urlencode($leader->name) }}&background=3f51b5&color=fff&size=48"
                                                    alt="">
                                                <a href="{{ route('members.show', $leader) }}"><strong>{{ $leader->name }}</strong></a>
                                            </div>
                                        </td>
                                        <td>{{ $leader->phone_number ?? '—' }}</td>
                                        <td>{{ $leader->department->name ?? '—' }}</td>
                                        <td>
                                            @foreach($leader->leadershipRoles as $r)
                                                <span class="badge badge-primary mr-1 mb-1">{{ $r->shortLabel() }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fa fa-id-badge fa-3x mb-3 d-block opacity-25"></i>
                                            Hakuna viongozi bado.<br>
                                            Tumia fomu <strong>Weka Jukumu</strong> kushoto.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .leaders-sidebar-tile { position: sticky; top: 70px; }
    .role-card {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        transition: box-shadow .2s;
    }
    .role-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.1); }
    .role-card-empty { border-style: dashed; background: #fafafa; }
    .role-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: linear-gradient(135deg, #009688 0%, #00796b 100%);
        color: #fff;
    }
    .role-card-header .text-muted { color: rgba(255,255,255,.75) !important; }
    .role-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .role-title { font-size: 1rem; font-weight: 600; color: #fff; }
    .role-card-header .badge { margin-left: auto; }
    .role-card-body { padding: 12px; max-height: 280px; overflow-y: auto; }
    .leader-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        margin-bottom: 8px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #eee;
    }
    .leader-item:last-child { margin-bottom: 0; }
    .leader-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        flex-shrink: 0;
        object-fit: cover;
    }
    .leader-avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
    }
    .leader-info { flex: 1; min-width: 0; }
    .leader-name {
        display: block;
        font-weight: 600;
        color: #2c3e50;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .leader-meta {
        display: block;
        font-size: 12px;
        color: #6c757d;
    }
    .leaders-table thead th {
        background: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    @media (max-width: 1199px) {
        .leaders-sidebar-tile { position: static; }
    }
</style>
@endpush

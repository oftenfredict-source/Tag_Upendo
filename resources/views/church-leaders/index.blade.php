@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-id-badge"></i> {{ __('Church Leaders') }}</h1>
            <p>{{ __('Leaders and their roles') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">{{ __('Members') }}</a></li>
            <li class="breadcrumb-item">{{ __('Church Leaders') }}</li>
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

    <div class="row mb-3">
        <div class="col-md-6 col-lg-3">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-users fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Leaders') }}</h4>
                    <p><b>{{ $stats['leaders'] }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-star fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Roles') }}</h4>
                    <p><b>{{ $stats['roles'] }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-check-circle fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Filled') }}</h4>
                    <p><b>{{ $stats['filled_roles'] }}</b> / {{ $stats['roles'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-link fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Assignments') }}</h4>
                    <p><b>{{ $stats['assignments'] }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="tile">
        <div class="tile-title-w-btn">
            <h3 class="title mb-0">{{ __('Leaders by Role') }}</h3>
            <p class="mb-0">
                <a href="#orodhaKamili" class="btn btn-sm btn-outline-primary mr-1">
                    <i class="fa fa-list"></i> {{ __('Full list') }}
                </a>
                @if(auth()->user()->isFullStaff())
                <button type="button" class="btn btn-sm btn-outline-success mr-1" data-toggle="modal" data-target="#addRoleModal">
                    <i class="fa fa-plus"></i> {{ __('Add Role') }}
                </button>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#assignLeaderModal">
                    <i class="fa fa-user-plus"></i> {{ __('Assign Role') }}
                </button>
                @endif
            </p>
        </div>
        <div class="tile-body">
            <div class="row">
                @foreach($roles as $role)
                    <div class="col-md-6 col-xl-4 mb-4">
                        <div class="role-card h-100 {{ $role->members->isEmpty() ? 'role-card-empty' : '' }}">
                            <div class="role-card-header">
                                <span class="role-icon"><i class="fa fa-star"></i></span>
                                <div>
                                    <h5 class="role-title mb-0">{{ $role->label() }}</h5>
                                    @if($role->name_sw && app()->getLocale() === 'en')
                                        <small class="text-muted">{{ $role->name_sw }}</small>
                                    @elseif(app()->getLocale() === 'sw')
                                        <small class="text-muted">{{ $role->name }}</small>
                                    @endif
                                </div>
                                <span class="badge badge-pill {{ $role->members->count() ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $role->members->count() }}
                                </span>
                                @if(auth()->user()->isFullStaff())
                                <button type="button"
                                    class="btn btn-sm btn-light btn-assign-role"
                                    title="{{ __('Assign Role') }}"
                                    data-toggle="modal"
                                    data-target="#assignLeaderModal"
                                    data-role-id="{{ $role->id }}">
                                    <i class="fa fa-plus"></i>
                                </button>
                                @endif
                            </div>
                            <div class="role-card-body">
                                @forelse($role->members as $member)
                                    <div class="leader-item">
                                        <img class="leader-avatar"
                                            src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=940000&color=fff&size=64"
                                            alt="">
                                        <div class="leader-info">
                                            <a href="{{ route('members.show', $member) }}" class="leader-name">{{ $member->name }}</a>
                                            @if($member->phone_number)
                                                <span class="leader-meta"><i class="fa fa-phone"></i> {{ $member->phone_number }}</span>
                                            @endif
                                            @if($member->pivot->assigned_at)
                                                <span class="leader-meta">{{ __('Since') }} {{ \Carbon\Carbon::parse($member->pivot->assigned_at)->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                        @if(auth()->user()->isFullStaff())
                                        <button type="button"
                                            class="btn btn-sm btn-light text-danger btn-unassign"
                                            title="{{ __('Remove') }}"
                                            data-toggle="modal"
                                            data-target="#unassignLeaderModal"
                                            data-url="{{ route('church-leaders.unassign', [$member, $role]) }}"
                                            data-member="{{ $member->name }}"
                                            data-role="{{ $role->label() }}">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted text-center py-3 mb-0">
                                        <i class="fa fa-user-times fa-2x d-block mb-2 opacity-50"></i>
                                        {{ __('No leaders yet') }}
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
        <h3 class="tile-title">{{ __('Full Leaders List') }}</h3>
        <div class="tile-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered leaders-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">#</th>
                            <th>{{ __('Member') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Department') }}</th>
                            <th>{{ __('Roles') }}</th>
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
                                        <span class="badge badge-primary mr-1 mb-1">{{ $r->label() }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fa fa-id-badge fa-3x mb-3 d-block opacity-25"></i>
                                    {{ __('No leaders yet.') }}<br>
                                    {{ __('Click Assign Role to appoint a leader.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addRoleModal" tabindex="-1" role="dialog" aria-labelledby="addRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('church-leaders.roles.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRoleModalLabel">
                            <i class="fa fa-plus"></i> {{ __('Add New Role') }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('Name (English)') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                placeholder="e.g. Praise and Worship Leader" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Name (Swahili)') }}</label>
                            <input type="text" name="name_sw" class="form-control" value="{{ old('name_sw') }}"
                                placeholder="e.g. Kiongozi wa Praise and Worship">
                            @error('name_sw')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">{{ __('Description') }}</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}"
                                placeholder="{{ __('Optional') }}">
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> {{ __('Save Role') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignLeaderModal" tabindex="-1" role="dialog" aria-labelledby="assignLeaderModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('church-leaders.assign') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignLeaderModalLabel">
                            <i class="fa fa-user-plus"></i> {{ __('Assign Role') }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('Member') }} <span class="text-danger">*</span></label>
                            <select name="member_id" id="assignMemberId" class="form-control" required>
                                <option value="">-- {{ __('Select') }} --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}>
                                        {{ $m->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Role') }} <span class="text-danger">*</span></label>
                            <select name="leadership_role_id" id="assignRoleId" class="form-control" required>
                                <option value="">-- {{ __('Select') }} --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('leadership_role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('leadership_role_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Date Assigned') }}</label>
                            <input type="date" name="assigned_at" class="form-control" value="{{ old('assigned_at', date('Y-m-d')) }}">
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">{{ __('Notes') }}</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="{{ __('Optional') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check"></i> {{ __('Assign Role') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="unassignLeaderModal" tabindex="-1" role="dialog" aria-labelledby="unassignLeaderModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="unassignLeaderForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="unassignLeaderModalLabel">
                            <i class="fa fa-exclamation-triangle"></i> {{ __('Remove Role') }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">
                            {{ __('You are removing the role') }} <strong id="unassignRoleLabel">—</strong>
                            {{ __('from') }} <strong id="unassignMemberName">—</strong>?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-times"></i> {{ __('Remove') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
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
        background: linear-gradient(135deg, #940000 0%, #700000 100%);
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
    .btn-assign-role {
        flex-shrink: 0;
        padding: 2px 8px;
        line-height: 1.4;
    }
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
</style>
@endpush

@push('scripts')
<script>
(function () {
    var assignModal = document.getElementById('assignLeaderModal');
    var assignRoleSelect = document.getElementById('assignRoleId');

    if (assignModal && assignRoleSelect) {
        $(assignModal).on('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            var roleId = trigger && trigger.getAttribute('data-role-id');
            if (roleId) {
                assignRoleSelect.value = roleId;
            } else if (!@json(old('leadership_role_id'))) {
                assignRoleSelect.value = '';
            }
        });
    }

    @if($errors->has('name') || $errors->has('name_sw') || $errors->has('description'))
        $(function () {
            $('#addRoleModal').modal('show');
        });
    @elseif($errors->any() || old('member_id') || old('leadership_role_id'))
        $(function () {
            $('#assignLeaderModal').modal('show');
        });
    @endif

    $('#unassignLeaderModal').on('show.bs.modal', function (event) {
        var btn = $(event.relatedTarget);
        $('#unassignLeaderForm').attr('action', btn.data('url'));
        $('#unassignMemberName').text(btn.data('member') || '—');
        $('#unassignRoleLabel').text(btn.data('role') || '—');
    });
})();
</script>
@endpush

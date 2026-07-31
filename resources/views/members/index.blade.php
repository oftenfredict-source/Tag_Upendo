@extends('layouts.app')

@section('title', __('Members'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-users"></i> {{ __('Members') }}</h1>
            <p>{{ __('View and manage all church members') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">{{ __('Members') }}</li>
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
                    <h4>{{ __('Adults') }}</h4>
                    <p><b>{{ number_format($stats['total']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-check-circle fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Full Members') }}</h4>
                    <p><b>{{ number_format($stats['members']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-user-plus fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Visitors') }} / {{ __('New Converts') }}</h4>
                    <p><b>{{ number_format($stats['visitors'] + $stats['new_converts']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('members.children') }}" class="text-decoration-none">
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-child fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Children') }} (0–18)</h4>
                    <p><b>{{ number_format($stats['children']) }}</b></p>
                </div>
            </div>
            </a>
        </div>
    </div>

    <div class="tile">
        <div class="tile-title-w-btn">
            <h3 class="title mb-0">{{ $showArchived ? __('Archived Members') : __('Member List') }}</h3>
            <p class="mb-0">
                @if(auth()->user()->isAdmin())
                <a class="btn btn-sm {{ $showArchived ? 'btn-outline-primary' : 'btn-primary' }}" href="{{ route('members.index') }}">
                    {{ __('Active members') }}
                </a>
                <a class="btn btn-sm {{ $showArchived ? 'btn-secondary' : 'btn-outline-secondary' }}" href="{{ route('members.index', ['status' => 'archived']) }}">
                    {{ __('Archived') }} ({{ $stats['archived'] }})
                </a>
                @endif
                @if(auth()->user()->isFullStaff())
                <a class="btn btn-sm btn-primary" href="{{ route('members.create') }}">
                    <i class="fa fa-plus"></i> {{ __('Add Member') }}
                </a>
                @endif
                @if(auth()->user()->canManageMemberRegistrations() && $pendingRegistrations > 0)
                <a class="btn btn-sm btn-warning" href="{{ route('member-registrations.index') }}">
                    <i class="fa fa-inbox"></i> {{ __('Pending registrations') }} ({{ $pendingRegistrations }})
                </a>
                @endif
            </p>
        </div>
        <div class="tile-body">
            <form method="GET" action="{{ route('members.index') }}" class="members-filter mb-4">
                @if($showArchived)
                    <input type="hidden" name="status" value="archived">
                @endif
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="control-label"><i class="fa fa-search"></i> {{ __('Search') }}</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                placeholder="{{ __('Name, phone, or email...') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="control-label">{{ __('Type') }}</label>
                            <select name="member_type" class="form-control">
                                <option value="">{{ __('All') }}</option>
                                <option value="member" {{ request('member_type') === 'member' ? 'selected' : '' }}>{{ __('Full Member') }}</option>
                                <option value="visitor" {{ request('member_type') === 'visitor' ? 'selected' : '' }}>{{ __('Visitor') }}</option>
                                <option value="new_convert" {{ request('member_type') === 'new_convert' ? 'selected' : '' }}>{{ __('New Convert') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="control-label">{{ __('Gender') }}</label>
                            <select name="gender" class="form-control">
                                <option value="">{{ __('All') }}</option>
                                <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="control-label">{{ __('Department') }}</label>
                            <select name="department_id" class="form-control">
                                <option value="">{{ __('All') }}</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="control-label">{{ __('Family') }}</label>
                            <select name="family" class="form-control">
                                <option value="">{{ __('All') }}</option>
                                <option value="adults" {{ request('family') === 'adults' ? 'selected' : '' }}>{{ __('Adults only') }}</option>
                                <option value="is_child" {{ request('family') === 'is_child' ? 'selected' : '' }}>{{ __('Children only') }}</option>
                                <option value="has_spouse" {{ request('family') === 'has_spouse' ? 'selected' : '' }}>{{ __('Has spouse') }}</option>
                                <option value="has_children" {{ request('family') === 'has_children' ? 'selected' : '' }}>{{ __('Has children') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm mr-2 mb-1">
                        <i class="fa fa-filter"></i> {{ __('Filter') }}
                    </button>
                    <a href="{{ route('members.index') }}" class="btn btn-secondary btn-sm mb-1">
                        <i class="fa fa-refresh"></i> {{ __('Clear filters') }}
                    </a>
                    @if(request()->hasAny(['search', 'member_type', 'gender', 'department_id', 'family']))
                        <span class="text-muted small ml-2 mb-1">
                            {{ __('Results') }}: <strong>{{ $members->total() }}</strong>
                        </span>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover members-table mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>{{ __('Member ID') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Gender') }}</th>
                            <th>{{ __('Department') }}</th>
                            <th>{{ __('Family') }}</th>
                            <th>{{ __('Joined') }}</th>
                            @if($showArchived)
                                <th>{{ __('Archive reason') }}</th>
                                <th>{{ __('Archived') }}</th>
                            @endif
                            <th width="200">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr class="{{ $member->isArchived() ? 'table-secondary' : '' }}">
                                <td>{{ $members->firstItem() + $loop->index }}</td>
                                <td><code>{{ $member->member_code ?? '—' }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img class="member-avatar mr-2"
                                            src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=940000&color=fff&size=48"
                                            alt="">
                                        <div>
                                            <a href="{{ route('members.show', $member) }}" class="member-name">{{ $member->name }}</a>
                                            @if($member->email)
                                                <small class="d-block text-muted">{{ $member->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $member->phone_number ?? '—' }}</td>
                                <td>
                                    @if($member->member_type === 'visitor')
                                        <span class="badge badge-info">{{ __('Visitor') }}</span>
                                    @elseif($member->member_type === 'new_convert')
                                        <span class="badge badge-warning">{{ __('New Convert') }}</span>
                                    @else
                                        <span class="badge badge-success">{{ __('Full Member') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($member->gender === 'male')
                                        {{ __('Male') }}
                                    @elseif($member->gender === 'female')
                                        {{ __('Female') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $member->department->name ?? '—' }}</td>
                                <td>
                                    @if($member->spouse)
                                        <span class="badge badge-secondary" title="{{ $member->spouse->name }}">{{ __('Married') }}</span>
                                    @endif
                                    @if($member->family_children_count > 0)
                                        <span class="badge badge-info">{{ $member->family_children_count }} {{ __('Children') }}</span>
                                    @endif
                                    @if($member->parent_id)
                                        <span class="badge badge-light">{{ __('Child') }}</span>
                                    @endif
                                    @if(!$member->spouse && $member->family_children_count == 0 && !$member->parent_id)
                                        —
                                    @endif
                                </td>
                                <td>{{ $member->date_joined ? $member->date_joined->format('d/m/Y') : '—' }}</td>
                                @if($showArchived)
                                    <td>{{ $member->archive_reason ?? '—' }}</td>
                                    <td>{{ $member->archived_at?->format('d/m/Y') ?? '—' }}</td>
                                @endif
                                <td class="text-nowrap member-actions">
                                    <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-primary" title="{{ __('View') }}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                        @if(!$showArchived)
                                            <a href="{{ route('members.edit', $member) }}" class="btn btn-sm btn-info" title="{{ __('Edit') }}">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            @if(!$member->parent_id)
                                            <form action="{{ route('members.generate-password', $member) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm(@json(__('Generate a new login password for this member?')));">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" title="{{ __('Generate password') }}">
                                                    <i class="fa fa-key"></i>
                                                </button>
                                            </form>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-secondary btn-archive-member" title="{{ __('Archive') }}"
                                                data-toggle="modal" data-target="#archiveMemberModal"
                                                data-action="{{ route('members.archive', $member) }}"
                                                data-name="{{ $member->name }}">
                                                <i class="fa fa-archive"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('members.restore', $member) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm(@json(__('Restore this member to the active list?')));">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="{{ __('Restore') }}">
                                                    <i class="fa fa-undo"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @elseif(auth()->user()->isFullStaff())
                                        <a href="{{ route('follow-ups.create', $member) }}" class="btn btn-sm btn-info" title="{{ __('Follow Up') }}">
                                            <i class="fa fa-envelope"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $showArchived ? 12 : 10 }}" class="text-center text-muted py-5">
                                    @if($showArchived)
                                        {{ __('No archived members.') }}
                                    @elseif(request()->hasAny(['search', 'member_type', 'gender', 'department_id', 'family']))
                                        {{ __('No members match your filters.') }}
                                        <a href="{{ route('members.index') }}">{{ __('Clear filters') }}</a>
                                    @else
                                        {{ __('No members yet.') }}
                                        <a href="{{ route('members.create') }}">{{ __('Register the first member') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $members->links() }}
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
    <div class="modal fade" id="archiveMemberModal" tabindex="-1" role="dialog" aria-labelledby="archiveMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="archiveMemberForm" method="POST">
                    @csrf
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title" id="archiveMemberModalLabel">
                            <i class="fa fa-archive"></i> {{ __('Archive member') }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">{{ __('Archive member instead of deleting. The record is kept and can be restored later.') }}</p>
                        <p class="mb-3"><strong id="archive-member-name">—</strong></p>
                        <div class="form-group mb-0">
                            <label class="control-label">{{ __('Archive reason') }} <span class="text-danger">*</span></label>
                            <textarea name="archive_reason" class="form-control" rows="4" required
                                placeholder="{{ __('Why is this member being archived?') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-secondary">
                            <i class="fa fa-archive"></i> {{ __('Archive member') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('styles')
<style>
    .members-filter {
        background: #f8f9fa;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 14px 16px;
    }
    .members-table thead th {
        background: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    .member-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .member-name {
        font-weight: 600;
        color: #2c3e50;
    }
    .member-name:hover { color: #940000; text-decoration: none; }
    .member-actions form { display: inline-block; }
    .member-actions .btn { margin-bottom: 2px; }
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.btn-archive-member').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('archiveMemberForm').action = btn.getAttribute('data-action');
        document.getElementById('archive-member-name').textContent = btn.getAttribute('data-name');
        document.querySelector('#archiveMemberForm textarea[name="archive_reason"]').value = '';
    });
});
</script>
@endpush

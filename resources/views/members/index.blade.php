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
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-child fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Children') }}</h4>
                    <p><b>{{ number_format($stats['children']) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="tile">
        <div class="tile-title-w-btn">
            <h3 class="title mb-0">{{ __('Member List') }}</h3>
            <p class="mb-0">
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
                            <th width="120">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr>
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
                                <td class="text-nowrap">
                                    <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-primary" title="{{ __('View') }}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->isFullStaff())
                                    <a href="{{ route('follow-ups.create', $member) }}" class="btn btn-sm btn-info" title="{{ __('Follow Up') }}">
                                        <i class="fa fa-envelope"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete-member"
                                        title="{{ __('Delete member') }}"
                                        data-toggle="modal" data-target="#deleteMemberModal"
                                        data-action="{{ route('members.destroy', $member) }}"
                                        data-name="{{ $member->name }}"
                                        data-phone="{{ $member->phone_number }}"
                                        data-email="{{ $member->email ?? '—' }}"
                                        data-type="{{ $member->member_type }}"
                                        data-department="{{ $member->department?->name ?? '—' }}"
                                        data-spouse="{{ $member->spouse?->name ?? '' }}"
                                        data-children="{{ $member->family_children_count }}"
                                        data-parent="{{ $member->parent_id ? '1' : '' }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    @if(request()->hasAny(['search', 'member_type', 'gender', 'department_id', 'family']))
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

    <div class="modal fade" id="deleteMemberModal" tabindex="-1" role="dialog" aria-labelledby="deleteMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="deleteMemberForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteMemberModalLabel">
                            <i class="fa fa-exclamation-triangle"></i> {{ __('Confirm Delete Member') }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">{{ __('You are about to delete this member from the system. Please confirm the details:') }}</p>

                        <table class="table table-bordered table-sm mb-3">
                            <tbody>
                                <tr>
                                    <th width="35%">{{ __('Name') }}</th>
                                    <td id="del-name"><strong>—</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('Phone') }}</th>
                                    <td id="del-phone">—</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Email') }}</th>
                                    <td id="del-email">—</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Type') }}</th>
                                    <td id="del-type">—</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Department') }}</th>
                                    <td id="del-department">—</td>
                                </tr>
                                <tr id="del-spouse-row" style="display:none;">
                                    <th>{{ __('Spouse') }}</th>
                                    <td id="del-spouse">—</td>
                                </tr>
                                <tr id="del-children-row" style="display:none;">
                                    <th>{{ __('Children') }}</th>
                                    <td id="del-children">—</td>
                                </tr>
                                <tr id="del-parent-row" style="display:none;">
                                    <th>{{ __('Relation') }}</th>
                                    <td id="del-parent-note">—</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="alert alert-warning mb-0">
                            <i class="fa fa-warning"></i>
                            <strong>{{ __('Warning') }}:</strong> {{ __('This action cannot be undone. Their follow-ups will also be deleted.') }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-trash"></i> {{ __('Yes, delete member') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.btn-delete-member').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var form = document.getElementById('deleteMemberForm');
        var typeLabels = {
            member: @json(__('Full Member')),
            visitor: @json(__('Visitor')),
            new_convert: @json(__('New Convert'))
        };

        form.action = btn.getAttribute('data-action');
        document.getElementById('del-name').innerHTML = '<strong>' + btn.getAttribute('data-name') + '</strong>';
        document.getElementById('del-phone').textContent = btn.getAttribute('data-phone');
        document.getElementById('del-email').textContent = btn.getAttribute('data-email');
        document.getElementById('del-type').textContent = typeLabels[btn.getAttribute('data-type')] || btn.getAttribute('data-type');
        document.getElementById('del-department').textContent = btn.getAttribute('data-department');

        var spouse = btn.getAttribute('data-spouse');
        var spouseRow = document.getElementById('del-spouse-row');
        if (spouse) {
            spouseRow.style.display = '';
            document.getElementById('del-spouse').textContent = spouse + ' (' + @json(__('link will be removed')) + ')';
        } else {
            spouseRow.style.display = 'none';
        }

        var children = parseInt(btn.getAttribute('data-children'), 10);
        var childrenRow = document.getElementById('del-children-row');
        if (children > 0) {
            childrenRow.style.display = '';
            document.getElementById('del-children').textContent = children + ' — ' + @json(__('will remain but without this parent'));
        } else {
            childrenRow.style.display = 'none';
        }

        var parentNote = btn.getAttribute('data-parent');
        var parentRow = document.getElementById('del-parent-row');
        if (parentNote) {
            parentRow.style.display = '';
            document.getElementById('del-parent-note').textContent = @json(__('This is a child record (only this person will be deleted)'));
        } else {
            parentRow.style.display = 'none';
        }
    });
});
</script>
@endpush

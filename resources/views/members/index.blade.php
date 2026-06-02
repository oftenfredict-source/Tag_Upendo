@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-users"></i> Members</h1>
            <p>View and manage all church members</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">Members</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title">Member List</h3>
                    <p>
                        <a class="btn btn-primary icon-btn" href="{{ route('members.create') }}">
                            <i class="fa fa-plus"></i> Add Member
                        </a>
                    </p>
                </div>
                <div class="tile-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="GET" action="{{ route('members.index') }}" class="mb-4 p-3" style="background:#f8f9fa;border-radius:6px;">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="control-label"><i class="fa fa-search"></i> Tafuta</label>
                                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                        placeholder="Jina, simu, au barua pepe...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="control-label">Aina</label>
                                    <select name="member_type" class="form-control">
                                        <option value="">Zote</option>
                                        <option value="member" {{ request('member_type') === 'member' ? 'selected' : '' }}>Full Member</option>
                                        <option value="visitor" {{ request('member_type') === 'visitor' ? 'selected' : '' }}>Visitor</option>
                                        <option value="new_convert" {{ request('member_type') === 'new_convert' ? 'selected' : '' }}>New Convert</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="control-label">Jinsia</label>
                                    <select name="gender" class="form-control">
                                        <option value="">Zote</option>
                                        <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="control-label">Idara</label>
                                    <select name="department_id" class="form-control">
                                        <option value="">Zote</option>
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
                                    <label class="control-label">Familia</label>
                                    <select name="family" class="form-control">
                                        <option value="">Zote</option>
                                        <option value="adults" {{ request('family') === 'adults' ? 'selected' : '' }}>Watu wazima</option>
                                        <option value="is_child" {{ request('family') === 'is_child' ? 'selected' : '' }}>Watoto tu</option>
                                        <option value="has_spouse" {{ request('family') === 'has_spouse' ? 'selected' : '' }}>Wana mwenzi</option>
                                        <option value="has_children" {{ request('family') === 'has_children' ? 'selected' : '' }}>Wana watoto</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center">
                            <button type="submit" class="btn btn-primary mr-2 mb-1">
                                <i class="fa fa-filter"></i> Chuja / Tafuta
                            </button>
                            <a href="{{ route('members.index') }}" class="btn btn-secondary mb-1">
                                <i class="fa fa-refresh"></i> Futa Vichujio
                            </a>
                            @if(request()->hasAny(['search', 'member_type', 'gender', 'department_id', 'family']))
                                <span class="text-muted small ml-2 mb-1">
                                    Matokeo: <strong>{{ $members->total() }}</strong> mwanachama
                                </span>
                            @endif
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Gender</th>
                                    <th>Department</th>
                                    <th>Familia</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($members as $member)
                                    <tr>
                                        <td>{{ $members->firstItem() + $loop->index }}</td>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->phone_number }}</td>
                                        <td>{{ $member->email ?? '-' }}</td>
                                        <td>
                                            @if($member->member_type === 'visitor')
                                                <span class="badge badge-info">Visitor</span>
                                            @elseif($member->member_type === 'new_convert')
                                                <span class="badge badge-warning">New Convert</span>
                                            @else
                                                <span class="badge badge-success">Member</span>
                                            @endif
                                        </td>
                                        <td>{{ $member->gender ? ucfirst($member->gender) : '-' }}</td>
                                        <td>{{ $member->department ? $member->department->name : '-' }}</td>
                                        <td>
                                            @if($member->spouse)
                                                <span class="badge badge-secondary" title="Mwenzi: {{ $member->spouse->name }}">Ndoa</span>
                                            @endif
                                            @if($member->family_children_count > 0)
                                                <span class="badge badge-info">{{ $member->family_children_count }} Watoto</span>
                                            @endif
                                            @if($member->parent_id)
                                                <span class="badge badge-light">Mtoto</span>
                                            @endif
                                            @if(!$member->spouse && $member->family_children_count === 0 && !$member->parent_id)
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $member->date_joined ? $member->date_joined->format('d/m/Y') : '-' }}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-primary"
                                                title="Angalia wasifu / familia">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('follow-ups.create', $member) }}" class="btn btn-sm btn-info"
                                                title="Follow Up / SMS">
                                                <i class="fa fa-envelope"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-member"
                                                title="Futa mwanachama"
                                                data-toggle="modal" data-target="#deleteMemberModal"
                                                data-action="{{ route('members.destroy', $member) }}"
                                                data-name="{{ $member->name }}"
                                                data-phone="{{ $member->phone_number }}"
                                                data-email="{{ $member->email ?? '-' }}"
                                                data-type="{{ $member->member_type }}"
                                                data-department="{{ $member->department?->name ?? '-' }}"
                                                data-spouse="{{ $member->spouse?->name ?? '' }}"
                                                data-children="{{ $member->family_children_count }}"
                                                data-parent="{{ $member->parent_id ? 'Mtoto wa mzazi' : '' }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            @if(request()->hasAny(['search', 'member_type', 'gender', 'department_id', 'family']))
                                                Hakuna mwanachama anayefanana na utafutaji/vichujio vyako.
                                                <a href="{{ route('members.index') }}">Ondoa vichujio</a>
                                            @else
                                                Hakuna wanachama bado.
                                                <a href="{{ route('members.create') }}">Sajili wa kwanza</a>
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
                            <i class="fa fa-exclamation-triangle"></i> Thibitisha Kufuta Mwanachama
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Unakaribia kufuta mwanachama huyu kutoka kwenye mfumo. Hakikisha taarifa ni sahihi:</p>

                        <table class="table table-bordered table-sm mb-3">
                            <tbody>
                                <tr>
                                    <th width="35%">Jina</th>
                                    <td id="del-name"><strong>-</strong></td>
                                </tr>
                                <tr>
                                    <th>Simu</th>
                                    <td id="del-phone">-</td>
                                </tr>
                                <tr>
                                    <th>Barua pepe</th>
                                    <td id="del-email">-</td>
                                </tr>
                                <tr>
                                    <th>Aina</th>
                                    <td id="del-type">-</td>
                                </tr>
                                <tr>
                                    <th>Idara</th>
                                    <td id="del-department">-</td>
                                </tr>
                                <tr id="del-spouse-row" style="display:none;">
                                    <th>Mwenzi</th>
                                    <td id="del-spouse">-</td>
                                </tr>
                                <tr id="del-children-row" style="display:none;">
                                    <th>Watoto</th>
                                    <td id="del-children">-</td>
                                </tr>
                                <tr id="del-parent-row" style="display:none;">
                                    <th>Mahusiano</th>
                                    <td id="del-parent-note">-</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="alert alert-warning mb-0" id="del-warning">
                            <i class="fa fa-warning"></i>
                            <strong>Onyo:</strong> Hatua hii haiwezi kutenduliwa. Follow-ups zake pia zitafutwa.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Ghairi</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-trash"></i> Ndiyo, Futa Mwanachama
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-delete-member').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var form = document.getElementById('deleteMemberForm');
        var typeLabels = { member: 'Full Member', visitor: 'Visitor', new_convert: 'New Convert' };

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
            document.getElementById('del-spouse').textContent = spouse + ' (ataondolewa uhusiano)';
        } else {
            spouseRow.style.display = 'none';
        }

        var children = parseInt(btn.getAttribute('data-children'), 10);
        var childrenRow = document.getElementById('del-children-row');
        if (children > 0) {
            childrenRow.style.display = '';
            document.getElementById('del-children').textContent = children + ' — watabaki mfumoni lakini bila mzazi huyu';
        } else {
            childrenRow.style.display = 'none';
        }

        var parentNote = btn.getAttribute('data-parent');
        var parentRow = document.getElementById('del-parent-row');
        if (parentNote) {
            parentRow.style.display = '';
            document.getElementById('del-parent-note').textContent = 'Huyu ni mtoto (atafutwa tu yeye)';
        } else {
            parentRow.style.display = 'none';
        }
    });
});
</script>
@endpush

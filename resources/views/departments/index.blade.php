@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-building"></i> Departments</h1>
            <p>Manage church or organization departments</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="tile">
                <h3 class="tile-title">Create Department</h3>
                <div class="tile-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('departments.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">Department Name</label>
                            <input class="form-control" type="text" name="name" required
                                placeholder="e.g. Youth, Women, Choir">
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit"><i
                                    class="fa fa-fw fa-lg fa-check-circle"></i>Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="tile">
                <h3 class="tile-title">Department List</h3>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Total Members</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departments as $dept)
                                    <tr>
                                        <td>{{ $dept->id }}</td>
                                        <td>{{ $dept->name }}</td>
                                        <td><span class="badge badge-primary">{{ $dept->members_count }}</span></td>
                                        <td>{{ $dept->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-toggle="modal"
                                            data-target="#viewMembersModal{{ $dept->id }}"><i
                                                class="fa fa-users"></i> View Members</button>

                                        <button class="btn btn-sm btn-primary" data-toggle="modal"
                                            data-target="#addMemberModal{{ $dept->id }}"><i
                                                class="fa fa-user-plus"></i> Add Member</button>

                                        <!-- View Members Modal -->
                                        <div class="modal fade" id="viewMembersModal{{ $dept->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="viewMembersModalLabel{{ $dept->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewMembersModalLabel{{ $dept->id }}">
                                                            Members in {{ $dept->name }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Name</th>
                                                                        <th>Phone</th>
                                                                        <th>Email</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($dept->members as $member)
                                                                        <tr>
                                                                            <td>{{ $member->name }}</td>
                                                                            <td>{{ $member->phone_number }}</td>
                                                                            <td>{{ $member->email ?? '-' }}</td>
                                                                            <td>
                                                                                <a href="{{ url('follow-ups/create/' . $member->id) }}"
                                                                                    class="btn btn-sm btn-info">Add SMS</a>
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="4" class="text-center">No members
                                                                                in this department.</td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Add Member Modal -->
                                        <div class="modal fade" id="addMemberModal{{ $dept->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="addMemberModalLabel{{ $dept->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="addMemberModalLabel{{ $dept->id }}">
                                                            Add Member to {{ $dept->name }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('departments.assign-member', $dept->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>Select Existing Member</label>
                                                                <select name="member_id" class="form-control" required>
                                                                    <option value="">-- Choose Member --</option>
                                                                    @foreach ($allMembers as $m)
                                                                        <option value="{{ $m->id }}"
                                                                            {{ $m->department_id == $dept->id ? 'disabled' : '' }}>
                                                                            {{ $m->name }} ({{ $m->phone_number }})
                                                                            {{ $m->department_id == $dept->id ? '- Already in ' . $dept->name : '' }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Assign
                                                                    Member</button>
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Close</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No departments created yet.</td>
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
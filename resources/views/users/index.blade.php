@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-users"></i> System Users</h1>
            <p>Manage people who can log in to the system</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="tile">
                <h3 class="tile-title">Add New User</h3>
                <div class="tile-body">
                    @if(session('success'))
                        <div class="alert alert-success">{!! session('success') !!}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">Full Name</label>
                            <input class="form-control" type="text" name="name" value="{{ old('name') }}" required
                                placeholder="Enter full name">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Email Address</label>
                            <input class="form-control" type="email" name="email" value="{{ old('email') }}" required
                                placeholder="Enter email">
                        </div>
                        <p class="text-muted"><i class="fa fa-info-circle"></i> A random password will be auto-generated and
                            displayed after saving.</p>
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit"><i
                                    class="fa fa-fw fa-lg fa-check-circle"></i>Create Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="tile">
                <h3 class="tile-title">Existing Users</h3>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Joined Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td><b>{{ $user->name }}</b></td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
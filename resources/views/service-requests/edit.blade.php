@extends('layouts.app')

@section('title', __('Edit service request'))

@section('content')
    @php $req = $serviceRequest; @endphp

    <div class="app-title">
        <div>
            <h1><i class="fa fa-pencil"></i> {{ __('Edit service request') }}</h1>
            <p>{{ $req->subject }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('requests.index') }}">{{ __('Service requests') }}</a></li>
            <li class="breadcrumb-item">{{ __('Edit') }}</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('requests.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> {{ __('Back') }}
        </a>
        <a href="{{ route('members.show', $req->member) }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-user"></i> {{ __('View member profile') }}
        </a>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-info-circle"></i> {{ __('Request details') }}</h3>
                <div class="tile-body">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th width="35%">{{ __('Member') }}</th>
                            <td>
                                <a href="{{ route('members.show', $req->member) }}">{{ $req->member->name }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('Phone') }}</th>
                            <td>{{ $req->member->phone_number ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Department') }}</th>
                            <td>{{ $req->member->department->name ?? __('No department') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Request type') }}</th>
                            <td>{{ $req->typeLabel() }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Subject') }}</th>
                            <td>{{ $req->subject }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Message') }}</th>
                            <td>{{ $req->message }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Preferred date') }}</th>
                            <td>{{ $req->preferred_date?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Submitted') }}</th>
                            <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-cog"></i> {{ __('Update request') }}</h3>
                <div class="tile-body">
                    <form method="POST" action="{{ route('requests.update', $req) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="control-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $value)
                                    <option value="{{ $value }}" {{ old('status', $req->status) === $value ? 'selected' : '' }}>
                                        {{ \App\Models\ServiceRequest::statusLabel($value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')<small class="text-danger d-block">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-group">
                            <label class="control-label">{{ __('Reply to member') }}</label>
                            <textarea name="admin_notes" rows="5" class="form-control"
                                placeholder="{{ __('Write a reply the member will see in their portal (optional)') }}">{{ old('admin_notes', $req->admin_notes) }}</textarea>
                            <small class="form-text text-muted">{{ __('This message is visible to the member in My Portal.') }}</small>
                            @error('admin_notes')<small class="text-danger d-block">{{ $message }}</small>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> {{ __('Save changes') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

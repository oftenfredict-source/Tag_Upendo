@extends('layouts.app')

@section('title', __('Announcements'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-bullhorn"></i> {{ __('Announcements') }}</h1>
            <p>{{ __('Publish church announcements for members and staff') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">{{ __('Announcements') }}</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-plus"></i> {{ __('New announcement') }}</h3>
                <div class="tile-body">
                    <form method="POST" action="{{ route('announcements.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('Title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required value="{{ old('title') }}"
                                placeholder="{{ __('Announcement title') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Message') }} <span class="text-danger">*</span></label>
                            <textarea name="body" rows="5" class="form-control" required
                                placeholder="{{ __('Write the announcement message...') }}">{{ old('body') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Audience') }}</label>
                            <select name="audience" class="form-control">
                                @foreach(\App\Models\Announcement::audiences() as $value => $label)
                                    <option value="{{ $value }}" {{ old('audience', 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Priority') }}</label>
                            <select name="priority" class="form-control">
                                @foreach(\App\Models\Announcement::priorities() as $value => $label)
                                    <option value="{{ $value }}" {{ old('priority', 'normal') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Start date') }}</label>
                                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('End date') }}</label>
                                    <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="d-flex align-items-center">
                                <input type="checkbox" name="is_published" value="1" class="mr-2" {{ old('is_published', true) ? 'checked' : '' }}>
                                {{ __('Publish immediately') }}
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-paper-plane"></i> {{ __('Publish announcement') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-list"></i> {{ __('All announcements') }}</h3>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Audience') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Published') }}</th>
                                    <th width="120">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($announcements as $item)
                                    <tr class="{{ $item->priority === 'important' ? 'table-warning' : '' }}">
                                        <td>
                                            <strong>{{ $item->title }}</strong>
                                            <br><small class="text-muted">{{ Str::limit($item->body, 60) }}</small>
                                        </td>
                                        <td>{{ $item->audienceLabel() }}</td>
                                        <td>
                                            <span class="badge badge-{{ $item->priority === 'important' ? 'danger' : 'secondary' }}">
                                                {{ $item->priorityLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $item->is_published ? 'success' : 'secondary' }}">
                                                {{ $item->is_published ? __('Published') : __('Draft') }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $item->published_at?->format('d/m/Y H:i') ?? '—' }}
                                            @if($item->expires_at)
                                                <br><small class="text-muted">{{ __('Until') }} {{ $item->expires_at->format('d/m/Y') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editAnnouncement{{ $item->id }}">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('announcements.destroy', $item) }}" class="d-inline"
                                                data-swal-confirm="{{ __('Delete this announcement?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editAnnouncement{{ $item->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('announcements.update', $item) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('Edit announcement') }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>{{ __('Title') }}</label>
                                                            <input type="text" name="title" class="form-control" required value="{{ $item->title }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>{{ __('Message') }}</label>
                                                            <textarea name="body" rows="4" class="form-control" required>{{ $item->body }}</textarea>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>{{ __('Audience') }}</label>
                                                                    <select name="audience" class="form-control">
                                                                        @foreach(\App\Models\Announcement::audiences() as $value => $label)
                                                                            <option value="{{ $value }}" {{ $item->audience === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>{{ __('Priority') }}</label>
                                                                    <select name="priority" class="form-control">
                                                                        @foreach(\App\Models\Announcement::priorities() as $value => $label)
                                                                            <option value="{{ $value }}" {{ $item->priority === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>{{ __('Start date') }}</label>
                                                                    <input type="datetime-local" name="starts_at" class="form-control"
                                                                        value="{{ $item->starts_at?->format('Y-m-d\TH:i') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>{{ __('End date') }}</label>
                                                                    <input type="datetime-local" name="expires_at" class="form-control"
                                                                        value="{{ $item->expires_at?->format('Y-m-d\TH:i') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb-0">
                                                            <label class="d-flex align-items-center">
                                                                <input type="checkbox" name="is_published" value="1" class="mr-2" {{ $item->is_published ? 'checked' : '' }}>
                                                                {{ __('Published') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">{{ __('No announcements yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">{{ $announcements->links() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection

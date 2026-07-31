@extends('layouts.app')

@section('title', __('Children'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-child"></i> {{ __('Children') }}</h1>
            <p>{{ __('All children aged 0 to 18 years') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">{{ __('Members') }}</a></li>
            <li class="breadcrumb-item">{{ __('Children') }}</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-child fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Total children') }}</h4>
                    <p><b>{{ number_format($stats['total']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-male fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Male') }}</h4>
                    <p><b>{{ number_format($stats['male']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-female fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Female') }}</h4>
                    <p><b>{{ number_format($stats['female']) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="tile">
        <div class="tile-title-w-btn">
            <h3 class="title mb-0">{{ __('Children list') }}</h3>
            <p class="mb-0">
                @if(auth()->user()->isAdmin())
                <a class="btn btn-sm btn-primary" href="{{ route('members.children.create') }}">
                    <i class="fa fa-plus"></i> {{ __('Add child') }}
                </a>
                @endif
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('members.index') }}">
                    <i class="fa fa-users"></i> {{ __('View Members') }}
                </a>
            </p>
        </div>
        <div class="tile-body">
            <form method="GET" action="{{ route('members.children') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="control-label"><i class="fa fa-search"></i> {{ __('Search') }}</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                placeholder="{{ __('Child name or parent name...') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="control-label">{{ __('Gender') }}</label>
                            <select name="gender" class="form-control">
                                <option value="">{{ __('All') }}</option>
                                <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-group mb-2">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fa fa-filter"></i> {{ __('Filter') }}
                            </button>
                            <a href="{{ route('members.children') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                        </div>
                    </div>
                </div>
            </form>

            @if($children->count())
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Gender') }}</th>
                                <th>{{ __('Date of Birth') }}</th>
                                <th>{{ __('Age') }}</th>
                                <th>{{ __('Parent / Guardian') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($children as $child)
                                <tr>
                                    <td>{{ $children->firstItem() + $loop->index }}</td>
                                    <td>
                                        <strong>{{ $child->name }}</strong>
                                        @if($child->parent_id)
                                            <span class="badge badge-light ml-1">{{ __('Child') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($child->gender === 'male')
                                            {{ __('Male') }}
                                        @elseif($child->gender === 'female')
                                            {{ __('Female') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $child->date_of_birth ? $child->date_of_birth->format('d/m/Y') : '—' }}</td>
                                    <td>
                                        @if($child->age !== null)
                                            @if($child->age === 1)
                                                {{ __(':count year', ['count' => 1]) }}
                                            @else
                                                {{ __(':count years', ['count' => $child->age]) }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($child->parent)
                                            <a href="{{ route('members.show', $child->parent) }}">{{ $child->parent->name }}</a>
                                            <small class="text-muted d-block">{{ __('Church member') }}</small>
                                        @elseif($child->guardian_name)
                                            {{ $child->guardian_name }}
                                            @if($child->guardian_phone)
                                                <small class="text-muted d-block">{{ $child->guardian_phone }}</small>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $child->department->name ?? '—' }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('members.show', $child) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $children->links() }}
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class="fa fa-info-circle"></i>
                    {{ __('No children found aged 0 to 18 years.') }}
                </div>
            @endif
        </div>
    </div>
@endsection

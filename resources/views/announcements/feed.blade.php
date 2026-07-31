@extends('layouts.app')

@section('title', __('Announcements'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-bullhorn"></i> {{ __('Announcements') }}</h1>
            <p>{{ __('Church news and updates') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item">{{ __('Announcements') }}</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-lg-12">
            @forelse($announcements as $announcement)
                <div class="tile mb-3">
                    <div class="tile-body">
                        <div class="announcement-card {{ $announcement->priority === 'important' ? 'announcement-important' : '' }}">
                            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                                <div>
                                    <h4 class="mb-1">
                                        @if($announcement->priority === 'important')
                                            <i class="fa fa-exclamation-circle text-danger"></i>
                                        @endif
                                        {{ $announcement->title }}
                                    </h4>
                                    <small class="text-muted">
                                        {{ $announcement->published_at?->format('d/m/Y H:i') ?? $announcement->created_at->format('d/m/Y H:i') }}
                                        @if($announcement->author)
                                            · {{ $announcement->author->name }}
                                        @endif
                                    </small>
                                </div>
                                @if($announcement->priority === 'important')
                                    <span class="badge badge-danger">{{ __('Important') }}</span>
                                @endif
                            </div>
                            <div class="announcement-body">{!! nl2br(e($announcement->body)) !!}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="tile">
                    <div class="tile-body text-center text-muted py-5">
                        <i class="fa fa-bullhorn fa-3x mb-3 d-block"></i>
                        {{ __('No announcements at the moment.') }}
                    </div>
                </div>
            @endforelse

            <div class="d-flex justify-content-center">{{ $announcements->links() }}</div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .announcement-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 16px;
        background: #fff;
    }
    .announcement-important {
        border-left: 4px solid #940000;
        background: #fff8f8;
    }
    .announcement-body {
        color: #444;
        line-height: 1.6;
    }
</style>
@endpush

@if(($announcements ?? collect())->isNotEmpty())
<div class="tile mb-4">
    <div class="tile-title-w-btn">
        <h3 class="title mb-0"><i class="fa fa-bullhorn"></i> {{ __('Announcements') }}</h3>
        <p class="mb-0">
            <a href="{{ route('announcements.index') }}" class="btn btn-sm btn-outline-primary">
                {{ __('View all') }}
            </a>
        </p>
    </div>
    <div class="tile-body">
        @foreach($announcements as $announcement)
            <div class="announcement-card mb-3 {{ $announcement->priority === 'important' ? 'announcement-important' : '' }}">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="mb-1">
                            @if($announcement->priority === 'important')
                                <i class="fa fa-exclamation-circle text-danger"></i>
                            @endif
                            {{ $announcement->title }}
                        </h5>
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
        @endforeach
    </div>
</div>
@endif

@once
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
@endonce

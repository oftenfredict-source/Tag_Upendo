@extends('layouts.app')

@section('title', __('Registration link'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-link"></i> {{ __('Member registration link') }}</h1>
            <p>{{ __('Share this link so people can fill the registration form online. The pastor must approve each request before the member appears in the list.') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">{{ __('Members') }}</a></li>
            <li class="breadcrumb-item">{{ __('Registration link') }}</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(auth()->user()->canManageMemberRegistrations() && $pendingRegistrations > 0)
        <div class="alert alert-warning">
            <i class="fa fa-inbox"></i>
            {{ __('You have :count pending registration(s) awaiting verification.', ['count' => $pendingRegistrations]) }}
            <a href="{{ route('member-registrations.index') }}" class="alert-link">{{ __('Review now') }}</a>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="tile">
                <h3 class="tile-title">{{ __('Active registration link') }}</h3>
                <div class="tile-body">
                    @if($activeRegistrationLink)
                        <div class="registration-link-box mb-3">
                            <label class="control-label font-weight-bold">{{ __('Copy and share this link') }}</label>
                            <div class="input-group input-group-lg mt-2">
                                <input type="text" class="form-control" id="registrationUrlInput" readonly
                                    value="{{ $activeRegistrationLink->publicUrl() }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" id="copyRegistrationUrlBtn">
                                        <i class="fa fa-copy"></i> {{ __('Copy link') }}
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2 text-muted small">
                                <span><i class="fa fa-bar-chart"></i> {{ __('Uses') }}: {{ $activeRegistrationLink->uses_count }}</span>
                                @if($activeRegistrationLink->label)
                                    <span class="ml-3"><i class="fa fa-tag"></i> {{ $activeRegistrationLink->label }}</span>
                                @endif
                                @if($activeRegistrationLink->expires_at)
                                    <span class="ml-3"><i class="fa fa-clock-o"></i> {{ __('Expires') }}: {{ $activeRegistrationLink->expires_at->format('d/m/Y H:i') }}</span>
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('registration-links.toggle', $activeRegistrationLink) }}" method="POST" class="d-inline"
                            onsubmit="return confirm(@json(__('Deactivate this link? People will not be able to register until you generate a new one.')));">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fa fa-ban"></i> {{ __('Deactivate link') }}
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fa fa-info-circle"></i> {{ __('No active registration link. Generate one using the form on the right.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="tile">
                <h3 class="tile-title">{{ __('Generate new link') }}</h3>
                <div class="tile-body">
                    <form action="{{ route('registration-links.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">{{ __('Label') }} <small class="text-muted">({{ __('optional') }})</small></label>
                            <input type="text" class="form-control" name="label" placeholder="{{ __('e.g. New members 2026') }}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Expires at') }} <small class="text-muted">({{ __('optional') }})</small></label>
                            <input type="datetime-local" class="form-control" name="expires_at">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-plus"></i> {{ __('Generate new link') }}
                        </button>
                    </form>
                    <p class="text-muted small mt-3 mb-0">
                        {{ __('Generating a new link will deactivate the previous one.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .registration-link-box {
        background: #f8f9fa;
        border: 2px dashed #940000;
        border-radius: 10px;
        padding: 1.25rem;
    }
    .registration-link-box .form-control {
        font-size: 0.95rem;
        background: #fff;
    }
</style>
@endpush

@push('scripts')
<script>
document.getElementById('copyRegistrationUrlBtn')?.addEventListener('click', function () {
    var input = document.getElementById('registrationUrlInput');
    if (!input) return;

    input.select();
    input.setSelectionRange(0, 99999);

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(input.value).then(function () {
            alert(@json(__('Link copied to clipboard.')));
        }).catch(function () {
            document.execCommand('copy');
            alert(@json(__('Link copied to clipboard.')));
        });
    } else {
        document.execCommand('copy');
        alert(@json(__('Link copied to clipboard.')));
    }
});
</script>
@endpush

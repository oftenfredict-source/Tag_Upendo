@extends('layouts.public-form')

@section('title', __('Invalid link'))

@section('content')
    <div class="text-center py-4">
        <div class="mb-3">
            <span class="public-form-page__error-icon"><i class="fa fa-exclamation-triangle"></i></span>
        </div>
        <h2 class="mb-2">{{ __('Registration link unavailable') }}</h2>
        <p class="text-muted mb-4">
            {{ __('This registration link is invalid, inactive, or has expired. Please contact the church office for a new link.') }}
        </p>
        <a href="{{ route('login') }}" class="btn btn-primary">
            <i class="fa fa-sign-in"></i> {{ __('Go to login') }}
        </a>
    </div>
@endsection

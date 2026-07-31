@extends('layouts.public-form')

@section('title', __('Registration submitted'))

@section('content')
    <div class="text-center py-4">
        <div class="mb-3">
            <span class="public-form-page__success-icon"><i class="fa fa-check-circle"></i></span>
        </div>
        <h2 class="mb-2">{{ __('Registration submitted') }}</h2>
        <p class="text-muted mb-4">
            {{ __('Thank you! Your registration has been received. The pastor will review your information and you will be added to the member list once approved.') }}
        </p>
        <a href="{{ route('login') }}" class="btn btn-primary">
            <i class="fa fa-sign-in"></i> {{ __('Go to login') }}
        </a>
    </div>
@endsection

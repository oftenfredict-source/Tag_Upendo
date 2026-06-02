@extends('layouts.app')

@section('content')
    @php
        $s = fn ($key, $default = '') => \App\Models\Setting::get($key, $default);
    @endphp

    <div class="app-title">
        <div>
            <h1><i class="fa fa-cog"></i> System Settings</h1>
            <p>Mipangilio ya kanisa, SMS, na mfumo</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">Settings</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="tile">
                <div class="tile-body">
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        @method('PUT')

                        <ul class="nav nav-tabs mb-4" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab-church">
                                    <i class="fa fa-church"></i> Church Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-sms">
                                    <i class="fa fa-comment"></i> SMS
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-general">
                                    <i class="fa fa-sliders"></i> General
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            {{-- Church --}}
                            <div class="tab-pane fade show active" id="tab-church">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Jina la Kanisa <span class="text-danger">*</span></label>
                                            <input type="text" name="church_name" class="form-control" required
                                                value="{{ old('church_name', $s('church_name', 'TAG Upendo')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Tagline / Kichwa Kidogo</label>
                                            <input type="text" name="church_tagline" class="form-control"
                                                value="{{ old('church_tagline', $s('church_tagline')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Mchungaji Mkuu</label>
                                            <input type="text" name="church_pastor" class="form-control"
                                                value="{{ old('church_pastor', $s('church_pastor')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Simu</label>
                                            <input type="text" name="church_phone" class="form-control"
                                                value="{{ old('church_phone', $s('church_phone')) }}"
                                                placeholder="255...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Barua Pepe</label>
                                            <input type="email" name="church_email" class="form-control"
                                                value="{{ old('church_email', $s('church_email')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">Anwani</label>
                                            <textarea name="church_address" class="form-control" rows="2">{{ old('church_address', $s('church_address')) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SMS --}}
                            <div class="tab-pane fade" id="tab-sms">
                                @if($smsBalance && ($smsBalance['success'] ?? false))
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        Salio la SMS: <strong>{{ $smsBalance['balance'] ?? '—' }}</strong>
                                        @if(isset($smsBalance['sms_count']))
                                            (takriban SMS {{ number_format($smsBalance['sms_count']) }})
                                        @endif
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label class="d-flex align-items-center">
                                        <input type="checkbox" name="sms_enabled" value="1" class="mr-2"
                                            {{ old('sms_enabled', \App\Models\Setting::bool('sms_enabled', true)) ? 'checked' : '' }}>
                                        Wezesha kutuma SMS
                                    </label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">SMS Sender ID</label>
                                            <input type="text" name="sms_sender_id" class="form-control" maxlength="11"
                                                value="{{ old('sms_sender_id', $s('sms_sender_id', 'TAG UPENDO')) }}">
                                            <small class="text-muted">Herufi 11 pekee (mf. TAG UPENDO)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">SMS API Token</label>
                                            <input type="password" name="sms_token" class="form-control"
                                                placeholder="{{ $s('sms_token') ? '•••••••• (acha tupu kubaki)' : 'Weka token' }}"
                                                autocomplete="new-password">
                                            <small class="text-muted">Acha tupu ikiwa hutaki kubadilisha token iliyopo</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- General --}}
                            <div class="tab-pane fade" id="tab-general">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">Sarafu</label>
                                            <input type="text" name="currency" class="form-control" required
                                                value="{{ old('currency', $s('currency', 'TSH')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">Timezone</label>
                                            <select name="timezone" class="form-control" required>
                                                @foreach(['Africa/Dar_es_Salaam', 'Africa/Nairobi', 'Africa/Kampala', 'UTC'] as $tz)
                                                    <option value="{{ $tz }}" {{ old('timezone', $s('timezone', 'Africa/Dar_es_Salaam')) === $tz ? 'selected' : '' }}>
                                                        {{ $tz }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">Muundo wa Tarehe</label>
                                            <select name="date_format" class="form-control" required>
                                                @foreach(['d/m/Y' => '31/12/2026', 'Y-m-d' => '2026-12-31', 'd-m-Y' => '31-12-2026'] as $fmt => $example)
                                                    <option value="{{ $fmt }}" {{ old('date_format', $s('date_format', 'd/m/Y')) === $fmt ? 'selected' : '' }}>
                                                        {{ $example }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> Hifadhi Mipangilio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .nav-tabs .nav-link { font-weight: 500; }
    .nav-tabs .nav-link.active { color: #009688; border-bottom: 2px solid #009688; }
</style>
@endpush

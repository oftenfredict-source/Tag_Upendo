@extends('layouts.app')

@section('title', __('System Settings'))

@section('content')
    @php
        $s = fn ($key, $default = '') => \App\Models\Setting::get($key, $default);
        $activeTab = $tab ?? 'church';
    @endphp

    <div class="app-title">
        <div>
            <h1><i class="fa fa-cog"></i> {{ __('System Settings') }}</h1>
            <p>{{ __('Church, SMS, users, logs and session management') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">{{ __('Settings') }}</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-none" data-session-flash>
            {!! session('success') !!}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-none" data-session-flash>
            {{ session('error') }}
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
                    <ul class="nav nav-tabs mb-4 settings-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'church' ? 'active' : '' }}" data-toggle="tab" href="#tab-church">
                                <i class="fa fa-building"></i> {{ __('Church Info') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'sms' ? 'active' : '' }}" data-toggle="tab" href="#tab-sms">
                                <i class="fa fa-comment"></i> {{ __('SMS') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}" data-toggle="tab" href="#tab-general">
                                <i class="fa fa-sliders"></i> {{ __('General') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'users' ? 'active' : '' }}" data-toggle="tab" href="#tab-users">
                                <i class="fa fa-users"></i> {{ __('User Management') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'logs' ? 'active' : '' }}" data-toggle="tab" href="#tab-logs">
                                <i class="fa fa-history"></i> {{ __('System Logs') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'sessions' ? 'active' : '' }}" data-toggle="tab" href="#tab-sessions">
                                <i class="fa fa-desktop"></i> {{ __('Sessions') }}
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- Church --}}
                        <div class="tab-pane fade {{ $activeTab === 'church' ? 'show active' : '' }}" id="tab-church">
                            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="tab" value="church">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Church name') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="church_name" class="form-control" required
                                                value="{{ old('church_name', $s('church_name', 'TAG Upendo')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Tagline') }}</label>
                                            <input type="text" name="church_tagline" class="form-control"
                                                value="{{ old('church_tagline', $s('church_tagline')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Senior Pastor') }}</label>
                                            <input type="text" name="church_pastor" class="form-control"
                                                value="{{ old('church_pastor', $s('church_pastor')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Phone') }}</label>
                                            <input type="text" name="church_phone" class="form-control"
                                                value="{{ old('church_phone', $s('church_phone')) }}" placeholder="255...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Email') }}</label>
                                            <input type="email" name="church_email" class="form-control"
                                                value="{{ old('church_email', $s('church_email')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Address') }}</label>
                                            <textarea name="church_address" class="form-control" rows="2">{{ old('church_address', $s('church_address')) }}</textarea>
                                        </div>
                                    </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Church logo') }}</label>
                                            <div class="church-logo-preview mb-3 text-center p-3 border rounded bg-light">
                                                @if($s('church_logo'))
                                                    <img src="{{ asset('storage/' . $s('church_logo')) }}" alt="{{ __('Church logo') }}" class="church-logo-preview-img">
                                                @else
                                                    <div class="text-muted py-4">
                                                        <i class="fa fa-image fa-3x mb-2 d-block"></i>
                                                        {{ __('No logo uploaded yet') }}
                                                    </div>
                                                @endif
                                            </div>
                                            <input type="file" name="church_logo" class="form-control-file mb-2" accept="image/jpeg,image/png,image/webp">
                                            <small class="text-muted d-block mb-2">{{ __('JPG, PNG or WEBP. Max 2MB.') }}</small>
                                            @if($s('church_logo'))
                                                <label class="d-flex align-items-center text-danger">
                                                    <input type="checkbox" name="remove_church_logo" value="1" class="mr-2">
                                                    {{ __('Remove current logo') }}
                                                </label>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> {{ __('Save settings') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- SMS --}}
                        <div class="tab-pane fade {{ $activeTab === 'sms' ? 'show active' : '' }}" id="tab-sms">
                            <form method="POST" action="{{ route('settings.update') }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="tab" value="sms">
                                @if($smsBalance && ($smsBalance['success'] ?? false))
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        {{ __('SMS balance') }}: <strong>{{ $smsBalance['balance'] ?? '—' }}</strong>
                                        @if(isset($smsBalance['sms_count']))
                                            ({{ __('approx. :count SMS', ['count' => number_format($smsBalance['sms_count'])]) }})
                                        @endif
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label class="d-flex align-items-center">
                                        <input type="checkbox" name="sms_enabled" value="1" class="mr-2"
                                            {{ old('sms_enabled', \App\Models\Setting::bool('sms_enabled', true)) ? 'checked' : '' }}>
                                        {{ __('Enable SMS sending') }}
                                    </label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('SMS Sender ID') }}</label>
                                            <input type="text" name="sms_sender_id" class="form-control" maxlength="11"
                                                value="{{ old('sms_sender_id', $s('sms_sender_id', 'TAG UPENDO')) }}">
                                            <small class="text-muted">{{ __('11 characters max (e.g. TAG UPENDO)') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('SMS API Token') }}</label>
                                            <input type="password" name="sms_token" class="form-control"
                                                placeholder="{{ $s('sms_token') ? __('Leave blank to keep current') : __('Enter token') }}"
                                                autocomplete="new-password">
                                            <small class="text-muted">{{ __('Leave blank if you do not want to change the existing token') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-right mb-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> {{ __('Save settings') }}
                                    </button>
                                </div>
                            </form>

                            <h5 class="mb-3"><i class="fa fa-list"></i> {{ __('SMS delivery logs') }}</h5>
                            <p class="text-muted mb-3">{{ __('Live delivery reports from SMS provider') }}</p>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('Date sent') }}</th>
                                            <th>{{ __('Recipient') }}</th>
                                            <th>{{ __('Message') }}</th>
                                            <th>{{ __('Cost') }} (TSH)</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($logs as $log)
                                            <tr>
                                                <td>{{ isset($log['date']) ? \Carbon\Carbon::parse($log['date'])->format('Y-m-d H:i') : '—' }}</td>
                                                <td>{{ $log['to'] ?? '—' }}</td>
                                                <td>{{ Str::limit($log['message'] ?? '—', 50) }}</td>
                                                <td>{{ $log['price'] ?? '0' }}</td>
                                                <td>
                                                    @php
                                                        $status = $log['status']['name'] ?? 'UNKNOWN';
                                                        $badge = 'secondary';
                                                        if (str_contains($status, 'DELIVERED') || str_contains($status, 'SUCCESS')) {
                                                            $badge = 'success';
                                                        } elseif (str_contains($status, 'PENDING') || str_contains($status, 'SENT') || str_contains($status, 'ENROUTE')) {
                                                            $badge = 'warning';
                                                        } elseif (str_contains($status, 'REJECTED') || str_contains($status, 'FAILED')) {
                                                            $badge = 'danger';
                                                        }
                                                    @endphp
                                                    <span class="badge badge-{{ $badge }}">{{ $status }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    {{ __('No logs found on SMS servers, or connection failed.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- General --}}
                        <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="tab-general">
                            <form method="POST" action="{{ route('settings.update') }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="tab" value="general">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Currency') }}</label>
                                            <input type="text" name="currency" class="form-control" required
                                                value="{{ old('currency', $s('currency', 'TSH')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Timezone') }}</label>
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
                                            <label class="control-label">{{ __('Date format') }}</label>
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
                                <hr>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> {{ __('Save settings') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Users --}}
                        <div class="tab-pane fade {{ $activeTab === 'users' ? 'show active' : '' }}" id="tab-users">
                            <div class="row">
                                <div class="col-md-5">
                                    <h4 class="mb-3"><i class="fa fa-user-plus"></i> {{ __('Add new user') }}</h4>
                                    <form action="{{ route('settings.users.store') }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Full name') }}</label>
                                            <input class="form-control" type="text" name="name" value="{{ old('name') }}" required
                                                placeholder="{{ __('Enter full name') }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Email address') }}</label>
                                            <input class="form-control" type="email" name="email" value="{{ old('email') }}" required
                                                placeholder="{{ __('Enter email') }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Account role') }}</label>
                                            <select name="role" id="userRole" class="form-control" required>
                                                <option value="admin" {{ old('role', 'admin') === 'admin' ? 'selected' : '' }}>{{ __('Administrator') }}</option>
                                                <option value="pastor" {{ old('role') === 'pastor' ? 'selected' : '' }}>{{ __('Pastor') }}</option>
                                                <option value="secretary" {{ old('role') === 'secretary' ? 'selected' : '' }}>{{ __('Secretary') }}</option>
                                            </select>
                                        </div>
                                        <div class="form-group" id="linkedMemberGroup" style="{{ in_array(old('role'), ['pastor', 'secretary'], true) ? '' : 'display:none' }}">
                                            <label class="control-label" id="linkedMemberLabel">{{ __('Link to member profile') }} <span class="text-danger">*</span></label>
                                            <select name="member_id" id="linkedMemberSelect" class="form-control">
                                                <option value="">-- {{ __('Select member') }} --</option>
                                            </select>
                                            <small class="text-muted" id="linkedMemberHint">{{ __('Select a member to link this account.') }}</small>
                                        </div>
                                        <template id="pastorMemberOptions">
                                            @foreach($pastorMembers as $m)
                                                <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}>
                                                    {{ $m->name }}@if($m->phone_number) — {{ $m->phone_number }}@endif
                                                </option>
                                            @endforeach
                                        </template>
                                        <template id="secretaryMemberOptions">
                                            @foreach($secretaryMembers as $m)
                                                <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}>
                                                    {{ $m->name }}@if($m->phone_number) — {{ $m->phone_number }}@endif
                                                </option>
                                            @endforeach
                                        </template>
                                        <p class="text-muted">
                                            <i class="fa fa-info-circle"></i>
                                            {{ __('A random password will be auto-generated and displayed after saving.') }}
                                        </p>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fa fa-check-circle"></i> {{ __('Create account') }}
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-7">
                                    <h4 class="mb-3"><i class="fa fa-users"></i> {{ __('Existing users') }}</h4>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Email') }}</th>
                                                    <th>{{ __('Role') }}</th>
                                                    <th>{{ __('Linked member') }}</th>
                                                    <th>{{ __('Joined date') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($users as $user)
                                                    <tr>
                                                        <td><strong>{{ $user->name }}</strong></td>
                                                        <td>{{ $user->email ?: '—' }}</td>
                                                        <td><span class="badge badge-{{ $user->isPastor() ? 'info' : ($user->isSecretary() ? 'secondary' : 'primary') }}">{{ $user->roleLabel() }}</span></td>
                                                        <td>{{ $user->member?->name ?? '—' }}</td>
                                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-3">{{ __('No users yet.') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center mt-3">{{ $users->links() }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- System Logs --}}
                        <div class="tab-pane fade {{ $activeTab === 'logs' ? 'show active' : '' }}" id="tab-logs">
                            <p class="text-muted mb-3">{{ __('Track user logins, actions, and devices used in the system.') }}</p>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('Date & time') }}</th>
                                            <th>{{ __('User') }}</th>
                                            <th>{{ __('Action') }}</th>
                                            <th>{{ __('Device') }}</th>
                                            <th>{{ __('IP address') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($systemLogs as $entry)
                                            <tr class="{{ $entry->action === 'login' ? 'table-success' : ($entry->action === 'logout' ? 'table-secondary' : '') }}">
                                                <td>
                                                    <strong>{{ $entry->created_at->format('d/m/Y') }}</strong>
                                                    <br><small class="text-muted">{{ $entry->created_at->format('H:i:s') }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $entry->user_name ?? __('Guest') }}</strong>
                                                    @if($entry->user?->email)
                                                        <br><small class="text-muted">{{ $entry->user->email }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $entry->description }}</td>
                                                <td><small>{{ $entry->deviceLabel() }}</small></td>
                                                <td>{{ $entry->ip_address ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    {{ __('No system activity recorded yet. Actions will appear here after users log in.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">{{ $systemLogs->links() }}</div>
                        </div>

                        {{-- Sessions --}}
                        <div class="tab-pane fade {{ $activeTab === 'sessions' ? 'show active' : '' }}" id="tab-sessions">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="p-3 rounded bg-light">
                                        <small class="text-muted d-block">{{ __('Session driver') }}</small>
                                        <strong>{{ $sessionConfig['driver'] }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded bg-light">
                                        <small class="text-muted d-block">{{ __('Session lifetime') }}</small>
                                        <strong>{{ $sessionConfig['lifetime'] }} {{ __('minutes') }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded bg-light">
                                        <small class="text-muted d-block">{{ __('Expire on browser close') }}</small>
                                        <strong>{{ $sessionConfig['expire_on_close'] ? __('Yes') : __('No') }}</strong>
                                    </div>
                                </div>
                            </div>

                            <h4 class="mb-3"><i class="fa fa-desktop"></i> {{ __('Active sessions') }}</h4>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('User') }}</th>
                                            <th>{{ __('IP address') }}</th>
                                            <th>{{ __('Device / Browser') }}</th>
                                            <th>{{ __('Last activity') }}</th>
                                            <th width="100"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sessions as $session)
                                            <tr class="{{ $session->is_current ? 'table-info' : '' }}">
                                                <td>
                                                    @if($session->user_name)
                                                        <strong>{{ $session->user_name }}</strong>
                                                        <br><small class="text-muted">{{ $session->user_email }}</small>
                                                    @else
                                                        <span class="text-muted">{{ __('Guest') }}</span>
                                                    @endif
                                                    @if($session->is_current)
                                                        <br><span class="badge badge-primary">{{ __('Current session') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $session->ip_address ?? '—' }}</td>
                                                <td><small>{{ Str::limit($session->user_agent ?? '—', 60) }}</small></td>
                                                <td>{{ $session->last_activity_at->diffForHumans() }}</td>
                                                <td class="text-center">
                                                    @if(!$session->is_current)
                                                        <form method="POST" action="{{ route('settings.sessions.destroy', $session->id) }}"
                                                            data-swal-confirm="{{ __('Revoke this session?') }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Revoke') }}">
                                                                <i class="fa fa-sign-out"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">{{ __('No active sessions.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .settings-tabs .nav-link { font-weight: 500; }
    .settings-tabs .nav-link.active { color: #940000; border-bottom: 2px solid #940000; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var tabMap = {
        '#tab-church': 'church',
        '#tab-sms': 'sms',
        '#tab-general': 'general',
        '#tab-users': 'users',
        '#tab-logs': 'logs',
        '#tab-sessions': 'sessions'
    };

    $('.settings-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tab = tabMap[$(e.target).attr('href')] || 'church';
        var url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        url.searchParams.delete('users_page');
        if (tab !== 'users') {
            history.replaceState(null, '', url.pathname + '?' + url.searchParams.toString());
        }
    });

    function updateLinkedMemberField() {
        var role = $('#userRole').val();
        var show = role === 'pastor' || role === 'secretary';
        $('#linkedMemberGroup').toggle(show);
        if (!show) {
            return;
        }
        var placeholder = role === 'pastor'
            ? @json('-- ' . __('Select pastor') . ' --')
            : @json('-- ' . __('Select secretary') . ' --');
        var options = role === 'pastor'
            ? $('#pastorMemberOptions').html()
            : $('#secretaryMemberOptions').html();
        $('#linkedMemberSelect').html('<option value="">' + placeholder + '</option>' + options);
        $('#linkedMemberLabel').text(role === 'pastor'
            ? @json(__('Link to pastor member'))
            : @json(__('Link to secretary member')));
        $('#linkedMemberHint').text(role === 'pastor'
            ? @json(__('Member must have a Pastor leadership role'))
            : @json(__('Member must have a Church Secretary leadership role')));
    }

    $('#userRole').on('change', updateLinkedMemberField);
    updateLinkedMemberField();
})();
</script>
@endpush

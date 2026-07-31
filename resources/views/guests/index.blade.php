@extends('layouts.app')

@section('title', __('Guests'))

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-handshake-o"></i> {{ __('Guests') }}</h1>
            <p>{{ __('Record visitors and promised guests for reminders and thank you messages') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">{{ __('Services & Attendance') }}</li>
            <li class="breadcrumb-item">{{ __('Guests') }}</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-6 col-lg-3">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-users fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Visited guests') }}</h4>
                    <p><b>{{ number_format($stats['visited']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-clock-o fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Promised (pending)') }}</h4>
                    <p><b>{{ number_format($stats['promised_pending']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-check fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Promised (attended)') }}</h4>
                    <p><b>{{ number_format($stats['promised_attended']) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-bell fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Upcoming promised') }}</h4>
                    <p><b>{{ number_format($stats['upcoming_promised']) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-plus"></i> {{ __('Add guest') }}</h3>
                <div class="tile-body">
                    <form method="POST" action="{{ route('guests.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">{{ __('Guest type') }} <span class="text-danger">*</span></label>
                            <select name="guest_type" class="form-control" id="guestTypeSelect" required>
                                <option value="visited" {{ old('guest_type', request('type') === 'promised' ? '' : 'visited') === 'visited' ? 'selected' : '' }}>
                                    {{ __('Visited guest') }} — {{ __('already came') }}
                                </option>
                                <option value="promised" {{ old('guest_type', request('type') === 'promised' ? 'promised' : '') === 'promised' ? 'selected' : '' }}>
                                    {{ __('Promised guest') }} — {{ __('will come') }}
                                </option>
                            </select>
                            @error('guest_type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Full name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required
                                placeholder="{{ __('Guest name') }}">
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Phone') }}</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                                placeholder="07XXXXXXXX">
                            <small class="text-muted">{{ __('Required for SMS reminder or thank you') }}</small>
                            @error('phone')<small class="text-danger d-block">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Coming from') }}</label>
                            <input type="text" name="coming_from" class="form-control" value="{{ old('coming_from') }}"
                                placeholder="{{ __('Church, city, or organization') }}">
                            @error('coming_from')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Church service') }}</label>
                            <select name="event_id" class="form-control" id="guestEventSelect">
                                <option value="">-- {{ __('Select service (optional)') }} --</option>
                                @foreach($serviceEvents as $event)
                                    <option value="{{ $event->id }}" {{ (string) old('event_id') === (string) $event->id ? 'selected' : '' }}
                                        data-date="{{ $event->start_at?->format('Y-m-d') }}">
                                        {{ $event->start_at?->format('d/m/Y') }}
                                        — {{ $event->theme ?: $event->service_type ?: __('Church Service') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Service date') }}</label>
                            <input type="date" name="service_date" id="guestServiceDate" class="form-control"
                                value="{{ old('service_date', date('Y-m-d')) }}">
                            @error('service_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('Notes') }}</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="{{ __('Optional notes') }}">{{ old('notes') }}</textarea>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit">
                            <i class="fa fa-save"></i> {{ __('Save guest') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title mb-0">{{ __('Guest list') }}</h3>
                    <p class="mb-0">
                        <a href="{{ route('guests.index') }}" class="btn btn-sm {{ $type === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('All') }}</a>
                        <a href="{{ route('guests.index', ['type' => 'visited']) }}" class="btn btn-sm {{ $type === 'visited' ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('Visited') }}</a>
                        <a href="{{ route('guests.index', ['type' => 'promised']) }}" class="btn btn-sm {{ $type === 'promised' ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('Promised') }}</a>
                    </p>
                </div>
                <div class="tile-body">
                    <form method="GET" action="{{ route('guests.index') }}" class="mb-3">
                        @if($type !== 'all')
                            <input type="hidden" name="type" value="{{ $type }}">
                        @endif
                        <div class="row">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                    placeholder="{{ __('Search name, phone, church...') }}">
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="service_date" class="form-control" value="{{ request('service_date') }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> {{ __('Filter') }}</button>
                            </div>
                        </div>
                    </form>

                    @if($guests->count())
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Coming from') }}</th>
                                        <th>{{ __('Service date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('SMS') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($guests as $guest)
                                        <tr>
                                            <td>
                                                <strong>{{ $guest->name }}</strong>
                                                @if($guest->notes)
                                                    <br><small class="text-muted">{{ Str::limit($guest->notes, 40) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $guest->isPromised() ? 'warning' : 'info' }}">
                                                    {{ \App\Models\ServiceGuest::typeLabel($guest->guest_type) }}
                                                </span>
                                            </td>
                                            <td>{{ $guest->phone ?: '—' }}</td>
                                            <td>{{ $guest->coming_from ?: '—' }}</td>
                                            <td>{{ $guest->serviceDateLabel() }}</td>
                                            <td>
                                                <span class="badge badge-{{ \App\Models\ServiceGuest::statusBadge($guest->status) }}">
                                                    {{ \App\Models\ServiceGuest::statusLabel($guest->status) }}
                                                </span>
                                            </td>
                                            <td class="small text-nowrap">
                                                @if($guest->reminder_sent_at)
                                                    <span class="text-success" title="{{ $guest->reminder_sent_at->format('d/m/Y H:i') }}">
                                                        <i class="fa fa-bell"></i> {{ __('Reminder') }}
                                                    </span><br>
                                                @endif
                                                @if($guest->thank_you_sent_at)
                                                    <span class="text-success" title="{{ $guest->thank_you_sent_at->format('d/m/Y H:i') }}">
                                                        <i class="fa fa-heart"></i> {{ __('Thanks') }}
                                                    </span>
                                                @endif
                                                @if(!$guest->reminder_sent_at && !$guest->thank_you_sent_at)
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                @if($guest->isPromised())
                                                    @if($guest->status === 'pending')
                                                        <form action="{{ route('guests.mark-attended', $guest) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-success" title="{{ __('Mark attended') }}">
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('guests.mark-missed', $guest) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-secondary" title="{{ __('Did not attend') }}">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($guest->canSendReminder())
                                                        <button type="button" class="btn btn-xs btn-warning sms-btn"
                                                            data-toggle="modal" data-target="#smsModal"
                                                            data-action="{{ route('guests.reminder', $guest) }}"
                                                            data-name="{{ $guest->name }}"
                                                            data-message="{{ $guest->defaultReminderMessage() }}"
                                                            data-title="{{ __('Send reminder') }}">
                                                            <i class="fa fa-bell"></i>
                                                        </button>
                                                    @endif
                                                    @if($guest->status !== 'attended')
                                                        <form action="{{ route('guests.convert-visited', $guest) }}" method="POST" class="d-inline"
                                                            onsubmit="return confirm(@json(__('Move this guest to visited list?')));">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-info" title="{{ __('Record as visited') }}">
                                                                <i class="fa fa-user-plus"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                                @if($guest->canSendThankYou())
                                                    <button type="button" class="btn btn-xs btn-primary sms-btn"
                                                        data-toggle="modal" data-target="#smsModal"
                                                        data-action="{{ route('guests.thank-you', $guest) }}"
                                                        data-name="{{ $guest->name }}"
                                                        data-message="{{ $guest->defaultThankYouMessage() }}"
                                                        data-title="{{ __('Send thank you') }}">
                                                        <i class="fa fa-envelope"></i>
                                                    </button>
                                                @endif
                                                @if(auth()->user()->isAdmin())
                                                <form action="{{ route('guests.destroy', $guest) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm(@json(__('Delete this guest record?')));">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">{{ $guests->links() }}</div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fa fa-info-circle"></i> {{ __('No guests found.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="smsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="smsForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="smsModalTitle">{{ __('Send SMS') }}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">{{ __('Recipient') }}: <strong id="smsRecipientName"></strong></p>
                        <div class="form-group mb-0">
                            <label class="control-label">{{ __('Message') }}</label>
                            <textarea name="message" id="smsMessage" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-send"></i> {{ __('Send SMS') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var eventSelect = document.getElementById('guestEventSelect');
    var dateInput = document.getElementById('guestServiceDate');
    if (eventSelect && dateInput) {
        eventSelect.addEventListener('change', function () {
            var opt = eventSelect.options[eventSelect.selectedIndex];
            var date = opt ? opt.getAttribute('data-date') : '';
            if (date) dateInput.value = date;
        });
    }

    document.querySelectorAll('.sms-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('smsForm').action = btn.getAttribute('data-action');
            document.getElementById('smsModalTitle').textContent = btn.getAttribute('data-title');
            document.getElementById('smsRecipientName').textContent = btn.getAttribute('data-name');
            document.getElementById('smsMessage').value = btn.getAttribute('data-message');
        });
    });
})();
</script>
@endpush

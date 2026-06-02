@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-envelope"></i> Send Follow Up SMS</h1>
            <p>Send a message to {{ $member->name }}</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="tile">
                <h3 class="tile-title">Compose Message</h3>
                <div class="tile-body">
                    <form action="{{ route('follow-ups.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="member_id" value="{{ $member->id }}">

                        <div class="form-group">
                            <label class="control-label">To: Member Name</label>
                            <input class="form-control" type="text" value="{{ $member->name }}" readonly>
                        </div>
                        <div class="form-group">
                            <label class="control-label">To: Phone Number</label>
                            <input class="form-control" type="text" value="{{ $member->phone_number }}" readonly>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Message</label>
                            <textarea class="form-control" name="message" rows="4" required
                                placeholder="Type the SMS content here..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Schedule At (Optional)</label>
                            <input class="form-control" type="datetime-local" name="scheduled_at">
                            <small class="form-text text-muted">Leave blank to send immediately.</small>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-paper-plane"></i>Send
                                / Schedule SMS</button>
                            <a class="btn btn-secondary" href="{{ route('members.index') }}"><i
                                    class="fa fa-fw fa-lg fa-times-circle"></i>Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-list"></i> Follow Up SMS Log</h1>
            <p>View all sent and scheduled SMS messages</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member Name</th>
                                    <th>Phone Number</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Scheduled At</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($followUps as $followUp)
                                    <tr>
                                        <td>{{ $followUp->id }}</td>
                                        <td>{{ $followUp->member?->name ?? '—' }}</td>
                                        <td>{{ $followUp->member?->phone_number ?? '—' }}</td>
                                        <td>{{ Str::limit($followUp->message, 50) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $followUp->status == 'sent' ? 'success' : 'warning' }}">
                                                {{ ucfirst($followUp->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $followUp->scheduled_at ? $followUp->scheduled_at : 'Immediate' }}</td>
                                        <td>{{ $followUp->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No follow-ups found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
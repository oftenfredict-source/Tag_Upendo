@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-list"></i> Live SMS Delivery Logs</h1>
            <p>Direct real-time delivery reports from NextSMS Servers</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th>Date Sent</th>
                                    <th>Recipient</th>
                                    <th>Message</th>
                                    <th>Cost (TSH)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ isset($log['date']) ? \Carbon\Carbon::parse($log['date'])->format('Y-m-d H:i') : '-' }}</td>
                                        <td>{{ $log['to'] ?? '-' }}</td>
                                        <td>{{ Str::limit($log['message'] ?? '-', 50) }}</td>
                                        <td>{{ $log['price'] ?? '0' }}</td>
                                        <td>
                                            @php
                                                $status = $log['status']['name'] ?? 'UNKNOWN';
                                                $badge = 'secondary';
                                                if (str_contains($status, 'DELIVERED') || str_contains($status, 'SUCCESS')) $badge = 'success';
                                                elseif (str_contains($status, 'PENDING') || str_contains($status, 'SENT') || str_contains($status, 'ENROUTE')) $badge = 'warning';
                                                elseif (str_contains($status, 'REJECTED') || str_contains($status, 'FAILED')) $badge = 'danger';
                                            @endphp
                                            <span class="badge badge-{{ $badge }}">{{ $status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No logs found on NextSMS servers, or the internet connection dropped.</td>
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

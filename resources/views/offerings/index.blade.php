@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-money"></i> Offerings & Collections</h1>
            <p>Record Sunday and mid-week offerings</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-6 col-lg-3">
            <div class="widget-small info coloured-icon"><i class="icon fa fa-heart fa-3x"></i>
                <div class="info">
                    <h4>Sadaka</h4>
                    <p><b>{{ number_format($totals['Sadaka'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small warning coloured-icon"><i class="icon fa fa-building fa-3x"></i>
                <div class="info">
                    <h4>Ujenzi</h4>
                    <p><b>{{ number_format($totals['Ujenzi'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small danger coloured-icon"><i class="icon fa fa-gift fa-3x"></i>
                <div class="info">
                    <h4>Shukran</h4>
                    <p><b>{{ number_format($totals['Shukran'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small primary coloured-icon"><i class="icon fa fa-ellipsis-h fa-3x"></i>
                <div class="info">
                    <h4>Other</h4>
                    <p><b>{{ number_format($totals['Other'], 0) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="tile">
                <h3 class="tile-title">Record New Entry</h3>
                <div class="tile-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <form action="{{ route('offerings.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">Category</label>
                            <select class="form-control" name="category" required>
                                <option value="Sadaka">Sadaka (Offering)</option>
                                <option value="Ujenzi">Ujenzi (Building)</option>
                                <option value="Shukran">Shukran (Thanksgiving)</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Service Type</label>
                            <select class="form-control" name="service_type" required>
                                @foreach(\App\Models\Event::serviceTypes() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Amount (TSH)</label>
                            <input class="form-control" type="number" name="amount" step="0.01" required
                                placeholder="Enter amount">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Collection Date</label>
                            <input class="form-control" type="date" name="collection_date" 
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" rows="3" 
                                placeholder="Enter details..."></textarea>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit"><i
                                    class="fa fa-fw fa-lg fa-check-circle"></i>Save Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="tile">
                <h3 class="tile-title">Entry History</h3>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Service</th>
                                    <th>Amount (TSH)</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($offerings as $offering)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($offering->collection_date)->format('Y-m-d') }}</td>
                                        <td>
                                            @php
                                                $badge = 'secondary';
                                                if($offering->category == 'Sadaka') $badge = 'info';
                                                elseif($offering->category == 'Ujenzi') $badge = 'warning';
                                                elseif($offering->category == 'Shukran') $badge = 'danger';
                                                elseif($offering->category == 'Zaka') $badge = 'secondary';
                                                else $badge = 'primary';
                                            @endphp
                                            <span class="badge badge-{{ $badge }}">{{ $offering->category }}</span>
                                        </td>
                                        <td>{{ $offering->service_type }}</td>
                                        <td><b>{{ number_format($offering->amount, 0) }}</b></td>
                                        <td>{{ $offering->description ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $offerings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

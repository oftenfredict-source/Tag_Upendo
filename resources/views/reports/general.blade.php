@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-file-text-o"></i> General Summary Report</h1>
            <p>Taarifa ya Jumla ya Kanisa</p>
        </div>
        <button class="btn btn-primary" type="button" onclick="window.print();"><i class="fa fa-print"></i> Print
            Report</button>
    </div>

    <div class="row">
        <!-- 1. Church & Member Statistics -->
        <div class="col-md-6">
            <div class="tile">
                <h3 class="tile-title">1. Church & Member Statistics</h3>
                <div class="tile-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Total Members:</th>
                            <td><b>{{ $memberStats['total'] }}</b></td>
                        </tr>
                    </table>
                    <h5 class="mt-4">Members by Department:</h5>
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Member Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($memberStats['by_dept'] as $dept)
                                <tr>
                                    <td>{{ $dept->name }}</td>
                                    <td>{{ $dept->members_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. Financial Overview (All Time) -->
        <div class="col-md-6">
            <div class="tile">
                <h3 class="tile-title">2. Financial Overview (All Time)</h3>
                <div class="tile-body">
                    <div class="row text-center mb-4">
                        <div class="col">
                            <h6>Total Income</h6>
                            <h4 class="text-success">{{ number_format($financials['total_income'], 0) }}</h4>
                        </div>
                        <div class="col">
                            <h6>Total Expenses</h6>
                            <h4 class="text-danger">{{ number_format($financials['total_expense'], 0) }}</h4>
                        </div>
                        <div class="col">
                            <h6>Net Balance</h6>
                            <h4
                                class="{{ ($financials['total_income'] - $financials['total_expense']) >= 0 ? 'text-primary' : 'text-danger' }}">
                                {{ number_format($financials['total_income'] - $financials['total_expense'], 0) }}
                            </h4>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{ __('Income by category') }}:</h6>
                            <ul class="list-unstyled">
                                @foreach($financials['by_income_cat'] as $inc)
                                    <li><b>{{ $inc->category }}:</b> {{ number_format($inc->total, 0) }}</li>
                                @endforeach
                                <li><b>{{ __('Tithes') }}:</b> {{ number_format($financials['total_tithes'], 0) }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Expenses by Category:</h6>
                            <ul class="list-unstyled">
                                @foreach($financials['by_expense_cat'] as $exp)
                                    <li><b>{{ $exp->category }}:</b> {{ number_format($exp->total, 0) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Asset Inventory Summary -->
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title">3. Asset Inventory Summary</h3>
                <div class="tile-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h5>Total Assets: {{ $assetStats['total_items'] }}</h5>
                        </div>
                        <div class="col-md-8">
                            <span class="mr-3"><b>Status Breakdown:</b></span>
                            @foreach($assetStats['by_status'] as $status)
                                <span class="badge badge-secondary mr-2">{{ $status->status }}: {{ $status->count }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Asset Name</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Purchase Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assetStats['list'] as $asset)
                                    <tr>
                                        <td>{{ $asset->name }}</td>
                                        <td>{{ $asset->category }}</td>
                                        <td>{{ $asset->quantity }}</td>
                                        <td>{{ $asset->status }}</td>
                                        <td>{{ $asset->purchase_date ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {

            .app-sidebar,
            .app-header,
            .btn,
            .app-title p {
                display: none !important;
            }

            .app-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            .tile {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
@endsection
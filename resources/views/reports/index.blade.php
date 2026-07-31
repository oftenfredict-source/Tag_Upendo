@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-bar-chart"></i> Reports & Statistics</h1>
            <p>Visual overview for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</p>
        </div>
        <form class="form-inline" action="{{ route('reports.index') }}" method="GET">
            <div class="form-group mr-2">
                <label class="control-label mr-2">Select Month: </label>
                <input class="form-control" type="month" name="month" value="{{ $selectedMonth }}" 
                    onchange="this.form.submit()">
            </div>
            <button class="btn btn-primary" type="submit">Filter</button>
        </form>
    </div>

    <!-- Quick Stats Cards (Filtered for selected month) -->
    <div class="row">
        <div class="col-md-6 col-lg-3">
            <div class="widget-small primary coloured-icon"><i class="icon fa fa-percent fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Tithes') }}</h4>
                    <p><b>TSH {{ number_format($stats['monthly_tithes'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small info coloured-icon"><i class="icon fa fa-heart fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Offerings') }}</h4>
                    <p><b>TSH {{ number_format($stats['monthly_offerings'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small success coloured-icon"><i class="icon fa fa-calendar-check-o fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Monthly Income') }}</h4>
                    <p><b>TSH {{ number_format($stats['monthly_income'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small danger coloured-icon"><i class="icon fa fa-minus-circle fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Monthly Expense') }}</h4>
                    <p><b>TSH {{ number_format($stats['monthly_expenses'], 0) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 col-lg-3">
            <div class="widget-small {{ $stats['net_income'] >= 0 ? 'primary' : 'warning' }} coloured-icon">
                <i class="icon fa {{ $stats['net_income'] >= 0 ? 'fa-plus-circle' : 'fa-exclamation-triangle' }} fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Month Balance') }}</h4>
                    <p><b>TSH {{ number_format($stats['net_income'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small info coloured-icon"><i class="icon fa fa-money fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Total Income') }}</h4>
                    <p><b>TSH {{ number_format($stats['total_income'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small primary coloured-icon"><i class="icon fa fa-database fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Total Tithes') }}</h4>
                    <p><b>TSH {{ number_format($stats['total_tithes'], 0) }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small warning coloured-icon"><i class="icon fa fa-database fa-3x"></i>
                <div class="info">
                    <h4>{{ __('Total Offerings') }}</h4>
                    <p><b>TSH {{ number_format($stats['total_offerings'], 0) }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Monthly Trends Chart (Income vs Expense) -->
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title">Income vs Expenses (Last 6 Months)</h3>
                <div class="report-trend-chart">
                    <canvas id="combinedTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Offerings by Category (Filtered for selected month) -->
        <div class="col-md-6">
            <div class="tile">
                <h3 class="tile-title">Income Distribution ({{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('M Y') }})</h3>
                <div class="embed-responsive embed-responsive-16by9">
                    <canvas class="embed-responsive-item" id="pieChartCat"></canvas>
                </div>
            </div>
        </div>

        <!-- Expenses by Category (Filtered for selected month) -->
        <div class="col-md-6">
            <div class="tile">
                <h3 class="tile-title">Expense Distribution ({{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('M Y') }})</h3>
                <div class="embed-responsive embed-responsive-16by9">
                    <canvas class="embed-responsive-item" id="pieChartExpense"></canvas>
                </div>
            </div>
        </div>

        <!-- Members by Department (Overall) -->
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title">Member Distribution by Department</h3>
                <div class="embed-responsive embed-responsive-16by9" style="max-height: 400px;">
                    <canvas class="embed-responsive-item" id="pieChartDept"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .report-trend-chart {
        position: relative;
        max-width: 720px;
        height: 260px;
        margin: 0 auto;
    }
    .report-trend-chart canvas {
        width: 100% !important;
        height: 100% !important;
    }
</style>
@endpush

@push('scripts')
    <script type="text/javascript" src="{{ asset('vali-master/docs/js/plugins/chart.js') }}"></script>
    <script type="text/javascript">
        // Data for Combined Trend
        var combinedData = {
            labels: {!! json_encode($labels) !!},
            datasets: [
                {
                    label: "Income ({{ __('Tithes') }} + {{ __('Offerings') }})",
                    fillColor: "rgba(32, 201, 151, 0.2)",
                    strokeColor: "rgba(32, 201, 151, 1)",
                    pointColor: "rgba(32, 201, 151, 1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(32, 201, 151, 1)",
                    data: {!! json_encode($incomeData) !!}
                },
                {
                    label: "Expenses",
                    fillColor: "rgba(220, 53, 69, 0.2)",
                    strokeColor: "rgba(220, 53, 69, 1)",
                    pointColor: "rgba(220, 53, 69, 1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(220, 53, 69, 1)",
                    data: {!! json_encode($expenseData) !!}
                }
            ]
        };

        // Data for Pie Chart Cat (Income incl. Tithes)
        var pieDataCat = {!! json_encode(collect($incomeBySource)->map(function($item) {
            return [
                'label' => $item['label'],
                'value' => $item['value'],
                'color' => '#' . substr(md5($item['label']), 0, 6),
                'highlight' => '#' . substr(md5($item['label']), 0, 6)
            ];
        })->values()) !!};

        // Data for Pie Chart Expense
        var pieDataExpense = {!! json_encode($expensesByCat->map(function($item) {
            return [
                'label' => $item['label'],
                'value' => $item['value'],
                'color' => '#' . substr(md5($item['label'] . 'exp'), 0, 6),
                'highlight' => '#' . substr(md5($item['label'] . 'exp'), 0, 6)
            ];
        })->values()) !!};

        // Data for Pie Chart Dept
        var pieDataDept = {!! json_encode($membersByDept->map(function($item) {
            return [
                'label' => $item['label'],
                'value' => $item['value'],
                'color' => '#' . substr(md5($item['label'] . 'alt'), 0, 6),
                'highlight' => '#' . substr(md5($item['label'] . 'alt'), 0, 6)
            ];
        })->values()) !!};

        // Initialize Charts
        var ctxt = $("#combinedTrendChart").get(0).getContext("2d");
        var trendChart = new Chart(ctxt).Line(combinedData);

        var ctxpCat = $("#pieChartCat").get(0).getContext("2d");
        var pieChartCat = new Chart(ctxpCat).Pie(pieDataCat);

        var ctxpExp = $("#pieChartExpense").get(0).getContext("2d");
        var pieChartExp = new Chart(ctxpExp).Pie(pieDataExpense);

        var ctxpDept = $("#pieChartDept").get(0).getContext("2d");
        var pieChartDept = new Chart(ctxpDept).Pie(pieDataDept);
    </script>
@endpush
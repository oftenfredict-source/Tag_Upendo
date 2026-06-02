@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-minus-circle"></i> Expenses (Matumizi)</h1>
            <p>Track church spending and costs</p>
        </div>
        <div class="tile-title">
            <h3>Total: {{ number_format($totalExpenses, 0) }} TSH</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="tile">
                <h3 class="tile-title">Record New Expense</h3>
                <div class="tile-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <form action="{{ route('expenses.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">Category</label>
                            <select class="form-control" name="category" required>
                                <option value="Electricity & Water">Electricity & Water</option>
                                <option value="Charity & Help">Charity & Help (Misaada)</option>
                                <option value="Maintenance">Maintenance & Repairs</option>
                                <option value="Salaries & Support">Salaries & Support</option>
                                <option value="Stationery & Office">Stationery & Office</option>
                                <option value="Construction">Construction (Ujenzi)</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Amount (TSH)</label>
                            <input class="form-control" type="number" name="amount" step="0.01" required
                                placeholder="Enter amount">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Expense Date</label>
                            <input class="form-control" type="date" name="expense_date" 
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
                <h3 class="tile-title">Expense History</h3>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Amount (TSH)</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $expense)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') }}</td>
                                        <td><span class="badge badge-info">{{ $expense->category }}</span></td>
                                        <td><b>{{ number_format($expense->amount, 0) }}</b></td>
                                        <td>{{ $expense->description ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $expenses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

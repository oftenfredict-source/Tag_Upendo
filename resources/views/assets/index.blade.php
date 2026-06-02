@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-archive"></i> Church Assets (Mali za Kanisa)</h1>
            <p>Track and manage church property and equipment</p>
        </div>
        <div class="tile-title">
            <h3>Total Items: {{ $totalQuantity }}</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="tile">
                <h3 class="tile-title">Add New Asset</h3>
                <div class="tile-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <form action="{{ route('assets.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">Asset Name</label>
                            <input class="form-control" type="text" name="name" required placeholder="e.g., Plastic Chairs">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Category</label>
                            <select class="form-control" name="category" required>
                                <option value="Furniture">Furniture (Samani)</option>
                                <option value="Electronics">Electronics (Vifaa vya Elektroniki)</option>
                                <option value="Instruments">Musical Instruments</option>
                                <option value="Construction">Construction Materials</option>
                                <option value="Land & Buildings">Land & Buildings</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Quantity</label>
                            <input class="form-control" type="number" name="quantity" value="1" required>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="Good">Good (Nzima)</option>
                                <option value="Broken">Broken (Imeharibika)</option>
                                <option value="Repairing">Under Repair (Inatengenezwa)</option>
                                <option value="Missing">Missing (Haikuonekana)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Purchase Date (Optional)</label>
                            <input class="form-control" type="date" name="purchase_date">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" rows="3"
                                placeholder="Enter details..."></textarea>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Save
                                Asset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="tile">
                <h3 class="tile-title">Asset Inventory</h3>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Purchase Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assets as $asset)
                                    <tr>
                                        <td><b>{{ $asset->name }}</b></td>
                                        <td>{{ $asset->category }}</td>
                                        <td>{{ $asset->quantity }}</td>
                                        <td>
                                            @php
                                                $badge = 'success';
                                                if ($asset->status == 'Broken')
                                                    $badge = 'danger';
                                                elseif ($asset->status == 'Repairing')
                                                    $badge = 'warning';
                                                elseif ($asset->status == 'Missing')
                                                    $badge = 'secondary';
                                            @endphp
                                            <span class="badge badge-{{ $badge }}">{{ $asset->status }}</span>
                                        </td>
                                        <td>{{ $asset->purchase_date ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No assets found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $assets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-calendar-plus-o"></i> Anza Mahudhurio</h1>
            <p>Unda rekodi ya ibada ya leo kisha weka alama za waliohudhuria</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item">New</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="tile">
                <h3 class="tile-title">Taarifa za Ibada</h3>
                <div class="tile-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('attendance.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">Tarehe ya Ibada <span class="text-danger">*</span></label>
                            <input type="date" name="service_date" class="form-control" required
                                value="{{ old('service_date', date('Y-m-d')) }}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Aina ya Ibada <span class="text-danger">*</span></label>
                            <select name="service_type" class="form-control" required>
                                @foreach(\App\Models\Event::serviceTypes() as $value => $label)
                                    <option value="{{ $value }}" {{ old('service_type', 'First Service (Sunday)') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Kichwa (si lazima)</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}"
                                placeholder="mf. Ibada ya asubuhi">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Kiongozi wa Ibada</label>
                            <input type="text" name="leader" class="form-control" value="{{ old('leader') }}"
                                placeholder="mf. Mch. John...">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Maelezo</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Maelezo ya ziada">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-arrow-right"></i> Endelea — Weka Mahudhurio
                        </button>
                        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Ghairi</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-child"></i> Ongeza Mtoto</h1>
            <p>Mtoto wa <strong>{{ $parent->name }}</strong></p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.show', $parent) }}">{{ $parent->name }}</a></li>
            <li class="breadcrumb-item">Ongeza Mtoto</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="tile">
                <h3 class="tile-title">Taarifa za Mtoto</h3>
                <div class="tile-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        Jaza jina, jinsia na tarehe ya kuzaliwa tu. Taarifa nyingine zitachukuliwa kutoka kwa mzazi.
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('members.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $parent->id }}">
                        <input type="hidden" name="is_child" value="1">

                        <div class="form-group">
                            <label class="control-label">Jina Kamili <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="name" value="{{ old('name') }}" required
                                placeholder="Jina la mtoto" autofocus>
                        </div>

                        <div class="form-group">
                            <label class="control-label">Jinsia</label>
                            <select class="form-control" name="gender">
                                <option value="">-- Chagua --</option>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label">Tarehe ya Kuzaliwa</label>
                            <input class="form-control" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
                        </div>

                        <div class="form-group mb-0">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-check-circle"></i> Hifadhi Mtoto
                            </button>
                            <a href="{{ route('members.show', $parent) }}" class="btn btn-secondary">Ghairi</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

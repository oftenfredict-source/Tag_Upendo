@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-check-square-o"></i> Mahudhurio ya Ibada</h1>
            <p>Rekodi na angalia waliohudhuria kila ibada</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item">Attendance</li>
        </ul>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Anza Mahudhurio ya Ibada Leo
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title">Historia ya Ibada</h3>
                <div class="tile-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="widget-small primary coloured-icon mb-4">
                        <i class="icon fa fa-users fa-3x"></i>
                        <div class="info">
                            <h4>Jumla ya Wanachama</h4>
                            <p><b>{{ $totalMembers }}</b> — hawa ndio wanaochukuliwa kwenye orodha ya mahudhurio</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Tarehe</th>
                                    <th>Aina ya Ibada</th>
                                    <th>Mahudhurio</th>
                                    <th>Asilimia</th>
                                    <th class="text-nowrap">Vitendo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    @php
                                        $pct = $totalMembers > 0
                                            ? round(($service->attendances_count / $totalMembers) * 100)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $service->service_date->format('d/m/Y') }}</td>
                                        <td>
                                            <strong>{{ $service->displayName() }}</strong>
                                            @if($service->title && $service->service_type !== $service->title)
                                                <br><small class="text-muted">{{ $service->service_type }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-success">{{ $service->attendances_count }}</span>
                                            / {{ $totalMembers }}
                                        </td>
                                        <td>{{ $pct }}%</td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('attendance.collect', $service) }}" class="btn btn-sm btn-primary"
                                                title="Weka / badilisha mahudhurio">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <a href="{{ route('attendance.show', $service) }}" class="btn btn-sm btn-info"
                                                title="Angalia ripoti">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <form action="{{ route('attendance.destroy', $service) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Futa rekodi hii ya ibada na mahudhurio yake?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Futa">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            Hakuna ibada iliyorekodiwa bado.
                                            <a href="{{ route('attendance.create') }}">Anza ibada ya kwanza</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $services->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

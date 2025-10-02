@extends('app')

@section('title', 'Tracking Kendaraan')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Tracking Kendaraan</h2>

    <div class="accordion" id="companyAccordion">
        @foreach($vehicles as $company => $niks)
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="heading-{{ $loop->index }}">
                    <button class="accordion-button collapsed" type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapse-{{ $loop->index }}" 
                            aria-expanded="false" aria-controls="collapse-{{ $loop->index }}">
                        {{ strtoupper($company) }}
                    </button>
                </h2>
                <div id="collapse-{{ $loop->index }}" class="accordion-collapse collapse" 
                     aria-labelledby="heading-{{ $loop->index }}" 
                     data-bs-parent="#companyAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            @foreach($niks as $data)
                                <div class="col-md-4">
                                    <div class="card mb-3 shadow-sm 
                                        @if($data['status'] === 'normal') border-success 
                                        @elseif($data['status'] === 'refuel') border-warning 
                                        @elseif($data['status'] === 'theft') border-danger 
                                        @else border-secondary @endif">
                                        <div class="card-body">
                                            <h5 class="card-title">NIK: {{ $data['nik'] }}</h5>
                                            <p class="card-text">
                                                <strong>Vehicle:</strong> {{ $data['vehicle_id'] }} <br>
                                                <strong>Status:</strong> 
                                                <span class="badge 
                                                    @if($data['status'] === 'normal') bg-success 
                                                    @elseif($data['status'] === 'refuel') bg-warning text-dark
                                                    @elseif($data['status'] === 'theft') bg-danger 
                                                    @else bg-secondary @endif">
                                                    {{ ucfirst($data['status']) }}
                                                </span><br>
                                                <strong>Fuel:</strong> {{ $data['fuel_level'] }}% <br>
                                                <small class="text-muted">
                                                    Last Update: {{ $data['recorded_at'] }}
                                                </small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@extends('app')

@section('title', 'Analisis BBM - Admin')

@section('content')
<div class="container-fluid py-4" style="background-color:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h3 class="fw-bold mb-3 text-primary">
                Analisis BBM - ADMIN
            </h3>

            {{-- FILTER --}}
            <form class="row g-3 align-items-end mb-3" method="GET">

                {{-- COMPANY --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Perusahaan:</label>
                   <select name="company" class="form-select shadow-sm" required>
    <option value="">-- Pilih Perusahaan --</option>

    @foreach($companyList as $c)
        <option value="{{ $c }}"
            {{ request('company') === $c ? 'selected' : '' }}>
            {{ $c }}
        </option>
    @endforeach
</select>

                </div>

                {{-- START --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Dari:</label>
                    <input type="datetime-local"
                           name="start"
                           class="form-control shadow-sm"
                           value="{{ $start }}">
                </div>

                {{-- END --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Sampai:</label>
                    <input type="datetime-local"
                           name="end"
                           class="form-control shadow-sm"
                           value="{{ $end }}">
                </div>

                {{-- NIK --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold">NIK (opsional):</label>
                    <input type="text"
                           name="nik"
                           class="form-control shadow-sm"
                           value="{{ $nik }}">
                </div>

                <div class="col-md-12">
                    <button class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-search"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MESSAGE --}}
    @if(isset($message))
        <div class="alert alert-info shadow-sm">
            {{ $message }}
        </div>
    @else

        {{-- SUMMARY --}}
     <div class="row g-3 mb-4 text-center">

    {{-- TOTAL BBM --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <h6 class="text-muted mb-1">Total BBM Digunakan</h6>
                <h4 class="fw-bold">
                    {{ number_format($totalFuelUsed, 2) }} L
                </h4>
            </div>
        </div>
    </div>

    {{-- TOTAL BIAYA --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <h6 class="text-muted mb-1">Total Biaya BBM</h6>
                <h4 class="fw-bold text-success">
                    Rp {{ number_format($totalCost, 0, ',', '.') }}
                </h4>
            </div>
        </div>
    </div>

    {{-- EFISIENSI --}}
    <div class="col-md-4">
        @php
            if ($efficiency > 30) {
                $effColor = 'success';
                $effText  = '🟢 Aman';
            } elseif ($efficiency >= 10) {
                $effColor = 'warning';
                $effText  = '🟡 Warning';
            } else {
                $effColor = 'danger';
                $effText  = '🔴 Over Budget Risk';
            }
        @endphp

        <div class="card border-0 shadow-sm h-100 bg-{{ $effColor }} text-white">
            <div class="card-body d-flex flex-column justify-content-center">
                <h6 class="mb-1">Efisiensi</h6>

                <h2 class="fw-bold mb-1">
                    {{ number_format($efficiency, 2) }}%
                </h2>

                <small class="fw-semibold">
                    {{ $effText }}
                </small>
            </div>
        </div>
    </div>

</div>


        {{-- CHART --}}
        <div class="card border-0 shadow-sm bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-graph-up"></i> Tren Konsumsi BBM
                </h5>

                <select id="trendType" class="form-select w-auto shadow-sm">
                    <option value="hourly">Per Jam</option>
                    <option value="daily" selected>Per Hari</option>
                    <option value="monthly">Per Bulan</option>
                    <option value="all">Semua Data</option>
                </select>
            </div>

            <div style="height:450px;">
                <canvas id="bbmChart"></canvas>
            </div>
        </div>

    @endif
</div>

{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartInstance;
const ctx = document.getElementById('bbmChart');

function renderChart(type) {
    if (chartInstance) chartInstance.destroy();

    let labels = [];
    let data   = [];

    switch (type) {
        case 'hourly':
            labels = {!! json_encode(array_keys($fuelUsedPerHour ?? [])) !!};
            data   = {!! json_encode(array_values($fuelUsedPerHour ?? [])) !!};
            break;

        case 'monthly':
            labels = {!! json_encode(array_keys($fuelUsedPerMonth ?? [])) !!};
            data   = {!! json_encode(array_values($fuelUsedPerMonth ?? [])) !!};
            break;

        case 'all':
            labels = {!! json_encode(array_keys($fuelUsedAll ?? [])) !!};
            data   = {!! json_encode(array_values($fuelUsedAll ?? [])) !!};
            break;

        default:
            labels = {!! json_encode(array_keys($fuelUsedPerDay ?? [])) !!};
            data   = {!! json_encode(array_values($fuelUsedPerDay ?? [])) !!};
    }

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Pemakaian BBM (Liter)',
                data,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.12)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Liter' }},
                x: { title: { display: true, text: 'Waktu' }}
            }
        }
    });
}

renderChart('daily');

document.getElementById('trendType')
    .addEventListener('change', e => renderChart(e.target.value));
</script>
@endsection

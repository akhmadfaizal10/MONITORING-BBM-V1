@extends('app')

@section('content')
<div class="container-fluid py-4" style="background-color:#f8f9fa; min-height:100vh;">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h3 class="fw-bold mb-3 text-primary">Analisis BBM - {{ strtoupper($company ?? '-') }}</h3>

            <form class="row g-3 align-items-end mb-3" method="GET">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Dari:</label>
                    <input type="datetime-local" name="start" class="form-control shadow-sm" value="{{ $start }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Sampai:</label>
                    <input type="datetime-local" name="end" class="form-control shadow-sm" value="{{ $end }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">NIK (opsional):</label>
                    <input type="text" name="nik" class="form-control shadow-sm" value="{{ $nik }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100 shadow-sm"><i class="bi bi-search"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    @if(isset($message))
        <div class="alert alert-info shadow-sm">{{ $message }}</div>
    @else
        <div class="row g-3 mb-4 text-center">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <h6 class="text-muted mb-1">Total BBM Digunakan</h6>
                    <h4 class="fw-bold">{{ number_format($totalFuelUsed, 2) }} L</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <h6 class="text-muted mb-1">Total Biaya BBM</h6>
                    <h4 class="fw-bold text-success">Rp {{ number_format($totalCost, 0, ',', '.') }}</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white p-3">
                    <h6 class="text-muted mb-1">Efisiensi</h6>
                    <h4 class="fw-bold text-info">{{ $efficiency }}%</h4>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up"></i> Tren BBM</h5>
                <select id="trendType" class="form-select w-auto shadow-sm">
                    <option value="hourly">Per Jam</option>
                    <option value="daily" selected>Per Hari</option>
                    <option value="monthly">Per Bulan</option>
                    <option value="all">Semua Data</option>
                </select>
            </div>
            <div style="height: 450px;">
                <canvas id="bbmChart"></canvas>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartInstance;
const ctx = document.getElementById('bbmChart');

function renderChart(type) {
    if (chartInstance) chartInstance.destroy();

    let labels = [];
    let data = [];

    switch (type) {
        case 'hourly':
            labels = {!! json_encode(array_keys($fuelUsedPerHour ?? [])) !!};
            data = {!! json_encode(array_values($fuelUsedPerHour ?? [])) !!};
            break;
        case 'monthly':
            labels = {!! json_encode(array_keys($fuelUsedPerMonth ?? [])) !!};
            data = {!! json_encode(array_values($fuelUsedPerMonth ?? [])) !!};
            break;
        case 'all':
            labels = {!! json_encode(array_keys($fuelUsedAll ?? [])) !!};
            data = {!! json_encode(array_values($fuelUsedAll ?? [])) !!};
            break;
        default: // daily
            labels = {!! json_encode(array_keys($fuelUsedPerDay ?? [])) !!};
            data = {!! json_encode(array_values($fuelUsedPerDay ?? [])) !!};
    }

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pemakaian BBM (Liter)',
                data: data,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Liter' }
                },
                x: {
                    title: { display: true, text: 'Waktu' },
                    ticks: { maxTicksLimit: 10, autoSkip: true }
                }
            }
        }
    });
}

// Initial chart
renderChart('daily');

// Dropdown event
document.getElementById('trendType').addEventListener('change', (e) => {
    renderChart(e.target.value);
});
</script>
@endsection

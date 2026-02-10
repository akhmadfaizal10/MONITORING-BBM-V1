@extends('app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<style>
.icon { width: 18px; height: 18px; stroke-width: 2; }
.card { border-radius: 1rem; transition: all 0.3s ease; }
.card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
.card-header { background: linear-gradient(135deg, #1e293b, #334155); color: #fff; border: none; }
.card-company { cursor: pointer; }
#loader {
  display: none;
  text-align: center;
  padding: 20px;
  color: #666;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 pt-4">

    {{-- ======= KARTU STATISTIK UTAMA ======= --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
                        <div class="card card-statistic shadow-sm border-0 h-100" id="companyFilterCard" style="cursor:pointer">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 50px; height: 50px;">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">

                        <select id="companySelect" class="form-select mt-2 ">
    <option value="all">Semua Perusahaan</option>
    @foreach($statusDistribution as $c)
        <option value="{{ $c['company'] }}">{{ $c['company'] }}</option>
    @endforeach
</select>
                        

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card card-statistic shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 50px; height: 50px;">
                            <i class="bi bi-truck fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-1">Total Kendaraan</h5>
                        <p class="h2 mb-0 fw-bold">{{ $totalVehicles ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card card-statistic shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 50px; height: 50px;">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-1">Total Outlier</h5>
                        <p class="h2 mb-0 fw-bold">
                            {{ isset($outlierData) ? count(json_decode($outlierData)) : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card card-statistic shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 50px; height: 50px;">
                            <i class="bi bi-fuel-pump-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-1">Status Refuel</h5>
                        <p class="h2 mb-0 fw-bold">
    {{
        collect($statusDistribution)
            ->sum(fn($c) => $c['data'][1] ?? 0)
    }}
</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ======= GRAFIK ======= --}}
    <div class="row g-4 mb-4">
        {{-- PIE CHART CAROUSEL --}}
        <div class="col-xl-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Distribusi Status Kendaraan</h5>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div class="w-100 text-center">
                        <h6 class="fw-bold mb-3" id="companyTitle"></h6>
                        <canvas id="statusDistributionChart" style="max-height: 350px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- LINE CHART --}}
        <div class="col-xl-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Konsumsi BBM per Perusahaan (7 Hari Terakhir)</h5>
                </div>
                <div class="card-body">
                    <canvas id="fuelConsumptionChart" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {

    const pieData  = @json($statusDistribution);
    const lineData = @json($lineCharts);

    /* ================= PIE CHART CAROUSEL ================= */
    let pieIndex = 0;
    let pieChart = null;

    const pieCtx = document.getElementById('statusDistributionChart').getContext('2d');
    const companyTitle = document.getElementById('companyTitle');

    const pieDots = document.createElement('div');
    pieDots.className = 'd-flex justify-content-center gap-2 mt-3';
    document.getElementById('statusDistributionChart').parentElement.appendChild(pieDots);

    function renderPieDots() {
        pieDots.innerHTML = '';
        pieData.forEach((_, i) => {
            const dot = document.createElement('span');
            dot.style.cssText = `
                width:10px;height:10px;border-radius:50%;
                background:${i===pieIndex?'#0d6efd':'#ced4da'};
                cursor:pointer;
            `;
            dot.onclick = () => { pieIndex = i; renderPie(); };
            pieDots.appendChild(dot);
        });
    }

    function renderPie() {
        const d = pieData[pieIndex];
        companyTitle.textContent = d.company;

        if (pieChart) pieChart.destroy();

        pieChart = new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: d.labels,
                datasets: [{
                    data: d.data,
                    backgroundColor: ['#198754','#ffc107','#dc3545','#b02a37'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                cutout: '65%',
                plugins: { legend: { position: 'bottom' } },
                animation: { duration: 700 }
            }
        });

        renderPieDots();
    }

    if (pieData.length) {
        renderPie();
        setInterval(() => {
            pieIndex = (pieIndex + 1) % pieData.length;
            renderPie();
        }, 3000);
    }

    /* ================= LINE CHART CAROUSEL ================= */
    let lineIndex = 0;
    let lineChart = null;

    const lineCanvas = document.getElementById('fuelConsumptionChart');
    const lineCtx = lineCanvas.getContext('2d');

    /* ⭐ TAMBAHAN: DOTS LINE CHART */
    const lineDots = document.createElement('div');
    lineDots.className = 'd-flex justify-content-center gap-2 mt-3';
    lineCanvas.parentElement.appendChild(lineDots);

    function renderLineDots() {
        lineDots.innerHTML = '';
        lineData.forEach((_, i) => {
            const dot = document.createElement('span');
            dot.style.cssText = `
                width:10px;height:10px;border-radius:50%;
                background:${i===lineIndex?'#0d6efd':'#ced4da'};
                cursor:pointer;
            `;
            dot.onclick = () => {
                lineIndex = i;
                renderLine();
            };
            lineDots.appendChild(dot);
        });
    }
    /* ⭐ END TAMBAHAN */

    function renderLine() {
        const d = lineData[lineIndex];
        if (!d) return;

        if (lineChart) lineChart.destroy();

        lineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: d.labels,
                datasets: d.datasets
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom' },
                    title: {
                        display: true,
                        text: d.company
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        /* ⭐ TAMBAHAN */
        renderLineDots();
    }

    if (lineData.length) {
        renderLine();
        setInterval(() => {
            lineIndex = (lineIndex + 1) % lineData.length;
            renderLine();
        }, 4000);
    }
 const companyFilterCard = document.getElementById('companyFilterCard');
    const companySelect = document.getElementById('companySelect');

    let filteredPieData = [...pieData];
    let filteredLineData = [...lineData];

    // toggle dropdown
    companyFilterCard?.addEventListener('click', () => {
        
    });

    // ketika company dipilih
    companySelect?.addEventListener('change', () => {
        const selected = companySelect.value;

        if (selected === 'all') {
            filteredPieData = [...pieData];
            filteredLineData = [...lineData];
        } else {
            filteredPieData = pieData.filter(p => p.company === selected);
            filteredLineData = lineData.filter(l => l.company === selected);
        }

        // reset index
        pieIndex = 0;
        lineIndex = 0;

        // render ulang
        if (filteredPieData.length) {
            pieData.length = 0;
            filteredPieData.forEach(d => pieData.push(d));
            renderPie();
        }

        if (filteredLineData.length) {
            lineData.length = 0;
            filteredLineData.forEach(d => lineData.push(d));
            renderLine();
        }
    });
});
</script>





@endpush
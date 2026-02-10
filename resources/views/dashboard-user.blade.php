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
            <div class="card card-statistic shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-1">Total Perusahaan</h5>
                        <p class="h2 mb-0 fw-bold">{{ $totalCompanies ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-statistic shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
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
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-1">Total Outlier</h5>
<p class="h2 mb-0 fw-bold">{{ $totalOutliers ?? 'N/A' }}</p>                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-statistic shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-fuel-pump-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-1">Status Refuel</h5>
                        <p class="h2 mb-0 fw-bold">{{ isset($statusDistribution) ? (json_decode($statusDistribution)->data[1] ?? 0) : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ======= GRAFIK ======= --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Distribusi Status Kendaraan</h5>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="statusDistributionChart" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
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

    {{-- ======= CARD PERUSAHAAN (ADMIN) ======= --}}
    @auth
        @if(auth()->user()->role === 'admin')
            <h5 class="fw-bold mb-3">Status Kendaraan per Perusahaan</h5>
            <div class="row">
                @foreach ($statusSummary as $company)
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow-sm border-0 h-100 card-company" data-company="{{ $company['company'] }}">
                        <div class="card-header d-flex align-items-center">
                            <span class="me-2">
                                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <h6 class="mb-0 fw-semibold">{{ $company['company'] }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2 p-2 bg-success bg-opacity-10 rounded">
                                <div class="d-flex align-items-center text-success">
                                    <span class="me-2">
                                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <strong>Normal</strong>
                                </div>
                                <span class="fw-bold text-success">{{ $company['normal'] }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2 p-2 bg-primary bg-opacity-10 rounded">
                                <div class="d-flex align-items-center text-primary">
                                    <span class="me-2">
                                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 22a7 7 0 0 0 7-7c0-3.87-7-13-7-13s-7 9.13-7 13a7 7 0 0 0 7 7z" />
                                        </svg>
                                    </span>
                                    <strong>Refuel</strong>
                                </div>
                                <span class="fw-bold text-primary">{{ $company['refuel'] }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2 p-2 bg-danger bg-opacity-10 rounded">
                                <div class="d-flex align-items-center text-danger">
                                    <span class="me-2">
                                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </span>
                                    <strong>Theft</strong>
                                </div>
                                <span class="fw-bold text-danger">{{ $company['theft'] }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-2 bg-warning bg-opacity-10 rounded">
                                <div class="d-flex align-items-center text-warning">
                                    <span class="me-2">
                                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </span>
                                    <strong>Plugged Theft</strong>
                                </div>
                                <span class="fw-bold text-warning">{{ $company['plugged_theft'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    @endauth

    {{-- ======= CARD PERUSAHAAN (USER) ======= --}}
  

    {{-- ======= MODAL STATUS (ADMIN ONLY) ======= --}}
    @auth
        @if(auth()->user()->role === 'admin')
        <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalLabel">Pilih Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center" id="statusOptions">
                        <button class="btn btn-outline-dark m-1" data-status="all">Semua Data</button>
                        <button class="btn btn-outline-success m-1" data-status="normal">Normal</button>
                        <button class="btn btn-outline-primary m-1" data-status="refuel">Refuel</button>
                        <button class="btn btn-outline-danger m-1" data-status="theft">Theft</button>
                        <button class="btn btn-outline-warning m-1" data-status="plugged_theft">Plugged Theft</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endauth

    {{-- ======= TABLE HASIL ======= --}}
  
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const statusData = {!! $statusDistribution ?? 'null' !!};
    const fuelData = {!! $fuelConsumptionData ?? 'null' !!};

    @auth
        const userRole = "{{ auth()->user()->role }}";
        const userCompany = "{{ auth()->user()->company ?? '' }}";
    @else
        const userRole = "";
        const userCompany = "";
    @endauth

    let selectedCompany = userRole === 'user' ? userCompany : null;
    let selectedStatus = 'all';
    let selectedDate = 'today';

    const resultSection = document.getElementById("resultSection");
    const tableBody = document.getElementById("tableBody");
    const paginationControls = document.getElementById("paginationControls");
    const loader = document.getElementById("loader");
    const selectedStatusEl = document.getElementById("selectedStatus");
    const selectedCompanyEl = document.getElementById("selectedCompany");

    // === CHART ===
    if (statusData) {
        new Chart(document.getElementById('statusDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: statusData.labels,
                datasets: [{
                    data: statusData.data,
                    backgroundColor: ['rgba(25,135,84,0.8)', 'rgba(255,193,7,0.8)', 'rgba(220,53,69,0.8)', 'rgba(179,28,28,0.8)'],
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    }

    if (fuelData) {
        new Chart(document.getElementById('fuelConsumptionChart'), {
            type: 'line',
            data: fuelData,
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    }

    // === FILTER TANGGAL ===
    const dateFilter = document.getElementById("dateFilter");
    const startDate = document.getElementById("startDate");
    const endDate = document.getElementById("endDate");
    const applyDate = document.getElementById("applyDate");

    dateFilter.addEventListener("change", () => {
        if (dateFilter.value === "custom") {
            startDate.style.display = "inline-block";
            endDate.style.display = "inline-block";
            applyDate.style.display = "inline-block";
        } else {
            startDate.style.display = "none";
            endDate.style.display = "none";
            applyDate.style.display = "none";
            selectedDate = dateFilter.value;
            loadPage(1);
        }
    });

    applyDate.addEventListener("click", () => {
        if (startDate.value && endDate.value) {
            selectedDate = `${startDate.value}_${endDate.value}`;
            loadPage(1);
        } else {
            alert("Pilih kedua tanggal terlebih dahulu!");
        }
    });

    // === LOAD DATA ===
    async function loadPage(page = 1) {
        if (!selectedCompany) return;

        loader.style.display = "block";
        tableBody.innerHTML = "";

        const url = `/dashboard/data?company=${encodeURIComponent(selectedCompany)}&status=${encodeURIComponent(selectedStatus)}&page=${page}&date=${encodeURIComponent(selectedDate)}`;
        const res = await fetch(url);
        const data = await res.json();

        loader.style.display = "none";
        renderTable(data);
    }

    function renderTable(data) {
        if (!data.data.length) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Tidak ada data</td></tr>`;
            paginationControls.innerHTML = '';
            return;
        }

        tableBody.innerHTML = data.data.map(row => `
            <tr>
                <td>${row.nik}</td>
                <td>${row.vehicle_id}</td>
                <td><span class="badge bg-${row.status === 'normal' ? 'success' : row.status === 'refuel' ? 'primary' : row.status === 'theft' ? 'danger' : 'warning'}">${row.status}</span></td>
                <td>${row.fuel_in ?? '-'}</td>
                <td>${row.fuel_out ?? '-'}</td>
                <td>${new Date(row.recorded_at).toLocaleString('id-ID')}</td>
            </tr>`).join('');

        const totalPages = Math.ceil(data.total / data.perPage);
        paginationControls.innerHTML = `
            <button class="btn btn-outline-secondary" ${data.currentPage <= 1 ? 'disabled' : ''} id="prevPage">Prev</button>
            <span>Halaman ${data.currentPage} dari ${totalPages}</span>
            <button class="btn btn-outline-secondary" ${data.currentPage >= totalPages ? 'disabled' : ''} id="nextPage">Next</button>
        `;

        document.getElementById("prevPage")?.addEventListener("click", () => loadPage(data.currentPage - 1));
        document.getElementById("nextPage")?.addEventListener("click", () => loadPage(data.currentPage + 1));
    }

    // === ADMIN: KLIK CARD ===
    @auth
        @if(auth()->user()->role === 'admin')
        document.querySelectorAll(".card-company").forEach(card => {
            card.addEventListener("click", () => {
                selectedCompany = card.dataset.company;
                document.getElementById("statusModalLabel").textContent = `Pilih Status - ${selectedCompany}`;
                new bootstrap.Modal(document.getElementById("statusModal")).show();
            });
        });

        document.querySelectorAll("#statusOptions button").forEach(btn => {
            btn.addEventListener("click", () => {
                selectedStatus = btn.dataset.status;
                selectedStatusEl.textContent = selectedStatus.toUpperCase();
                selectedCompanyEl.textContent = selectedCompany;
                resultSection.style.display = 'block';
                bootstrap.Modal.getInstance(document.getElementById("statusModal")).hide();
                loadPage(1);
            });
        });
        @endif

        // === USER: OTOMATIS LOAD ===
        @if(auth()->user()->role === 'user' && auth()->user()->company)
        selectedStatusEl.textContent = 'SEMUA';
        selectedCompanyEl.textContent = userCompany;
        resultSection.style.display = 'block';
        loadPage(1);
        @endif
    @endauth
});
</script>
@endpush
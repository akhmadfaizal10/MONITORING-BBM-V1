@extends('app')
@section('page_title', 'Data Analysis')
@section('title', 'Analisis Bahan Bakar')

@section('content')

<style>
    body { background-color: #f5f6fa; font-family: 'Poppins', sans-serif; color: #333; }
    .container { background: #fff; padding: 35px; border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.05); animation: fadeIn 0.5s ease-in-out; }
    h2 { font-weight: 700; color: #222; border-left: 6px solid #444; padding-left: 10px; margin-bottom: 25px; }
    h3 { font-weight: 600; font-size: 1.2rem; color: #222; border-bottom: 2px solid #ddd; padding-bottom: 6px; margin-bottom: 15px; }
    .grid { display: grid; gap: 20px; }
    @media (min-width: 768px) { .grid-cols-2 { grid-template-columns: repeat(2, 1fr); } }
    .bg-white { background: #fff; }
    .shadow-lg { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06); }
    .rounded-lg { border-radius: 14px; }
    .p-6 { padding: 25px; }
    ul { list-style: none; padding: 0; margin: 0; }
    ul li { padding: 10px 0; border-bottom: 1px solid #eee; color: #444; font-size: 0.95rem; }
    ul li:last-child { border-bottom: none; }
    canvas { margin-top: 15px; width: 100% !important; height: 300px; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .bg-white:hover { transform: translateY(-3px); transition: all 0.2s ease-in-out; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
</style>

<div class="container mx-auto mt-10 pt-10">
    <h2>Analisis Bahan Bakar Kendaraan</h2>

    <form method="GET" action="{{ url()->current() }}" class="mb-4 flex flex-wrap gap-3 items-center">
        @if(Auth::user()->role === 'admin')
            <div>
                <label for="company">Perusahaan:</label>
                <select name="company" id="company" class="border border-gray-300 rounded px-2 py-1">
                    @foreach($companies as $company)
                        <option value="{{ $company }}" {{ $selectedCompany == $company ? 'selected' : '' }}>
                            {{ ucfirst($company) }}
                        </option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="company" value="{{ $selectedCompany }}">
        @endif

        <div>
            <label for="start_date">Dari:</label>
            <input type="datetime-local" name="start_date" id="start_date" class="border border-gray-300 rounded" value="{{ request('start_date') }}">
        </div>

        <div>
            <label for="end_date">Sampai:</label>
            <input type="datetime-local" name="end_date" id="end_date" class="border border-gray-300 rounded" value="{{ request('end_date') }}">
        </div>

        <button type="submit" class="bg-blue-500 text-white rounded px-4 py-2">Terapkan</button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h3>Grafik Tren Level BBM</h3>
            <canvas id="fuelTrendChart"></canvas>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6">
            <h3>Rata-rata Konsumsi per Jam</h3>
            <ul>
                @foreach ($fuelData['average_consumption'] as $nik => $average)
                    <li><strong>NIK:</strong> {{$nik}} — {{$average}} L/jam</li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6">
            <h3>Efisiensi Bahan Bakar</h3>
            <ul>
                @foreach ($fuelData['fuel_efficiency'] as $nik => $efficiency)
                    <li><strong>NIK:</strong> {{$nik}} — {{$efficiency}} L/km</li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6">
            <h3>Analisis Biaya</h3>
            <ul>
                @foreach ($fuelData['cost_analysis'] as $nik => $cost)
                    <li><strong>NIK:</strong> {{$nik}} — Rp{{number_format($cost, 0, ',', '.')}}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const trendData = @json($fuelData['trend_data']);
    const ctxTrend = document.getElementById('fuelTrendChart').getContext('2d');

    if (trendData.length > 0) {
        const labels = trendData.map(data => data.date);
        const niks = Object.keys(trendData[0]).filter(k => k !== 'date');

        const datasets = niks.map(nik => ({
            label: `NIK ${nik}`,
            data: trendData.map(d => d[nik] || 0),
            borderColor: '#' + Math.floor(Math.random() * 16777215).toString(16),
            backgroundColor: 'rgba(0,0,0,0)',
            borderWidth: 2,
            tension: 0.3,
            pointRadius: 3,
            pointBackgroundColor: '#666'
        }));

        new Chart(ctxTrend, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Level BBM (Liter)' } },
                    x: { title: { display: true, text: 'Tanggal' } }
                },
                plugins: { legend: { labels: { font: { weight: 'bold' } } } }
            }
        });
    }
</script>

@endsection

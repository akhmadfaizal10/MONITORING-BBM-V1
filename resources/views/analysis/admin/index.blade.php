@extends('app')

@section('title', 'Analisis Bahan Bakar - Admin')

@section('content')
@php
    $fuelData = []; // Initialize the variable to avoid undefined error
@endphp

<style>
    /* Your existing styles */
</style>

<div class="container mx-auto mt-10 pt-10">
    <h2>Analisis Bahan Bakar Kendaraan - Admin</h2>

    <!-- Filters -->
    <div>
        <label for="companySelect">Pilih Perusahaan:</label>
        <select id="companySelect">
            <!-- Populate this with companies dynamically, if needed -->
        </select>
        
        <label for="statusSelect">Pilih Status:</label>
        <select id="statusSelect">
            <option value="all">Semua</option>
            <option value="normal">Normal</option>
            <option value="refuel">Pengisian</option>
            <option value="theft">Pencurian</option>
            <option value="plugged_theft">Pencurian Terhubung</option>
        </select>

        <label for="dateSelect">Pilih Tanggal:</label>
        <select id="dateSelect">
            <option value="today">Hari Ini</option>
            <option value="7days">7 Hari Terakhir</option>
            <option value="custom">Kustom</option>
            <!-- Add more date options here if needed -->
        </select>

        <button id="applyFilters">Terapkan</button>
    </div>

    <div id="loader" style="display:none;">Loading...</div>

    <div id="tableContainer">
        <!-- Render the table data dynamically here -->
        <table id="dataTable">
            <thead>
                <tr>
                    <th>ID Kendaraan</th>
                    <th>Status</th>
                    <th>Fuel In</th>
                    <th>Fuel Out</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Data rows will be inserted here -->
            </tbody>
        </table>
    </div>

    <!-- Chart for fuel consumption -->
    <div class="bg-white shadow-lg rounded-lg p-6 mt-5">
        <h3>Konsumsi Bahan Bakar (7 Hari Terakhir)</h3>
        <canvas id="fuelConsumptionChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.getElementById('applyFilters').addEventListener('click', async function() {
        const loader = document.getElementById('loader');
        const tableBody = document.getElementById('tableBody');
        loader.style.display = "block";
        tableBody.innerHTML = ""; // Clear existing data

        const selectedCompany = document.getElementById('companySelect').value;
        const selectedStatus = document.getElementById('statusSelect').value;
        const selectedDate = document.getElementById('dateSelect').value;

        const url = `/analysis/getData?company=${encodeURIComponent(selectedCompany)}&status=${encodeURIComponent(selectedStatus)}&date=${encodeURIComponent(selectedDate)}`;
        const res = await fetch(url);
        const data = await res.json();

        loader.style.display = "none";

        // Render the table data
        renderTable(data);
    });

    function renderTable(data) {
        const tableBody = document.getElementById('tableBody');
        data.data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.vehicle_id}</td>
                <td>${row.status}</td>
                <td>${row.fuel_in}</td>
                <td>${row.fuel_out}</td>
                <td>${new Date(row.recorded_at).toLocaleString()}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    // Initialize the chart here
    // You'll also want to ensure data is available for rendering the chart
</script>
@endsection
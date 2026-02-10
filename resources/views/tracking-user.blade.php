@extends('app')

@section('title', 'Vehicle Data Monitoring')
@section('page_title', 'Vehicle Data Monitoring')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tracking.css') }}">
@endpush


@section('content')
<div class="container">
   

    @if($vehicles && count($vehicles) > 0)
    <div class="table-card">
      
        <div class="table-wrapper">
            <table>
                 <thead id="table-head">
        <!-- Akan diisi oleh JS -->
    </thead>
                <tbody>
                    @foreach ($vehicles as $index => $v)
                        @php
                            $statusClass = match($v['status']) {
                                'normal' => 'normal',
                                'refuel' => 'refuel',
                                'theft' => 'theft',
                                'plugged_theft' => 'plugged-theft',
                                default => 'unknown'
                            };
                            $fuelLevel = intval($v['fuel_level']);
                            $fuelClass = $fuelLevel > 60 ? 'high' : ($fuelLevel > 30 ? 'medium' : 'low');
                        @endphp
                        <tr>
                            <td><span class="code-badge">{{ $v['nik'] }}</span></td>
                            <td><span class="code-badge">{{ $v['vehicle_id'] }}</span></td>
                           <td>
    <span class="status-badge {{ $statusClass }}">
        @switch($v['status'])
            @case('normal')
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                @break

            @case('refuel')
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 22a7 7 0 0 0 7-7c0-3.87-7-13-7-13s-7 9.13-7 13a7 7 0 0 0 7 7z"/>
                </svg>
                @break

            @case('theft')
            @case('plugged_theft')
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4
                             c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                @break

            @default
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                </svg>
        @endswitch
        {{ ucfirst(str_replace('_', ' ', $v['status'])) }}
    </span>
</td>

                            <td>
                                <div class="fuel-level" styles="margin-left: 10px; padding-left: 100px;">
                                    <span class="fuel-text ">{{ $fuelLevel }}L</span>
                                </div>
                            </td>
                            <td>
                                <div class="last-update">
                                    <div class="update-indicator"></div>
                                    {{ \Carbon\Carbon::parse($v['recorded_at'])->format('d/m/Y H:i') }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @php
        $stats = [
            'total' => count($vehicles),
            'normal' => count(array_filter($vehicles, fn($x) => $x['status'] === 'normal')),
            'refuel' => count(array_filter($vehicles, fn($x) => $x['status'] === 'refuel')),
            'alerts' => count(array_filter($vehicles, fn($x) => in_array($x['status'], ['theft', 'plugged_theft']))),
        ];
    @endphp

    <div class="footer-stats">
        <div class="footer-stat-card total">
            <div class="footer-stat-header">
                <div class="footer-stat-icon">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h2m-6 0h4"/></svg>
                </div>
                <div class="footer-stat-label">Total Vehicles</div>
            </div>
            <div class="footer-stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="footer-stat-card normal">
            <div class="footer-stat-header">
                <div class="footer-stat-icon">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="footer-stat-label">Normal</div>
            </div>
            <div class="footer-stat-value">{{ $stats['normal'] }}</div>
        </div>
        <div class="footer-stat-card refuel">
            <div class="footer-stat-header">
                <div class="footer-stat-icon">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22a7 7 0 0 0 7-7c0-3.87-7-13-7-13s-7 9.13-7 13a7 7 0 0 0 7 7z" /></svg>
                </div>
                <div class="footer-stat-label"> Refuel</div>
            </div>
            <div class="footer-stat-value">{{ $stats['refuel'] }}</div>
        </div>
        <div class="footer-stat-card alerts">
            <div class="footer-stat-header">
                <div class="footer-stat-icon">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="footer-stat-label">theft</div>
            </div>
            <div class="footer-stat-value">{{ $stats['alerts'] }}</div>
        </div>
    </div>
    @else
        <div class="alert alert-warning mt-4" role="alert">
            Tidak ada data kendaraan yang ditemukan.
        </div>
    @endif
</div>
@push('scripts')
<script>
    const icons = {
        creditCard: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
        car: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h2m-6 0h4"/></svg>',
        activity: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
        fuel: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v5a3 3 0 003 3h1a3 3 0 003-3v-5a3 3 0 00-3-3h-1a3 3 0 00-3 3zm0 0V9a3 3 0 013-3h1a3 3 0 013 3v2m-6 0h6m-3 5v5m-3-13h.01" /></svg>',
        clock: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    };

    // Isi thead dengan innerHTML
    const tableHead = document.getElementById('table-head');
    tableHead.innerHTML = `
        <tr>
            <th><div>${icons.creditCard} NIK</div></th>
            <th><div>${icons.car} Vehicle ID</div></th>
            <th><div>${icons.activity} Status</div></th>
            <th><div>${icons.fuel} Fuel Level</div></th>
            <th><div>${icons.clock} Last Update</div></th>
        </tr>
    `;
</script>
@endpush

@endsection
<!-- Sidebar -->
<div class="bg-dark border-end" id="sidebar-wrapper">
    <div class="sidebar-heading text-white d-flex align-items-center justify-content-center p-3">
        <i class="bi bi-truck-front-fill me-2 fs-4"></i>
        <span class="fs-5 fw-bold">BBM MONITORING</span>
    </div>
    <div class="list-group list-group-flush">

        {{-- DASHBOARD --}}
        @if(auth()->user()->role === 'admin')
            <a href="{{ url('/dashboard') }}" 
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('dashboard*') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard Admin
            </a>
        @else
            <a href="{{ url('/dashboard-user') }}" 
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('dashboard-user*') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        @endif

        {{-- TRACKING --}}
        @if(auth()->user()->role === 'admin')
            <a href="{{ url('/tracking') }}" 
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('tracking*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill me-2"></i> Tracking Kendaraan
            </a>
        @else
            <a href="{{ url('/tracking-user') }}" 
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('tracking-user*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill me-2"></i> Tracking Kendaraan
            </a>
        @endif

        {{-- DATA --}}
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('DATA') }}" 
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('DATA*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line-fill me-2"></i> Data Admin
            </a>
        @else
            <a href="{{ url('/analysis') }}" 
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('analysis*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line-fill me-2"></i> Data Analysis
            </a>
        @endif

    </div>
</div>
<!-- /#sidebar-wrapper -->
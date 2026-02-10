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
        
        @endif

        {{-- ANALYSIS --}}
        @if(auth()->user()->role === 'admin')
           <a href="{{ url('/admin/analysis') }}"
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('admin/analysis*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line-fill me-2"></i> Data Analysis
            </a>
        @else
            <a href="{{ url('/analysis') }}"
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('analysis*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line-fill me-2"></i> Data Analysis
            </a>
        @endif

        {{-- KALIBRASI --}}
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('calibration.index') }}"
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('calibration*') ? 'active' : '' }}">
                <i class="bi bi-sliders me-2"></i> Faktor per Liter
            </a>
        @endif


        {{-- ============================= --}}
        {{-- PENGATURAN BBM (ADMIN ONLY) --}}
        {{-- ============================= --}}

        @if(auth()->user()->role === 'admin')

            <a href="{{ route('admin.budget.index') }}"
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('admin/budget*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack me-2"></i> Budget Perusahaan
            </a>

            <a href="{{ route('admin.nikfuel.index') }}"
               class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('admin/nikfuel*') ? 'active' : '' }}">
                <i class="bi bi-fuel-pump-fill me-2"></i> Harga Fuel per NIK
            </a>

        @endif

     @if(auth()->user()->role === 'user')

    
    {{-- Budget Perusahaan --}}
    <a href="{{ route('user.budget.index') }}"
       class="list-group-item list-group-item-action list-group-item-dark p-3
       {{ Request::is('user/budget*') ? 'active' : '' }}">
        <i class="bi bi-cash-stack me-2"></i> Budget Perusahaan
    </a>

    {{-- Harga Fuel per NIK --}}
    <a href="{{ route('user.nikfuel.index') }}"
       class="list-group-item list-group-item-action list-group-item-dark p-3
       {{ Request::is('user/nikfuel*') ? 'active' : '' }}">
        <i class="bi bi-fuel-pump-fill me-2"></i> Harga Fuel per NIK
    </a>

@endif


    </div>
</div>
<!-- /#sidebar-wrapper -->

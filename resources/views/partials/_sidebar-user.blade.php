<!-- Sidebar -->
<div class="bg-dark border-end" id="sidebar-wrapper">
    <div class="sidebar-heading text-white d-flex align-items-center justify-content-center p-3">
        <i class="bi bi-truck-front-fill me-2 fs-4"></i>
        <span class="fs-5 fw-bold">BBM MONITORING</span>
    </div>
    <div class="list-group list-group-flush">
        <a href=" {{ url('/dashboard-user')}} " class="list-group-item list-group-item-action list-group-item-dark p-3  {{ Request::is('dashboard*') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a href="{{ url('/tracking-user') }}" class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('tracking*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill me-2"></i> Tracking Kendaraan
        </a>
     <a href="{{ route('DATA-user') }}" class="list-group-item list-group-item-action list-group-item-dark p-3 {{ Request::is('DATA*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line-fill me-2"></i> Data
        </a>
       
    </div>
</div>
<!-- /#sidebar-wrapper -->

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <!-- Tombol Toggle Sidebar -->
        <button class="btn btn-primary" id="sidebarToggle"><i class="bi bi-list"></i></button>

        <!-- Judul Halaman Dinamis -->
        <span class="navbar-text ms-3 fw-bold fs-5 text-dark">
            @yield('page_title', 'Halaman Utama')
        </span>

        <!-- Tombol Toggler untuk mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu di Sebelah Kanan -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item {{ Request::is('profile*') ? 'active' : '' }}" href="{{ url('/profile') }}">Pengaturan</a>
                        <a class="dropdown-item" href="{{ route('logout') }}" 
                           onclick="event.preventDefault();
                                   document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
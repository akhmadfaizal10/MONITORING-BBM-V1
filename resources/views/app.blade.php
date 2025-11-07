<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'FleetTrack')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom Layout CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Stack untuk CSS spesifik per halaman -->
    @stack('styles')
</head>
<body class="bg-light">

    <div class="d-flex" id="wrapper">

        <!-- Sidebar -->
        @include('partials._sidebar')

        <!-- Konten Utama Wrapper -->
        <div id="page-content-wrapper">
            
            <!-- Navigasi Atas -->
            @include('partials._topbar')

            <!-- Konten Halaman -->
            <main>
                @yield('content')
            </main>

        </div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Script Kustom untuk Toggle Sidebar -->
  <script>
        window.addEventListener('DOMContentLoaded', event => {
            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            const wrapperElement = document.getElementById('wrapper'); // Dapatkan elemen wrapper

            if (sidebarToggle && wrapperElement) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    
                    // Toggle kelas 'toggled' pada elemen #wrapper
                    // Ini akan cocok dengan selector CSS '#wrapper.toggled'
                    wrapperElement.classList.toggle('toggled');
                });
            }
        });
    </script>
    
    <!-- Stack untuk script spesifik per halaman -->
    @stack('scripts')
</body>
</html>


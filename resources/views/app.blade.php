<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', 'Monitoring Dashboard')</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Global CSS --}}
<link rel="stylesheet" href="{{ asset('style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

  @stack('styles')
</head>
<body>
  <div class="container">
    {{-- Header global --}}
    <div class="header mb-4 ">
      <h1>Monitoring BBM DATA</h1>
    </div>

    {{-- Konten halaman --}}
    <div class="content">
      @yield('content')
    </div>
  </div>

  {{-- Global script --}}
  <script>
    // Bisa taruh JS global di sini, misalnya csrf setup axios/fetch
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
  </script>

  {{-- Script tambahan per-halaman --}}
  @stack('scripts')
</body>
</html>

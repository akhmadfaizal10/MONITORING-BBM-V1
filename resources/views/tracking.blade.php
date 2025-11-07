@extends('app')

@section('title', 'Vehicle Data Monitoring')
@section('page_title', 'Vehicle Data Monitoring')
{{-- Pastikan Anda memiliki section 'styles' di layout utama Anda (app.blade.php) --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tracking.css') }}">
@endpush

@section('content')
<div class="container">
    {{-- Bagian Header Utama --}}
    <div class="header" id="header">
<div class="header-subtitle d-flex align-items-center gap-3 mt-4">
    <span id="backButton" class="hidden"></span>
    <div class="status-indicator hidden"></div>
    <p id="headerSubtitle" class="mb-0 flex-grow-1">Real-time fleet tracking • Select a company to begin</p>
</div>

    </div>

    {{-- Container untuk Tampilan Kartu Perusahaan (Akan diisi oleh JavaScript) --}}
    <div id="companiesView" class="companies-grid"></div>

    {{-- Container untuk Tampilan Tabel Kendaraan (Akan diisi oleh JavaScript) --}}
    <div id="tableView" class="hidden"></div>
</div>
@endsection

@push('scripts')
{{-- Mengirim data dari Controller ke JavaScript dengan aman --}}
<script>
    const companyData = @json($vehicles);
</script>

{{-- Memanggil file JavaScript utama untuk logika tampilan --}}
<script src="{{ asset('js/script.js') }}"></script>
@endpush
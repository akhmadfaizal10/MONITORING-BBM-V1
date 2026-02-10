@extends('app')

@section('title', 'Tambah Kalibrasi')
@section('page_title', 'Tambah Kalibrasi Faktor per Liter')

@include('partials.style')

@section('content')
<div class="container-fluid px-4 pt-4">

    <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px;">
        <div class="card-header">
            <h5 class="mb-0">Tambah Kalibrasi Kendaraan</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('calibration.store') }}" method="POST">
                @csrf

                {{-- NIK --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">NIK</label>
                    <input type="text"
                           name="nik"
                           class="form-control"
                           placeholder="Masukkan NIK kendaraan"
                           required>
                </div>

                {{-- SENSOR KOSONG --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Sensor Kosong</label>
                    <input type="number"
                           step="0.01"
                           name="sensor_kosong"
                           class="form-control"
                           placeholder="Contoh: 288"
                           required>
                </div>

                {{-- FAKTOR PER LITER --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Faktor per Liter</label>
                    <input type="number"
                           step="0.0001"
                           name="faktor_per_liter"
                           class="form-control"
                           placeholder="Contoh: 0.9"
                           required>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('calibration.index') }}"
                       class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit"
                            class="btn btn-primary">
                        Simpan Kalibrasi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

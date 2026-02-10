@extends('app')

@section('title', 'Faktor per Liter')
@section('page_title', 'Kalibrasi Faktor per Liter')

@include('partials.style')

@section('content')
<div class="container-fluid px-4 pt-4">

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Kalibrasi Kendaraan</h5>
            <a href="{{ route('calibration.create') }}" class="btn btn-light btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah Kalibrasi
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Sensor Kosong</th>
                            <th>Faktor / Liter</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            <td>{{ $row->nik }}</td>
                            <td>{{ $row->sensor_kosong }}</td>
                            <td>{{ $row->faktor_per_liter }}</td>
                            <td>
                                <a href="{{ route('calibration.edit', $row->id) }}"
                                   class="btn btn-sm btn-primary">
                                    Edit
                                </a>
                                <form action="{{ route('calibration.destroy', $row->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Hapus kalibrasi ini?')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Belum ada data kalibrasi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

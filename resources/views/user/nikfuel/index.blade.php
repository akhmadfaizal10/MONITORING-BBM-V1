@extends('app')

@section('title', 'Harga Fuel per NIK')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Harga Fuel per NIK</h3>
            <small class="text-muted">Perusahaan: {{ $company }}</small>
        </div>

        <a href="{{ route('user.nikfuel.create') }}" class="btn btn-primary">
            + Tambah Harga
        </a>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NIK</th>
                        <th>Harga / Liter</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prices as $i => $price)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $price->nik }}</td>
                            <td>Rp {{ number_format($price->price_per_liter, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('user.nikfuel.edit', $price->id) }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <form action="{{ route('user.nikfuel.destroy', $price->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Belum ada data harga fuel
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

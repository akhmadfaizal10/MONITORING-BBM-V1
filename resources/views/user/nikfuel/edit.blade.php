@extends('app')

@section('title', 'Edit Harga Fuel')

@section('content')
<div class="container py-4">

    <h3 class="fw-bold mb-3">Edit Harga Fuel</h3>

    {{-- Error Validation --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('user.nikfuel.update', $price->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Company (readonly) --}}
                <div class="mb-3">
                    <label class="form-label">Perusahaan</label>
                    <input type="text"
                           class="form-control"
                           value="{{ auth()->user()->company }}"
                           readonly>
                </div>

                {{-- NIK --}}
                <div class="mb-3">
                    <label class="form-label">NIK</label>
                    <input type="text"
                           name="nik"
                           class="form-control"
                           value="{{ $price->nik }}"
                           required>
                </div>

                {{-- Harga --}}
                <div class="mb-3">
                    <label class="form-label">Harga per Liter</label>
                    <input type="number"
                           name="price_per_liter"
                           class="form-control"
                           value="{{ $price->price_per_liter }}"
                           required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('user.nikfuel.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                    <button class="btn btn-primary">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

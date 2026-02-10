@extends('app')

@section('content')
<div class="container">
    <h4>Edit Harga Fuel per NIK</h4>

    <form action="{{ route('admin.nikfuel.update', $nikfuel->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">NIK</label>
            <input type="text"
                   class="form-control"
                   value="{{ $nikfuel->nik }}"
                   disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">Harga Fuel (Rp/Liter)</label>
            <input type="number"
                   name="fuel_price"
                   class="form-control"
                   value="{{ $nikfuel->fuel_price }}"
                   required>
        </div>

        <button type="submit" class="btn btn-primary">
            Update
        </button>

        <a href="{{ route('admin.nikfuel.index') }}"
           class="btn btn-secondary">
            Kembali
        </a>
    </form>
</div>
@endsection

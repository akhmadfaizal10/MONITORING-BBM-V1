@extends('app')

@section('content')
<div class="container">
    <h3>Tambah Budget</h3>

    <form method="POST" action="{{ route('admin.budget.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Company</label>
            <input type="text"
                   name="company"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Bulan</label>
            <input type="month"
                   name="month"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tahun</label>
            <input type="number"
                   name="year"
                   class="form-control"
                   min="2000"
                   max="2100"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Budget BBM (Rp)</label>
            <input type="number"
                   name="budget_amount"
                   class="form-control"
                   required>
        </div>

        <button class="btn btn-success">Simpan</button>
        <a href="{{ route('admin.budget.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </form>
</div>
@endsection

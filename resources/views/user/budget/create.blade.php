@extends('app')

@section('content')
<div class="container">
    <h4>Tambah Budget BBM</h4>

    <form action="{{ route('user.budget.store') }}" method="POST">
    @csrf

    {{-- Company (display only) --}}
    <div class="mb-3">
        <label class="form-label">Perusahaan</label>
        <input type="text"
               class="form-control"
               value="{{ auth()->user()->company }}"
               readonly>
    </div>

    <div class="mb-3">
        <label class="form-label">Bulan</label>
        <input type="month" name="month" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Tahun</label>
        <input type="number" name="year" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Budget</label>
        <input type="number" name="budget_amount" class="form-control" required>
    </div>

    <button class="btn btn-primary">Simpan</button>
</form>

</div>
@endsection

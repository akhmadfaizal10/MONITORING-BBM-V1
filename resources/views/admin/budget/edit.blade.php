@extends('app')

@section('content')
<div class="container">
    <h4>Edit Budget Perusahaan</h4>

    <form action="{{ route('admin.budget.update', $budget->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Bulan</label>
            <input type="month" name="month"
                   class="form-control"
                   value="{{ $budget->month }}"
                   required>
        </div>
       <div class="mb-3">
                    <label class="form-label">Tahun</label>
                    <input type="number"
                           name="year"
                           class="form-control"
                           value="{{ $budget->year }}"
                           required>
                </div>
        <div class="mb-3">
            <label class="form-label">Budget BBM (Rp)</label>
            <input type="number" name="budget_amount"
                   class="form-control"
                   value="{{ $budget->budget_amount }}"
                   required>
        </div>

        <button type="submit" class="btn btn-primary">
            Update
        </button>
        <a href="{{ route('admin.budget.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </form>
</div>
@endsection

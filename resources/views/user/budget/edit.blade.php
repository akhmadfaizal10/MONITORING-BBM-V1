@extends('app')

@section('title', 'Edit Budget BBM')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="mb-4">
        <h3 class="fw-bold mb-0">Edit Budget BBM</h3>
        <small class="text-muted">
            Perusahaan: {{ auth()->user()->company }}
        </small>
    </div>

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

    {{-- Form --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('user.budget.update', $budget->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Company (display only) --}}
                <div class="mb-3">
                    <label class="form-label">Perusahaan</label>
                    <input type="text"
                           class="form-control"
                           value="{{ auth()->user()->company }}"
                           readonly>
                </div>

                {{-- Month --}}
                <div class="mb-3">
                    <label class="form-label">Bulan</label>
                    <input type="month"
                           name="month"
                           class="form-control"
                           value="{{ $budget->month }}"
                           required>
                </div>

                {{-- Year --}}
                <div class="mb-3">
                    <label class="form-label">Tahun</label>
                    <input type="number"
                           name="year"
                           class="form-control"
                           value="{{ $budget->year }}"
                           required>
                </div>

                {{-- Budget --}}
                <div class="mb-3">
                    <label class="form-label">Budget</label>
                    <input type="number"
                           name="budget_amount"
                           class="form-control"
                           value="{{ $budget->budget_amount }}"
                           required>
                </div>

                {{-- Action --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('user.budget.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>

                    <button class="btn btn-primary">
                        Update Budget
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

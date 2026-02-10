@extends('app')

@section('content')
<div class="container">

<h3>Budget BBM Company</h3>

<a href="{{ route('admin.budget.create') }}" class="btn btn-primary mb-3">
    Tambah Budget
</a>

<table class="table table-bordered">
<thead>
<tr>
    <th>Company</th>
    <th>Bulan</th>
    <th>Tahun</th>
    <th>Budget</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
@foreach($budgets as $b)
<tr>
    <td>{{ $b->company }}</td>
    <td>{{ $b->month }}</td>
    <td>{{ $b->year }}</td>
    <td>Rp {{ number_format($b->budget_amount) }}</td>
    <td>
    <a href="{{ route('admin.budget.edit', $b->id) }}" 
       class="btn btn-warning btn-sm">
        Edit
    </a>

    <form action="{{ route('admin.budget.destroy', $b->id) }}" 
          method="POST" 
          style="display:inline;">
        @csrf
        @method('DELETE')

        <button type="submit" 
                class="btn btn-danger btn-sm"
                onclick="return confirm('Yakin ingin menghapus data ini?')">
            Delete
        </button>
    </form>
</td>


</tr>
@endforeach
</tbody>
</table>

</div>
@endsection

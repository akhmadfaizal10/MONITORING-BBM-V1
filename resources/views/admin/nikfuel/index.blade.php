@extends('app')

@section('content')
<div class="container">

<h3>Harga Fuel per NIK</h3>

<a href="{{ route('admin.nikfuel.create') }}" class="btn btn-primary mb-3">
    Tambah Data
</a>

<table class="table table-bordered">
<thead>
<tr>
    <th>Company</th>
    <th>NIK</th>
    <th>Harga / Liter</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
@foreach($prices as $p)
<tr>
    <td>{{ $p->company }}</td>
    <td>{{ $p->nik }}</td>
    <td>Rp {{ number_format($p->price_per_liter) }}</td>
    <td>
    <a href="{{ route('admin.nikfuel.edit', $p->id) }}"
       class="btn btn-warning btn-sm">
        Edit
    </a>

    <form action="{{ route('admin.nikfuel.destroy', $p->id) }}"
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

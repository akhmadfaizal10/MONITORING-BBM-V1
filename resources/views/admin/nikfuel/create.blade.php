@extends('app')

@section('content')
<div class="container">
<h3>Tambah Harga Fuel</h3>

<form method="POST" action="{{ route('admin.nikfuel.store') }}">
@csrf

<input name="company" class="form-control mb-2" placeholder="Company">
<input name="nik" class="form-control mb-2" placeholder="NIK">
<input name="price_per_liter" class="form-control mb-2" placeholder="Harga per Liter">

<button class="btn btn-success">Simpan</button>
</form>
</div>
@endsection

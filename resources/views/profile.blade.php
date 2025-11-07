@extends('app')

@section('title', 'Profil Saya')
@section('page_title', 'Profil Saya')

@push('styles')
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f5f5f5;
    }

    .profile-container {
        margin: 40px auto;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        max-width: 600px;
        padding: 40px;
    }

    .profile-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #ddd;
        cursor: pointer;
        transition: 0.3s;
    }

    .profile-photo:hover {
        opacity: 0.8;
    }

    .photo-container {
        text-align: center;
        margin-bottom: 25px;
    }

    input[type="file"] {
        display: none;
    }

    .btn-primary {
        background-color: #337ab7;
        border: none;
    }

    .btn-primary:hover {
        background-color: #286090;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="profile-container">
        <h3 class="text-center"><b>Profil Saya</b></h3>
        <hr>

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif

        <!-- Foto Profil -->
        <div class="photo-container">
            <label for="photoInput">
                <img src="{{ asset('profile_photos/' . (Auth::user()->photo ?? 'default.png')) }}" 
                     alt="Foto Profil" 
                     class="profile-photo" 
                     id="previewPhoto">
            </label>
            <input type="file" name="photo" id="photoInput" accept="image/*" form="profileForm">
            <p class="text-muted small mt-2">Klik foto untuk mengganti</p>
        </div>

        <!-- Form Profil -->
        <form id="profileForm" action="{{ route('updateProfile') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-control"
                    value="{{ Auth::user()->name }}" required>
            </div>

            <div class="form-group">
                <label>Email (tidak bisa diubah)</label>
                <input type="email" name="email" class="form-control"
                    value="{{ Auth::user()->email }}" readonly>
            </div>

            <div class="form-group">
                <label>Perusahaan / Instansi</label>
                <input type="text" name="company" class="form-control"
                    value="{{ Auth::user()->company }}">
            </div>

            <div class="form-group">
                <label>Password Baru (opsional)</label>
                <input type="password" name="password" class="form-control"
                    placeholder="Kosongkan jika tidak ingin mengganti">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>

            <hr>
            <p class="text-center">
                <a href="{{ route('dashboard') }}">← Kembali ke Dashboard</a> |
                <a href="#" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            </p>
        </form>

        <!-- Logout Form -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</div>

<script>
    // Preview foto sebelum upload
    document.getElementById('photoInput').addEventListener('change', function(event) {
        const reader = new FileReader();
        reader.onload = function(){
            document.getElementById('previewPhoto').src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    });
</script>
@endsection

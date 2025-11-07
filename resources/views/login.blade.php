<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal Tambang - Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="relative min-h-screen bg-gray-100">

  <!-- Background -->
  <div class="fixed inset-0 z-0">
    <img 
      src="https://cdn.prod.website-files.com/6215eff0aa650a4cc128fae3/626d2e7e2390aa5a34f7ecd1_mengenal%252Bapa%252Bitu%252Bpertambangan.jpeg" 
      alt="Gambar Tambang"
      class="w-full h-full object-cover"
    />
    <div class="absolute inset-0 bg-black opacity-25"></div>
  </div>

  <!-- Konten -->
  <div class="relative z-10 flex h-screen">
    <!-- Sisi kiri gambar -->
    <div class="hidden md:block md:w-1/2"></div>

    <!-- Sisi kanan form -->
    <div class="w-full md:w-1/2 flex items-center justify-center p-8">
      <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl p-8">

          <!-- Header -->
          <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-500 rounded-full mb-4">
              <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7v10c0 5.55 3.84 10 9 11 1.16.21 2.84.21 4 0 5.16-1 9-5.45 9-11V7l-10-5z"/>
                <path d="M8 10h8v2H8v-2z"/>
                <path d="M9 13h6v1H9v-1z"/>
                <path d="M10 15h4v1h-4v-1z"/>
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Portal Tambang</h2>
            <p class="text-gray-600">Masuk ke akun Anda</p>
          </div>

          <!-- Alert -->
          @if(session('error'))
            <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 text-sm">
              <b>Opps!</b> {{ session('error') }}
            </div>
          @endif

          @if(session('message'))
            <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800 text-sm">
              {{ session('message') }}
            </div>
          @endif

          <!-- Form -->
          <form action="{{ route('actionlogin') }}" method="POST" class="space-y-6">
            @csrf

            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input 
                type="email" 
                id="email" 
                name="email"
                required
                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 
                placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                placeholder="Masukkan email Anda">
            </div>

            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
              <div class="relative">
                <input 
                  type="password" 
                  id="password" 
                  name="password"
                  required
                  class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 
                  placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent pr-12"
                  placeholder="Masukkan password Anda">
                <button 
                  type="button" 
                  id="togglePassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
                >
                  <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 
                          9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 
                          0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>

            <button 
              type="submit" 
              class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg 
              focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all">
              Masuk
            </button>

            <p class="text-center text-sm text-gray-600 mt-4">
              Belum punya akun? 
              <a href="{{ url('/register') }}" class="text-amber-600 hover:text-amber-700 font-medium">Daftar di sini</a>
            </p>
          </form>

        </div>
      </div>
    </div>
  </div>

  <script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', () => {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      eyeIcon.classList.toggle('text-amber-500');
    });
  </script>
</body>
</html>

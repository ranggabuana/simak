<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMAK DINDIKPORA Banjarnegara</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-banjarnegara.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="h-full antialiased flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50/30 to-slate-100">

    <!-- Subtle Light Ambient Background Elements -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-300/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-20 h-20 rounded-3xl bg-white p-2.5 items-center justify-center shadow-xl shadow-slate-200/80 ring-4 ring-white border border-slate-200/80 mb-4 transition transform hover:scale-105">
                <img src="{{ asset('images/logo-banjarnegara.png') }}" alt="Logo Kab. Banjarnegara" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">SIMAK PENDIDIKAN</h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">Sistem Informasi Akuntabilitas Kinerja Pendidikan</p>
            <p class="text-xs text-blue-600 font-bold tracking-wide uppercase mt-0.5">DINDIKPORA KABUPATEN BANJARNEGARA</p>
        </div>

        <!-- Crisp Light Card Container -->
        <div class="bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-3xl p-8 sm:p-9 shadow-xl shadow-slate-200/50">
            @if(session('info'))
                <div class="mb-5 p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-medium">
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Email Pegawai / Akun</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input id="email" 
                               name="email" 
                               type="email" 
                               value="{{ old('email', 'admin@dindikpora.banjarnegarakab.go.id') }}" 
                               required 
                               placeholder="nama@dindikpora.banjarnegarakab.go.id"
                               class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50/70 border border-slate-200 text-slate-900 placeholder-slate-400 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" 
                               name="password" 
                               type="password" 
                               value="password" 
                               required 
                               placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50/70 border border-slate-200 text-slate-900 placeholder-slate-400 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer font-medium">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>Ingat Sesi Saya</span>
                    </label>
                    <span class="text-slate-400">Portal Internal DINDIKPORA</span>
                </div>

                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-500 hover:from-blue-700 hover:to-indigo-700 focus:ring-4 focus:ring-blue-500/20 shadow-md shadow-blue-500/25 transition transform hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                    Masuk ke Sistem SIMAK
                </button>
            </form>

            <!-- Quick Demo Login Switcher (Super Admin Only) -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-2.5">Akses Cepat Pengujian</p>
                <div>
                    <button type="button" 
                            onclick="fillCredentials('admin@dindikpora.banjarnegarakab.go.id')"
                            class="w-full p-2.5 rounded-xl bg-slate-50 hover:bg-blue-50/80 border border-slate-200/80 hover:border-blue-300 text-slate-700 transition flex items-center justify-between cursor-pointer group">
                        <div class="text-left">
                            <p class="font-bold text-xs text-blue-700 group-hover:text-blue-800">Akun Super Admin</p>
                            <p class="text-[10px] text-slate-400">admin@dindikpora.banjarnegarakab.go.id</p>
                        </div>
                        <span class="text-[11px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-200 group-hover:bg-blue-100">
                            Isi Otomatis &rarr;
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6 font-medium">
            &copy; {{ date('Y') }} DINDIKPORA Kabupaten Banjarnegara &bull; SIMAK
        </p>
    </div>

    <script>
        function fillCredentials(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }
    </script>
</body>
</html>

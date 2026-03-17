<x-guest-layout>
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-black text-gray-900 tracking-tight italic">MASUK KE PSSM</h2>
        <p class="text-xs text-gray-400 font-black uppercase tracking-widest mt-2">Powered Smart School Management</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Email Institusi</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-600 transition-colors">
                    <i class="fas fa-envelope"></i>
                </div>
                <input id="email" type="email" name="email" :value="old('email')" required autofocus 
                    class="block w-full pl-11 bg-gray-50 border-gray-200 rounded-2xl py-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" 
                    placeholder="nama@sekolah.sch.id" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <label for="password" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Kata Sandi</label>
                @if (Route::has('password.request'))
                    <a class="text-[10px] font-black text-indigo-600 hover:underline uppercase tracking-widest" href="{{ route('password.request') }}">
                        Lupa Sandi?
                    </a>
                @endif
            </div>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-600 transition-colors">
                    <i class="fas fa-lock"></i>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password" 
                    class="block w-full pl-11 bg-gray-50 border-gray-200 rounded-2xl py-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" 
                    placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" class="rounded-lg border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5" name="remember">
                <span class="ms-3 text-[10px] font-black text-gray-500 uppercase tracking-widest group-hover:text-gray-700 transition-colors">Ingat Saya di Perangkat Ini</span>
            </label>
        </div>

        <div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-indigo-100 transition-all transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-sm">
                MASUK SEKARANG <i class="fas fa-sign-in-alt ml-2"></i>
            </button>
        </div>
    </form>

    <div class="mt-8 pt-8 border-t border-gray-100 text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
            Masalah akses? Hubungi Admin IT Sekolah
        </p>
    </div>
</x-guest-layout>

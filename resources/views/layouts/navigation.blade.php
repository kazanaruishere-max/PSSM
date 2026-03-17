<div x-data="{ sidebarOpen: false }" class="pssm-layout">
    <!-- Sidebar for Mobile (Overlay) -->
    <div x-show="sidebarOpen" 
         class="fixed inset-0 z-40 lg:hidden" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="sidebarOpen = false"></div>
    </div>

    <!-- Sidebar Content -->
    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
         class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-border transition-transform duration-300 transform lg:translate-x-0 lg:static lg:inset-0">
        <div class="flex flex-col h-full">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-border">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-primary rounded-md flex items-center justify-center text-primary-foreground shadow-sm">
                        <i data-lucide="school" class="w-5 h-5"></i>
                    </div>
                    <span class="text-lg font-bold tracking-tight">PSSM</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-muted-foreground hover:text-foreground">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <p class="px-2 text-[10px] font-medium text-muted-foreground uppercase tracking-wider mb-2">Utama</p>
                
                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="layout-dashboard">
                    Beranda
                </x-sidebar-link>

                <x-sidebar-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')" icon="megaphone">
                    Pengumuman
                </x-sidebar-link>

                <p class="px-2 text-[10px] font-medium text-muted-foreground uppercase tracking-wider mb-2 mt-6">Akademik</p>

                <x-sidebar-link :href="route('attendances.index')" :active="request()->routeIs('attendances.*')" icon="user-check">
                    Absensi
                </x-sidebar-link>

                <x-sidebar-link :href="route('assignments.index')" :active="request()->routeIs('assignments.*')" icon="book-open">
                    Tugas
                </x-sidebar-link>

                <x-sidebar-link :href="route('quizzes.index')" :active="request()->routeIs('quizzes.*')" icon="graduation-cap">
                    Ujian CBT
                </x-sidebar-link>

                @role('super_admin')
                <p class="px-2 text-[10px] font-medium text-muted-foreground uppercase tracking-wider mb-2 mt-6">Manajemen</p>
                
                <x-sidebar-link :href="route('academic-years.index')" :active="request()->routeIs('academic-years.*')" icon="calendar">
                    Tahun Ajaran
                </x-sidebar-link>
                <x-sidebar-link :href="route('subjects.index')" :active="request()->routeIs('subjects.*')" icon="book">
                    Mapel
                </x-sidebar-link>
                <x-sidebar-link :href="route('classes.index')" :active="request()->routeIs('classes.*')" icon="building">
                    Kelas
                </x-sidebar-link>
                <x-sidebar-link :href="route('users.index')" :active="request()->routeIs('users.*')" icon="users">
                    User Akses
                </x-sidebar-link>
                @endrole
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-border">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full btn btn-ghost justify-start text-muted-foreground hover:text-destructive">
                        <i data-lucide="log-out" class="lucide"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="h-16 bg-white border-b border-border flex items-center justify-between px-6 lg:px-10 sticky top-0 z-30">
            <div class="flex items-center">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-muted-foreground hover:text-foreground">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h2 class="ml-4 lg:ml-0 text-sm font-semibold text-foreground">
                    @yield('header_title', 'Sistem PSSM')
                </h2>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ route('notifications.index') }}" class="btn btn-ghost btn-icon relative text-muted-foreground">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute top-2 right-2 w-2 h-2 bg-destructive rounded-full"></span>
                    @endif
                </a>

                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 btn btn-ghost px-2">
                    <div class="w-8 h-8 rounded-full bg-secondary flex items-center justify-center font-medium text-xs text-secondary-foreground border border-border">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <span class="hidden md:block text-sm font-medium">{{ auth()->user()->name }}</span>
                </a>
            </div>
        </header>

        <!-- Dynamic Content -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-10 bg-slate-50/50">
            {{ $slot }}

            <footer class="mt-20 pt-8 pb-10 border-t border-border text-center">
                <p class="text-[10px] text-muted-foreground uppercase font-medium tracking-widest">
                    &copy; {{ date('Y') }} PSSM CORE &bull; POWERED SMART SCHOOL MANAGEMENT
                </p>
            </footer>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

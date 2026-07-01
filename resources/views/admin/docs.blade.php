@extends('layouts.guest')

@section('title', 'SmarTern Developer Documentation')

@section('content')
<div class="min-h-screen bg-[#FDFDFD] dark:bg-[#0F172A] text-[#334155] dark:text-[#94A3B8] font-sans antialiased selection:bg-red-500 selection:text-white transition-colors duration-200"
     x-data="{
         searchQuery: '',
         activeSection: 'installation',
         darkMode: localStorage.getItem('darkMode') === 'true' || 
                   (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
         mobileMenuOpen: false,
         
         init() {
             this.$watch('darkMode', val => {
                 localStorage.setItem('darkMode', val);
                 if (val) document.documentElement.classList.add('dark');
                 else document.documentElement.classList.remove('dark');
             });
             document.documentElement.classList.toggle('dark', this.darkMode);
         }
     }">

    <!-- Laravel Docs Style Header -->
    <header class="sticky top-0 z-40 w-full bg-white/95 dark:bg-[#0F172A]/95 backdrop-blur border-b border-[#E2E8F0] dark:border-[#1E293B] transition-colors duration-200">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Left Side: Brand Logo -->
            <div class="flex items-center gap-4">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/blue-smartern.png') }}" class="h-8 w-auto dark:hidden" alt="SmarTern">
                    <img src="{{ asset('images/white-smartern.png') }}" class="h-8 w-auto hidden dark:block" alt="SmarTern">
                    <span class="text-lg font-bold text-[#0F172A] dark:text-white tracking-tight">SmarTern <span class="text-red-500 font-medium">Docs</span></span>
                </a>
                <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">v1.0.0</span>
            </div>

            <!-- Right Side: Search and Dark Mode & Back to Dashboard -->
            <div class="flex items-center gap-4 sm:gap-6">
                <!-- Live Search Box -->
                <div class="relative hidden md:block w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Search documentation..." 
                           class="w-full pl-9 pr-4 py-1.5 bg-[#F1F5F9] dark:bg-[#1E293B] border-0 text-sm rounded-xl focus:ring-2 focus:ring-red-500 text-gray-900 dark:text-gray-150 placeholder-gray-450 dark:placeholder-gray-500 transition-all focus:outline-none">
                </div>

                <!-- Theme Toggle Button -->
                <button @click="darkMode = !darkMode" class="p-2 rounded-xl hover:bg-[#F1F5F9] dark:hover:bg-[#1E293B] text-gray-500 dark:text-gray-400 transition-colors">
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>

                <!-- Back to Admin Dashboard link -->
                <a href="{{ route('admin.dashboard') }}" class="text-xs uppercase tracking-wider font-extrabold text-red-500 hover:text-red-600 dark:hover:text-red-400 transition-colors flex items-center gap-1">
                    Dashboard
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>
    </header>

    <!-- Content Workspace -->
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 flex">
        
        <!-- Sidebar Navigation (Laravel Docs Left Nav style) -->
        <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 lg:sticky lg:top-16 z-30 w-72 h-[calc(100vh-4rem)] bg-white dark:bg-[#0F172A] border-r border-[#E2E8F0] dark:border-[#1E293B] py-8 pl-4 pr-6 overflow-y-auto transition-transform duration-200 ease-in-out shrink-0 custom-scrollbar">
            
            <nav class="space-y-8 text-left">
                <!-- Prologue -->
                <div class="space-y-2">
                    <h5 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Prologue</h5>
                    <ul class="space-y-1 pl-2 border-l border-gray-100 dark:border-gray-800">
                        <li>
                            <button @click="activeSection = 'installation'; mobileMenuOpen = false" 
                                    :class="activeSection === 'installation' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Installation & Env</button>
                        </li>
                        <li>
                            <button @click="activeSection = 'routing'; mobileMenuOpen = false"
                                    :class="activeSection === 'routing' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Routing Architecture</button>
                        </li>
                    </ul>
                </div>

                <!-- Database Specifications -->
                <div class="space-y-2">
                    <h5 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Database & Models</h5>
                    <ul class="space-y-1 pl-2 border-l border-gray-100 dark:border-gray-800">
                        <li>
                            <button @click="activeSection = 'models'; mobileMenuOpen = false"
                                    :class="activeSection === 'models' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Eloquent Models</button>
                        </li>
                        <li>
                            <button @click="activeSection = 'migrations'; mobileMenuOpen = false"
                                    :class="activeSection === 'migrations' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Migrations & Seeders</button>
                        </li>
                    </ul>
                </div>

                <!-- The Portals (Controller Layers) -->
                <div class="space-y-2">
                    <h5 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Core Features</h5>
                    <ul class="space-y-1 pl-2 border-l border-gray-100 dark:border-gray-800">
                        <li>
                            <button @click="activeSection = 'admin_port'; mobileMenuOpen = false"
                                    :class="activeSection === 'admin_port' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Admin Workspace</button>
                        </li>
                        <li>
                            <button @click="activeSection = 'head_port'; mobileMenuOpen = false"
                                    :class="activeSection === 'head_port' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Head Supervisor</button>
                        </li>
                        <li>
                            <button @click="activeSection = 'supervisor_port'; mobileMenuOpen = false"
                                    :class="activeSection === 'supervisor_port' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Supervisor Portal</button>
                        </li>
                        <li>
                            <button @click="activeSection = 'intern_port'; mobileMenuOpen = false"
                                    :class="activeSection === 'intern_port' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Intern Portal</button>
                        </li>
                        <li>
                            <button @click="activeSection = 'coordinator_port'; mobileMenuOpen = false"
                                    :class="activeSection === 'coordinator_port' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Coordinator Portal</button>
                        </li>
                    </ul>
                </div>

                <!-- Deep Dive Systems -->
                <div class="space-y-2">
                    <h5 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Deep Dive Algorithms</h5>
                    <ul class="space-y-1 pl-2 border-l border-gray-100 dark:border-gray-800">
                        <li>
                            <button @click="activeSection = 'geofencing_deep'; mobileMenuOpen = false"
                                    :class="activeSection === 'geofencing_deep' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Geofence Verifications</button>
                        </li>
                        <li>
                            <button @click="activeSection = 'matching_deep'; mobileMenuOpen = false"
                                    :class="activeSection === 'matching_deep' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">Matching Algorithm</button>
                        </li>
                        <li>
                            <button @click="activeSection = 'security_deep'; mobileMenuOpen = false"
                                    :class="activeSection === 'security_deep' ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                    class="w-full text-left py-1.5 text-[13px] transition-colors focus:outline-none">MFA & Anti-Hijacking</button>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main Content Panel (Laravel Docs Central Grid style) -->
        <main class="flex-1 min-w-0 py-12 lg:pl-10 text-left">
            
            <!-- SECTION 1: INSTALLATION & ENV -->
            <div x-show="activeSection === 'installation'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Prologue</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Installation & Environment</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    SmarTern requires PHP 8.2+ and a local MySQL/PostgreSQL schema. Follow this setup sequence to spin up the codebase locally.
                </p>

                <div class="space-y-6">
                    <h3 class="text-xl font-bold text-[#0F172A] dark:text-white">Setting up the Environment Variables</h3>
                    <p class="text-sm">
                        Create a `.env` file in the root workspace copied from `.env.example` and align the parameters configuration:
                    </p>
                    <div class="bg-[#0F172A] dark:bg-[#1E293B] text-emerald-400 p-6 rounded-2xl font-mono text-xs overflow-x-auto select-all shadow-inner border border-slate-800">
# Core parameters configuration
APP_NAME=SmarTern
APP_ENV=local
APP_KEY=base64:zSm8Y29yZVNtYXJUZXJuUHJvZHVjdGlvbktleW==
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database coordinates
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartern_production
DB_USERNAME=root
DB_PASSWORD=secret

# Two-Factor credentials settings
GOOGLE2FA_ENABLED=true
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-xl font-bold text-[#0F172A] dark:text-white">System Commands Executions</h3>
                    <p class="text-sm">Run these shell lines sequentially to map composer libraries, compile static assets, and generate table schemas:</p>
                    <div class="bg-[#0F172A] dark:bg-[#1E293B] text-emerald-400 p-6 rounded-2xl font-mono text-xs overflow-x-auto select-all shadow-inner border border-slate-800">
# Ingest PHP dependencies
composer install

# Build static frontend styles and Alpine JS scripts
npm install
npm run build

# Rebuild relational schemas and seed core data
php artisan migrate:fresh --seed
                    </div>
                </div>
            </div>

            <!-- SECTION 2: ROUTING -->
            <div x-show="activeSection === 'routing'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Architectures</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Routing Architecture</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    SmarTern handles requests in `routes/web.php` separating routes into logical middleware blocks grouped by privilege role keys.
                </p>

                <div class="bg-[#0F172A] dark:bg-[#1E293B] text-emerald-400 p-6 rounded-2xl font-mono text-xs overflow-x-auto select-all shadow-inner border border-slate-800">
<span class="text-[#94A3B8]">// Routes partitioning inside web.php</span>
Route::middleware(['auth', 'verified'])->group(function () {
    
    <span class="text-[#94A3B8]">// --- ADMIN BLOCK ---</span>
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/accounts', [AccountController::class, 'index'])->name('account');
        Route::get('/configuration', [AdminController::class, 'configuration'])->name('configuration');
        Route::get('/docs', [DocsController::class, 'index'])->name('docs');
    });

    <span class="text-[#94A3B8]">// --- SUPERVISOR & HEAD SUPERVISOR ---</span>
    Route::middleware(['role:supervisor,head_supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/interns', [SupervisorIntern::class, 'index'])->name('interns');
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks');
    });

    <span class="text-[#94A3B8]">// --- HEAD SUPERVISOR ONLY ---</span>
    Route::middleware(['role:head_supervisor'])->prefix('head-supervisor')->name('head-supervisor.')->group(function () {
        Route::get('/office-matching', [OfficeMatchingController::class, 'index'])->name('matching');
    });

    <span class="text-[#94A3B8]">// --- INTERN BLOCK ---</span>
    Route::middleware(['role:intern'])->prefix('intern')->name('intern.')->group(function () {
        Route::get('/attendance', [InternAttendance::class, 'index'])->name('attendance');
        Route::post('/attendance/process', [InternAttendance::class, 'processScan'])->name('attendance.process');
    });
});
                </div>
            </div>

            <!-- SECTION 3: MODELS -->
            <div x-show="activeSection === 'models'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Database mapping</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Eloquent Models</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    Here are the definitions of all models mapping relational records inside SmarTern.
                </p>

                <!-- Dynamic Accordion of Models -->
                <div class="space-y-6">
                    <!-- User Model -->
                    <div class="p-6 bg-white dark:bg-[#1E293B] border border-[#E2E8F0] dark:border-[#1E293B] rounded-2xl shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-base font-bold text-[#0F172A] dark:text-white font-mono">App\Models\User.php</h3>
                            <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Core Identity</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-4">Binds roles, Google Authenticator credentials secret keys, and links user details metadata sheets.</p>
                        <div class="bg-[#0F172A] dark:bg-[#0F172A] text-emerald-400 p-4 rounded-xl font-mono text-xs overflow-x-auto select-all">
class User extends Authenticatable {
    protected $fillable = ['email', 'password', 'role_id', 'google2fa_secret', 'google2fa_enabled'];

    public function role() {
        return $this->belongsTo(Role::class);
    }
    public function internDetails() {
        return $this->hasOne(InternDetail::class);
    }
}
                        </div>
                    </div>

                    <!-- InternDetail Model -->
                    <div class="p-6 bg-white dark:bg-[#1E293B] border border-[#E2E8F0] dark:border-[#1E293B] rounded-2xl shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-base font-bold text-[#0F172A] dark:text-white font-mono">App\Models\InternDetail.php</h3>
                            <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Student Profiling</span>
                        </div>
                        <p class="text-xs text-gray-550 dark:text-gray-400 mb-4">Binds school information, required training hours count, and current rendered hours count.</p>
                        <div class="bg-[#0F172A] dark:bg-[#0F172A] text-emerald-400 p-4 rounded-xl font-mono text-xs overflow-x-auto select-all">
class InternDetail extends Model {
    protected $fillable = ['user_id', 'office_id', 'required_hours', 'hours_rendered', 'school_coordinator_id'];

    public function user() { return $this->belongsTo(User::class); }
    public function office() { return $this->belongsTo(Office::class); }
}
                        </div>
                    </div>

                    <!-- Office Model -->
                    <div class="p-6 bg-white dark:bg-[#1E293B] border border-[#E2E8F0] dark:border-[#1E293B] rounded-2xl shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-base font-bold text-[#0F172A] dark:text-white font-mono">App\Models\Office.php</h3>
                            <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Division Coordinates</span>
                        </div>
                        <div class="bg-[#0F172A] dark:bg-[#0F172A] text-emerald-400 p-4 rounded-xl font-mono text-xs overflow-x-auto select-all">
class Office extends Model {
    protected $fillable = ['name', 'latitude', 'longitude', 'geofence_radius_meters', 'capacity'];

    public function interns() { return $this->hasMany(InternDetail::class); }
    public function supervisors() { return $this->hasMany(SupervisorDetail::class); }
}
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: MIGRATIONS -->
            <div x-show="activeSection === 'migrations'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Database Schemas</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Migrations & Seeders</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    SmarTern seeders initialize geofenced structures and role constraints.
                </p>

                <div class="space-y-4">
                    <h3 class="text-xl font-bold text-[#0F172A] dark:text-white">RoleSeeder Definition</h3>
                    <div class="bg-[#0F172A] dark:bg-[#1E293B] text-emerald-400 p-6 rounded-2xl font-mono text-xs overflow-x-auto select-all shadow-inner border border-slate-800">
class RoleSeeder extends Seeder {
    public function run() {
        Role::create(['id' => 1, 'name' => 'admin']);
        Role::create(['id' => 2, 'name' => 'head_supervisor']);
        Role::create(['id' => 3, 'name' => 'supervisor']);
        Role::create(['id' => 4, 'name' => 'school_coordinator']);
        Role::create(['id' => 5, 'name' => 'intern']);
    }
}
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-xl font-bold text-[#0F172A] dark:text-white">OfficeSeeder Definition</h3>
                    <div class="bg-[#0F172A] dark:bg-[#1E293B] text-emerald-400 p-6 rounded-2xl font-mono text-xs overflow-x-auto select-all shadow-inner border border-slate-800">
class OfficeSeeder extends Seeder {
    public function run() {
        Office::create([
            'name' => 'SDO-QC Bago Bantay Main Office',
            'latitude' => 14.6534,
            'longitude' => 121.0345,
            'geofence_radius_meters' => 150,
            'capacity' => 15
        ]);
    }
}
                    </div>
                </div>
            </div>

            <!-- SECTION 5: ADMIN PORTAL -->
            <div x-show="activeSection === 'admin_port'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Admin Controllers</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Admin Portal</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    Admin controllers control system-level accounts generation, backup automation, and log evaluations.
                </p>

                <div class="space-y-6">
                    <div class="p-6 bg-white dark:bg-[#1E293B] border border-[#E2E8F0] dark:border-[#1E293B] rounded-2xl shadow-sm">
                        <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">AccountController.php</h4>
                        <div class="bg-[#0F172A] dark:bg-[#0F172A] text-emerald-400 p-4 rounded-xl font-mono text-xs overflow-x-auto select-all">
public function store(Request $request) {
    $request->validate(['email' => 'required|email|unique:users', 'role_id' => 'required']);
    $user = User::create([
        'email' => $request->email,
        'password' => Hash::make(Str::random(12)),
        'role_id' => $request->role_id
    ]);
    return redirect()->back()->with('success', 'User account created.');
}
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 6: HEAD SUPERVISOR -->
            <div x-show="activeSection === 'head_port'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Head Supervisor Controllers</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Head Supervisor Portal</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    Allows Head Supervisors to configure office vacancies, rules, match algorithms, and signatures templates.
                </p>
            </div>

            <!-- SECTION 7: SUPERVISOR -->
            <div x-show="activeSection === 'supervisor_port'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Supervisor Controllers</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Supervisor Portal</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    Manages internships assignments, logs validation rules, schedules approvals, and weekly evaluations.
                </p>
            </div>

            <!-- SECTION 8: INTERN PORTAL -->
            <div x-show="activeSection === 'intern_port'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Intern Controllers</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Intern Portal</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    Enables student workflows (timekeeping validation scanning, daily logging, journal postings, certificates downloads).
                </p>
            </div>

            <!-- SECTION 9: COORDINATOR -->
            <div x-show="activeSection === 'coordinator_port'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Coordinator Controllers</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Coordinator Portal</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    Provides school advisors views of total hours accumulation, and exports progress tracking files.
                </p>
            </div>

            <!-- SECTION 10: GEOFENCING -->
            <div x-show="activeSection === 'geofencing_deep'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Algorithm Math</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Geofence Coordinate Verification</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    Attendance uses the Haversine math model to verify geographical scans coordinates against configured office coordinates.
                </p>

                <div class="space-y-4">
                    <h3 class="text-xl font-bold text-[#0F172A] dark:text-white">Verification Logic</h3>
                    <div class="bg-[#0F172A] dark:bg-[#1E293B] text-emerald-400 p-6 rounded-2xl font-mono text-xs overflow-x-auto select-all shadow-inner border border-slate-800">
private function verifyGeofence($userLat, $userLon, $officeLat, $officeLon, $radius) {
    $earthRadius = 6371000; <span class="text-gray-400 font-bold">// meters</span>
    $latDelta = deg2rad($officeLat - $userLat);
    $lonDelta = deg2rad($officeLon - $userLon);

    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos(deg2rad($userLat)) * cos(deg2rad($officeLat)) *
         sin($lonDelta / 2) * sin($lonDelta / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    return $distance <= $radius;
}
                    </div>
                </div>
            </div>

            <!-- SECTION 11: MATCHING -->
            <div x-show="activeSection === 'matching_deep'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Algorithm Logic</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Placement Matching Scoring</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    Intake matching works by intersecting comma-separated applicant skills with the department required skills tags arrays.
                </p>
            </div>

            <!-- SECTION 12: SECURITY -->
            <div x-show="activeSection === 'security_deep'" x-cloak class="space-y-8 animate-fade-in">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Security Controls</span>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] dark:text-white mt-1 tracking-tight">Two-Factor Authentication & Anti-Hijacking</h1>
                    <div class="h-1 bg-red-500 w-12 mt-4 rounded-full"></div>
                </div>

                <p class="text-lg text-[#475569] dark:text-[#94A3B8] leading-relaxed">
                    Uses Google 2FA (TOTP authenticator app check) and email verification OTPs through custom middleware classes.
                </p>

                <div class="space-y-6">
                    <h3 class="text-xl font-bold text-[#0F172A] dark:text-white">Single Session & Device Profiling</h3>
                    <p class="text-sm">
                        The application implements a unique `CheckSingleSession` middleware layer that acts as an advanced anti-hijacking guard:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-sm">
                        <li><strong>Session Hijacking Protection:</strong> Restricts active sessions to one concurrent device/browser window. Session IDs are registered inside a centralized cache.</li>
                        <li><strong>Fingerprint Mismatch Invalidation:</strong> Compares user-agents, IP addresses, custom client IDs (`smartern_client_id`), and device IDs (`smartern_device_id`) on each state request.</li>
                        <li><strong>Safe Client ID Initialization:</strong> Dynamically synchronizes the client ID with the session store if a user authenticates from a guest flow where the Javascript device binding had not run yet.</li>
                    </ul>
                </div>

                <div class="space-y-6">
                    <h3 class="text-xl font-bold text-[#0F172A] dark:text-white">Inactivity Session Timeout Manager</h3>
                    <p class="text-sm">
                        To prevent physical security breaches, the application executes multi-layered inactivity checks:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-sm">
                        <li><strong>Server Heartbeat Ping:</strong> Authenticated views query `/session/ping` every 10 seconds. Session expiration on the server side automatically redirects to the login screen with a timeout notification.</li>
                        <li><strong>Client Inactivity Monitor:</strong> Alpine JS tracks active UI events (clicks, scrolling, key presses). 30 minutes of idle time invokes a warning overlay with a 10-second logout counter.</li>
                        <li><strong>Stale Tab Interlocks:</strong> Checks `localStorage` activity timestamps before performing inactivity redirects, preventing stale background tabs from logging out active sessions from other windows.</li>
                        <li><strong>Inactivity Timestamp Reset:</strong> Login, password resets, and updates forms clear the localStorage activity store on submit to prevent guest AFK state from triggering immediate timeout warnings.</li>
                    </ul>
                </div>
            </div>

        </main>
    </div>
</div>
@endsection

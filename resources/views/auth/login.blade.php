<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Supply Chain Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN for guaranteed rendering without build step) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Plus Jakarta Sans', 'sans-serif'],
                        'outfit': ['Outfit', 'sans-serif'],
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        drift: {
                            '0%': { transform: 'translate(0, 0)' },
                            '100%': { transform: 'translate(20px, 20px)' },
                        }
                    },
                    animation: {
                        float: 'float 6s ease-in-out infinite',
                        drift: 'drift 10s ease-in-out infinite alternate',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-slate-50 text-slate-900 min-h-screen flex selection:bg-blue-500 selection:text-white">

    <div class="flex w-full min-h-screen shadow-2xl overflow-hidden">
        
        <!-- VISUAL SIDE (Left on Desktop) -->
        <div class="hidden lg:flex lg:w-[55%] bg-slate-900 relative overflow-hidden flex-col justify-center p-16 text-white">
            <!-- Background Elements (Supply Chain Nodes) -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(59,130,246,0.15),transparent_50%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_80%,rgba(99,102,241,0.15),transparent_50%)]"></div>
            
            <!-- Dynamic Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.03]" 
                 style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 50px 50px;">
            </div>

            <!-- Floating Nodes Animation (Abstract Supply Chain) -->
            <div class="absolute top-1/4 left-1/4 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl animate-drift"></div>
            <div class="absolute bottom-1/3 right-1/4 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl animate-drift" style="animation-delay: 2s;"></div>

            <!-- Content -->
            <div class="relative z-10 max-w-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/10 backdrop-blur-md text-xs font-medium text-blue-200 mb-6 w-fit">
                    <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                    System Operational
                </div>

                <h1 class="font-outfit text-5xl md:text-6xl font-bold leading-tight mb-8 text-transparent bg-clip-text bg-gradient-to-r from-white via-blue-100 to-slate-400">
                    Supply Chain <br>Intelligence
                </h1>

                <div class="grid gap-6">
                    <!-- Feature 1 -->
                    <div class="group flex items-start gap-4 p-4 rounded-2xl transition-all duration-300 hover:bg-white/5 border border-transparent hover:border-white/10">
                        <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br from-blue-500/20 to-indigo-500/20 text-2xl group-hover:scale-110 transition-transform">
                            🌐
                        </div>
                        <div>
                            <h3 class="font-outfit text-lg font-semibold text-white mb-1">Real-time Tracking</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">
                                Monitor your fleet and shipments globally with precision GPS and live telemetry.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group flex items-start gap-4 p-4 rounded-2xl transition-all duration-300 hover:bg-white/5 border border-transparent hover:border-white/10">
                        <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 text-2xl group-hover:scale-110 transition-transform">
                            🏭
                        </div>
                        <div>
                            <h3 class="font-outfit text-lg font-semibold text-white mb-1">Integrated Ecosystem</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">
                                Unified workflow connecting Suppliers, Factories, and Distributors in one platform.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group flex items-start gap-4 p-4 rounded-2xl transition-all duration-300 hover:bg-white/5 border border-transparent hover:border-white/10">
                        <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 text-2xl group-hover:scale-110 transition-transform">
                            🔐
                        </div>
                        <div>
                            <h3 class="font-outfit text-lg font-semibold text-white mb-1">Secure Access</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">
                                Enterprise-grade security with role-based verification for every stakeholder.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Stats/Decor -->
            <div class="absolute bottom-10 left-16 flex gap-8 text-slate-500 text-xs font-mono uppercase tracking-widest opacity-60">
                <div>Lat: 07° 16' S</div>
                <div>Long: 112° 47' E</div>
                <div>Status: Online</div>
            </div>
        </div>

        <!-- LOGIN SIDE (Right / Full on Mobile) -->
        <div class="w-full lg:w-[45%] flex flex-col relative bg-white lg:rounded-l-[3rem] shadow-[-20px_0_60px_rgba(0,0,0,0.05)] z-20 overflow-y-auto">
            
            <div class="flex-1 flex flex-col justify-center items-center p-8 md:p-12 lg:p-16 w-full max-w-md mx-auto">
                <!-- Mobile Header (Visible only on small screens) -->
                <div class="lg:hidden text-center mb-10">
                     <div class="inline-block text-5xl mb-4 animate-float">📦</div>
                     <h1 class="font-outfit text-3xl font-bold text-slate-900">Supply Chain Portal</h1>
                </div>

                <!-- Desktop Logo (Hidden on mobile to save space or layout differently) -->
                <div class="hidden lg:block text-center mb-2">
                    <div class="text-6xl mb-6 inline-block animate-float drop-shadow-lg">📦</div>
                </div>

                <div class="w-full text-center space-y-2 mb-10">
                    <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight">Welcome Back</h2>
                    <p class="text-slate-500 text-sm font-medium">Seamlessly manage your logistics network.</p>
                </div>

                <!-- Error Alert -->
                @if(session('error'))
                    <div class="w-full mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-center gap-3 text-red-600 text-sm font-medium animate-pulse">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Google Button -->
                <a href="{{ route('auth.google') }}" 
                   class="group w-full relative flex items-center justify-center gap-3 px-6 py-4 bg-white border border-slate-200 rounded-2xl text-slate-600 font-outfit font-semibold transition-all duration-300 hover:border-blue-300 hover:bg-blue-50/50 hover:shadow-lg hover:shadow-blue-500/10 hover:-translate-y-0.5 active:translate-y-0 text-base">
                    
                    <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Continue with Google Account</span>
                    
                    <div class="absolute right-4 w-2 h-2 rounded-full bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </a>

                <p class="mt-8 text-center text-xs text-slate-400">
                    Use your institutional account to continue.<br>By logging in, you agree to our Terms of Service.
                </p>

                <!-- Footer Contacts -->
                <div class="w-full mt-12 pt-8 border-t border-slate-100">
                    <h4 class="font-outfit text-xs font-bold text-slate-400 uppercase tracking-widest text-center mb-4">Contact Superadmin</h4>
                    
                    <div class="space-y-2">
                        <!-- Contact Item -->
                        <a href="mailto:5002221003@student.its.ac.id" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-white hover:shadow-md hover:shadow-slate-200/50 hover:scale-[1.02] border border-transparent hover:border-slate-100 transition-all duration-300 group cursor-pointer text-sm">
                            <span class="font-medium text-slate-600 group-hover:text-blue-600 transition-colors">Nicholas Joe Sumantri</span>
                            <span class="text-xs font-mono text-slate-400 bg-white px-2 py-1 rounded border border-slate-100 group-hover:border-blue-100 group-hover:text-blue-500">5002221003</span>
                        </a>

                        <a href="mailto:5002221041@student.its.ac.id" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-white hover:shadow-md hover:shadow-slate-200/50 hover:scale-[1.02] border border-transparent hover:border-slate-100 transition-all duration-300 group cursor-pointer text-sm">
                            <span class="font-medium text-slate-600 group-hover:text-blue-600 transition-colors">Nabilah Safa Nur Fatimah</span>
                            <span class="text-xs font-mono text-slate-400 bg-white px-2 py-1 rounded border border-slate-100 group-hover:border-blue-100 group-hover:text-blue-500">5002221041</span>
                        </a>

                        <a href="mailto:5002221055@student.its.ac.id" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-white hover:shadow-md hover:shadow-slate-200/50 hover:scale-[1.02] border border-transparent hover:border-slate-100 transition-all duration-300 group cursor-pointer text-sm">
                            <span class="font-medium text-slate-600 group-hover:text-blue-600 transition-colors">Marsyanda Auditya</span>
                            <span class="text-xs font-mono text-slate-400 bg-white px-2 py-1 rounded border border-slate-100 group-hover:border-blue-100 group-hover:text-blue-500">5002221055</span>
                        </a>

                        <a href="mailto:5002221084@student.its.ac.id" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-white hover:shadow-md hover:shadow-slate-200/50 hover:scale-[1.02] border border-transparent hover:border-slate-100 transition-all duration-300 group cursor-pointer text-sm">
                            <span class="font-medium text-slate-600 group-hover:text-blue-600 transition-colors">Moch Fajar Aditya Putra</span>
                            <span class="text-xs font-mono text-slate-400 bg-white px-2 py-1 rounded border border-slate-100 group-hover:border-blue-100 group-hover:text-blue-500">5002221084</span>
                        </a>

                        <a href="mailto:5002221085@student.its.ac.id" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-white hover:shadow-md hover:shadow-slate-200/50 hover:scale-[1.02] border border-transparent hover:border-slate-100 transition-all duration-300 group cursor-pointer text-sm">
                            <span class="font-medium text-slate-600 group-hover:text-blue-600 transition-colors">Prasasti Intan Pratiwi</span>
                            <span class="text-xs font-mono text-slate-400 bg-white px-2 py-1 rounded border border-slate-100 group-hover:border-blue-100 group-hover:text-blue-500">5002221085</span>
                        </a>
                    </div>
                </div>

                <!-- Academic Footer -->
                <div class="mt-12 text-center">
                    <div class="font-outfit font-bold text-slate-700 text-sm">Department of Mathematics ITS</div>
                    <div class="text-blue-600 font-medium text-xs mt-1">Group 6 : Database Technology</div>
                    <div class="text-slate-400 text-xs mt-2 font-mono">Surabaya 2025</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        localStorage.removeItem('dismissedBroadcasts');
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Supply Chain GIS') - {{ config('app.name') }}</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #0ea5e9;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e1b4b;
            --darker: #0f0a3c;
            --light: #e0e7ff;
            --bg-glass: rgba(255, 255, 255, 0.05);
            --border-glass: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, #1e3a5f 100%);
            color: #fff;
            min-height: 100vh;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border-glass);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255, 255, 255, 0.4);
            margin-bottom: 0.75rem;
            padding: 0 0.75rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 0.25rem;
        }

        .nav-link:hover {
            background: var(--bg-glass);
            color: #fff;
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
        }

        .nav-icon {
            font-size: 1.25rem;
        }

        .user-section {
            margin-top: auto;
            padding: 1rem;
            background: var(--bg-glass);
            border-radius: 12px;
            border: 1px solid var(--border-glass);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--secondary);
            text-transform: capitalize;
        }

        .btn-logout {
            display: block;
            width: 100%;
            padding: 0.5rem;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin-left: 280px;
        }

        .header {
            padding: 1.5rem 2rem;
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-glass);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        /* Cards */
        .card {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
        }

        /* Stat Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.supplier { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .stat-icon.factory { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-icon.distributor { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .stat-icon.courier { background: linear-gradient(135deg, #0ea5e9, #0284c7); }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .stat-label {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Map */
        .map-container {
            height: 500px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border-glass);
        }

        #map {
            height: 100%;
            width: 100%;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-glass);
            border: 1px solid var(--border-glass);
            border-radius: 10px;
            color: #fff;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #16a34a);
            color: #fff;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #d97706);
            color: #fff;
        }

        .btn-info {
            background: linear-gradient(135deg, var(--secondary), #0284c7);
            color: #fff;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: #fff;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-glass);
        }

        .table th {
            font-weight: 600;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table tbody tr:hover {
            background: var(--bg-glass);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success { background: rgba(34, 197, 94, 0.2); color: #86efac; }
        .badge-warning { background: rgba(245, 158, 11, 0.2); color: #fcd34d; }
        .badge-danger { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }
        .badge-info { background: rgba(14, 165, 233, 0.2); color: #7dd3fc; }

        /* Alert */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        /* Grid */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-glass);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-glass);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Leaflet popup customization */
        .leaflet-popup-content-wrapper {
            background: var(--dark);
            color: #fff;
            border-radius: 12px;
        }

        .leaflet-popup-tip {
            background: var(--dark);
        }

        .popup-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .popup-type {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .popup-type.supplier { background: #22c55e; }
        .popup-type.factory { background: #f59e0b; }
        .popup-type.distributor { background: #6366f1; }
        .popup-type.courier { background: #0ea5e9; }

        .popup-info {
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .popup-products {
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid var(--border-glass);
        }

        .popup-product {
            font-size: 0.8rem;
            padding: 0.2rem 0;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="app-container">
        @auth
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-icon">🌐</div>
                <span class="logo-text">Supply Chain GIS</span>
            </div>

            <nav>
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">📊</span>
                        Dashboard
                    </a>
                </div>

                @if(auth()->user()->role === 'superadmin')
                <div class="nav-section">
                    <div class="nav-section-title">Admin</div>
                    <a href="{{ route('superadmin.index') }}" class="nav-link {{ request()->routeIs('superadmin.index') ? 'active' : '' }}">
                        <span class="nav-icon">⚙️</span>
                        Admin Panel
                    </a>
                    <a href="{{ route('superadmin.suppliers') }}" class="nav-link {{ request()->routeIs('superadmin.suppliers*') || request()->routeIs('superadmin.add.supplier') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span>
                        Manage Suppliers
                    </a>
                    <a href="{{ route('superadmin.factories') }}" class="nav-link {{ request()->routeIs('superadmin.factories*') || request()->routeIs('superadmin.add.factory') ? 'active' : '' }}">
                        <span class="nav-icon">🏭</span>
                        Manage Factories
                    </a>
                    <a href="{{ route('superadmin.distributors') }}" class="nav-link {{ request()->routeIs('superadmin.distributors*') || request()->routeIs('superadmin.add.distributor') ? 'active' : '' }}">
                        <span class="nav-icon">🚚</span>
                        Manage Distributors
                    </a>
                    <a href="{{ route('superadmin.couriers') }}" class="nav-link {{ request()->routeIs('superadmin.couriers*') || request()->routeIs('superadmin.add.courier') ? 'active' : '' }}">
                        <span class="nav-icon">🛵</span>
                        Manage Couriers
                    </a>
                </div>
                @endif

                @if(auth()->user()->role === 'supplier')
                <div class="nav-section">
                    <div class="nav-section-title">Supplier</div>
                    <a href="{{ route('supplier.index') }}" class="nav-link {{ request()->routeIs('supplier.*') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span>
                        My Products
                    </a>
                </div>
                @endif

                @if(auth()->user()->role === 'factory')
                <div class="nav-section">
                    <div class="nav-section-title">Factory</div>
                    <a href="{{ route('factory.index') }}" class="nav-link {{ request()->routeIs('factory.*') ? 'active' : '' }}">
                        <span class="nav-icon">🏭</span>
                        My Factory
                    </a>
                </div>
                @endif

                @if(auth()->user()->role === 'distributor')
                <div class="nav-section">
                    <div class="nav-section-title">Distributor</div>
                    <a href="{{ route('distributor.index') }}" class="nav-link {{ request()->routeIs('distributor.*') ? 'active' : '' }}">
                        <span class="nav-icon">🚚</span>
                        My Warehouse
                    </a>
                </div>
                @endif

                @if(auth()->user()->role === 'courier' || auth()->user()->role === 'superadmin')
                <div class="nav-section">
                    <div class="nav-section-title">Courier</div>
                    <a href="{{ route('courier.index') }}" class="nav-link {{ request()->routeIs('courier.*') ? 'active' : '' }}">
                        <span class="nav-icon">🛵</span>
                        My Deliveries
                    </a>
                </div>
                @endif
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}" 
                         alt="Avatar" 
                         class="user-avatar">
                    <div>
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ auth()->user()->role }}</div>
                    </div>
                </div>
                <a href="{{ route('logout.confirm') }}" class="btn-logout">Sign Out</a>
            </div>
        </aside>
        @endauth

        <main class="main-content">
            <header class="header">
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
                <div>
                    @yield('header-actions')
                </div>
            </header>

            <div class="content fade-in">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    @yield('scripts')
</body>
</html>

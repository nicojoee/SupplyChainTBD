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

        /* Select dropdown styling */
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23fff' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
            cursor: pointer;
        }

        select.form-control option {
            background: #1e1b4b;
            color: #fff;
            padding: 0.5rem;
        }

        select.form-control option:hover,
        select.form-control option:checked {
            background: #4f46e5;
            color: #fff;
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

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 200;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            color: #fff;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1.5rem;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }

        .mobile-menu-toggle.open {
            transform: translateX(280px);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 9998;
        }

        /* Responsive Styles */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 9999;
                display: flex;
            }

            .sidebar.open {
                transform: translateX(0);
                z-index: 9999;
            }

            .mobile-menu-toggle {
                display: flex;
                z-index: 10000;
            }

            .sidebar-overlay.active {
                display: block;
                z-index: 9998;
            }

            .header {
                padding: 1rem;
                padding-left: 4rem;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .content {
                padding: 1rem;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .grid-3 {
                grid-template-columns: 1fr;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 0.75rem;
                align-items: flex-start;
                padding-left: 4rem;
            }

            .card {
                padding: 1rem;
            }

            .card-title {
                font-size: 1rem;
            }

            table {
                font-size: 0.8rem;
            }

            table th, table td {
                padding: 0.5rem 0.4rem;
            }

            .btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            .form-control {
                padding: 0.6rem;
                font-size: 0.9rem;
            }

            /* Chat responsive */
            .chat-container {
                flex-direction: column;
                height: auto;
                min-height: calc(100vh - 120px);
            }

            .chat-sidebar {
                width: 100%;
                max-height: 250px;
                border-radius: 16px 16px 0 0;
            }

            .chat-panel {
                min-height: 400px;
                border-radius: 0 0 16px 16px;
            }

            /* Stats responsive */
            .stat-value {
                font-size: 1.5rem;
            }

            /* Map responsive */
            #map {
                min-height: 300px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0.75rem;
                padding-left: 3.5rem;
            }

            .page-title {
                font-size: 1rem;
            }

            .content {
                padding: 0.75rem;
            }

            .card {
                padding: 0.75rem;
                border-radius: 12px;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            .form-group {
                margin-bottom: 0.75rem;
            }

            .form-label {
                font-size: 0.8rem;
            }

            /* Sidebar compact */
            .sidebar {
                width: 280px;
                padding: 1rem;
            }

            .logo {
                margin-bottom: 1rem;
            }

            .logo-text {
                font-size: 1rem;
            }

            .nav-link {
                padding: 0.6rem;
                font-size: 0.9rem;
            }

            .user-section {
                padding: 0.75rem;
            }

            /* Chat mobile improvements */
            .chat-sidebar {
                max-height: 200px;
            }

            .conversation-item {
                padding: 0.5rem;
            }

            .message-bubble {
                max-width: 85% !important;
            }

            /* Stats grid single column on small mobile */
            .stats-grid {
                grid-template-columns: 1fr !important;
                gap: 0.75rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.25rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }

            /* Map container taller on mobile for better usability */
            .map-container {
                height: 55vh !important;
                min-height: 300px !important;
            }

            #map {
                height: 100% !important;
            }

            /* Card header wrap on mobile */
            .card-header {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .card-title {
                font-size: 0.9rem;
                width: 100%;
            }
        }

        /* Utility classes for responsive */
        .hide-mobile {
            display: block;
        }

        .show-mobile {
            display: none;
        }

        @media (max-width: 768px) {
            .hide-mobile {
                display: none !important;
            }

            .show-mobile {
                display: block !important;
            }

            /* Stats grid 2 columns on tablet */
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
        }

        /* Responsive tables */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -0.5rem;
            padding: 0 0.5rem;
        }

        .table-responsive table {
            min-width: 500px;
        }

        /* Touch-friendly buttons */
        @media (max-width: 768px) {
            .btn, button {
                min-height: 44px;
            }

            input[type="text"],
            input[type="number"],
            input[type="email"],
            input[type="password"],
            select,
            textarea {
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }

        /* Mobile modal improvements */
        @media (max-width: 480px) {
            [style*="position: fixed"][style*="z-index: 1000"],
            [style*="position: fixed"][style*="z-index: 10000"] {
                padding: 0.5rem !important;
            }
        }

        /* Pagination responsive */
        .pagination-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-glass);
        }

        @media (max-width: 480px) {
            .pagination-controls {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .pagination-controls .btn {
                flex: 1;
            }
        }

        /* Full width button on mobile */
        .btn-mobile-full {
            display: inline-flex;
        }

        @media (max-width: 480px) {
            .btn-mobile-full {
                width: 100%;
                justify-content: center;
            }
        }

        /* Flex wrap helper */
        .flex-wrap-mobile {
            display: flex;
            gap: 0.5rem;
        }

        @media (max-width: 480px) {
            .flex-wrap-mobile {
                flex-wrap: wrap;
            }

            .flex-wrap-mobile > * {
                flex: 1 1 100%;
            }
        }

        /* Mobile input full width */
        @media (max-width: 768px) {
            .mobile-full-width {
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        /* Grid info for mobile */
        .grid-info-mobile {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.5rem;
        }

        @media (max-width: 480px) {
            .grid-info-mobile {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Card stacking */
        @media (max-width: 480px) {
            .grid-2 {
                gap: 0.75rem;
            }
        }

        /* Alert mobile */
        @media (max-width: 480px) {
            .alert {
                padding: 0.75rem 1rem;
                font-size: 0.85rem;
            }
        }

        /* Badge responsive */
        @media (max-width: 480px) {
            .badge {
                padding: 0.2rem 0.5rem;
                font-size: 0.7rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
<div class="app-container">
        @auth
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
            ☰
        </button>
        <div class="sidebar-overlay" onclick="closeMobileMenu()"></div>
        
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
                    <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                        <span class="nav-icon">💬</span>
                        Chat
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
                        <span class="nav-icon">🏪</span>
                        Manage Distributors
                    </a>
                    <a href="{{ route('superadmin.couriers') }}" class="nav-link {{ request()->routeIs('superadmin.couriers*') || request()->routeIs('superadmin.add.courier') ? 'active' : '' }}">
                        <span class="nav-icon">🚚</span>
                        Manage Couriers
                    </a>
                </div>
                @endif

                @if(auth()->user()->role === 'supplier')
                <div class="nav-section">
                    <div class="nav-section-title">Supplier</div>
                    <a href="{{ route('supplier.index') }}" class="nav-link {{ request()->routeIs('supplier.index') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span>
                        My Products
                    </a>
                    <a href="{{ route('supplier.orders') }}" class="nav-link {{ request()->routeIs('supplier.orders') ? 'active' : '' }}">
                        <span class="nav-icon">📋</span>
                        Incoming Orders
                    </a>
                </div>
                @endif

                @if(auth()->user()->role === 'factory')
                <div class="nav-section">
                    <div class="nav-section-title">Factory</div>
                    <a href="{{ route('factory.index') }}" class="nav-link {{ request()->routeIs('factory.index') ? 'active' : '' }}">
                        <span class="nav-icon">🏭</span>
                        My Products
                    </a>
                    <a href="{{ route('factory.orders') }}" class="nav-link {{ request()->routeIs('factory.orders') ? 'active' : '' }}">
                        <span class="nav-icon">📋</span>
                        Incoming Orders
                    </a>
                    <a href="{{ route('factory.my-orders') }}" class="nav-link {{ request()->routeIs('factory.my-orders') ? 'active' : '' }}">
                        <span class="nav-icon">🛒</span>
                        My Orders
                    </a>
                    <a href="{{ route('factory.marketplace') }}" class="nav-link {{ request()->routeIs('factory.marketplace') ? 'active' : '' }}">
                        <span class="nav-icon">🏪</span>
                        Marketplace
                    </a>
                </div>
                @endif

                @if(auth()->user()->role === 'distributor')
                <div class="nav-section">
                    <div class="nav-section-title">Distributor</div>
                    <a href="{{ route('distributor.index') }}" class="nav-link {{ request()->routeIs('distributor.index') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span>
                        My Stock
                    </a>
                    <a href="{{ route('distributor.orders') }}" class="nav-link {{ request()->routeIs('distributor.orders') ? 'active' : '' }}">
                        <span class="nav-icon">🛒</span>
                        My Orders
                    </a>
                    <a href="{{ route('distributor.marketplace') }}" class="nav-link {{ request()->routeIs('distributor.marketplace') ? 'active' : '' }}">
                        <span class="nav-icon">🏪</span>
                        Marketplace
                    </a>
                </div>
                @endif

                @if(auth()->user()->role === 'courier' || auth()->user()->role === 'superadmin')
                <div class="nav-section">
                    <div class="nav-section-title">Courier</div>
                    <a href="{{ route('courier.index') }}" class="nav-link {{ request()->routeIs('courier.index') ? 'active' : '' }}">
                        <span class="nav-icon">🚛</span>
                        My Deliveries
                    </a>
                    <a href="{{ route('courier.available-deliveries') }}" class="nav-link {{ request()->routeIs('courier.available-deliveries') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span>
                        Available Deliveries
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
    
    @auth
    <!-- Session Timeout: Auto-logout after 2 hours of inactivity -->
    <script>
        (function() {
            // Session timeout configuration (in milliseconds)
            const SESSION_TIMEOUT = {{ config('session.lifetime', 120) }} * 60 * 1000; // Convert minutes to ms
            const WARNING_BEFORE = 5 * 60 * 1000; // Show warning 5 minutes before logout
            
            let lastActivity = Date.now();
            let warningShown = false;
            let warningTimeout = null;
            let logoutTimeout = null;

            // Track user activity
            const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
            
            function resetTimer() {
                lastActivity = Date.now();
                warningShown = false;
                
                // Clear existing timeouts
                if (warningTimeout) clearTimeout(warningTimeout);
                if (logoutTimeout) clearTimeout(logoutTimeout);
                
                // Hide warning if shown
                const warningBanner = document.getElementById('session-warning');
                if (warningBanner) {
                    warningBanner.style.display = 'none';
                }
                
                // Set new timeouts
                warningTimeout = setTimeout(showWarning, SESSION_TIMEOUT - WARNING_BEFORE);
                logoutTimeout = setTimeout(performLogout, SESSION_TIMEOUT);
            }

            function showWarning() {
                if (warningShown) return;
                warningShown = true;
                
                // Create warning banner if not exists
                let warningBanner = document.getElementById('session-warning');
                if (!warningBanner) {
                    warningBanner = document.createElement('div');
                    warningBanner.id = 'session-warning';
                    warningBanner.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        background: linear-gradient(135deg, #f59e0b, #d97706);
                        color: white;
                        padding: 12px 20px;
                        text-align: center;
                        z-index: 10000;
                        font-weight: 500;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                    `;
                    warningBanner.innerHTML = `
                        ⚠️ Anda akan logout otomatis dalam 5 menit karena tidak ada aktivitas. 
                        <button onclick="window.sessionKeepAlive()" style="
                            margin-left: 15px;
                            padding: 6px 16px;
                            background: white;
                            color: #d97706;
                            border: none;
                            border-radius: 6px;
                            cursor: pointer;
                            font-weight: 600;
                        ">Tetap Login</button>
                    `;
                    document.body.insertBefore(warningBanner, document.body.firstChild);
                }
                warningBanner.style.display = 'block';
            }

            function performLogout() {
                // Show logout message
                alert('Sesi Anda telah berakhir karena tidak ada aktivitas selama 2 jam. Anda akan diarahkan ke halaman login.');
                
                // Perform logout via POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("logout") }}';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                document.body.appendChild(form);
                form.submit();
            }

            // Keep alive function (called when user clicks "Tetap Login")
            window.sessionKeepAlive = function() {
                // Make a request to refresh session
                fetch('{{ route("dashboard") }}', {
                    method: 'GET',
                    credentials: 'same-origin'
                }).then(() => {
                    resetTimer();
                }).catch(() => {
                    resetTimer();
                });
            };

            // Add event listeners for activity tracking
            activityEvents.forEach(event => {
                document.addEventListener(event, resetTimer, { passive: true });
            });

            // Initialize timer
            resetTimer();
            
            console.log('Session timeout active: ' + (SESSION_TIMEOUT / 60000) + ' minutes');
        })();
    </script>
    @endauth

    <!-- Mobile Menu Script -->
    <script>
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            toggleBtn.classList.toggle('open');
        }

        function closeMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            toggleBtn.classList.remove('open');
        }

        // Close menu when clicking a nav link on mobile
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 1024) {
                    closeMobileMenu();
                }
            });
        });

        // Close menu on window resize to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                closeMobileMenu();
            }
        });
    </script>
    
    {{-- Auto GPS Tracking for Couriers - runs on ALL pages while logged in --}}
    @auth
    @if(auth()->user()->role === 'courier' && auth()->user()->courier)
    <script>
        // ==========================================
        // AUTO GPS TRACKING FOR COURIERS
        // Runs globally on all pages while logged in
        // ==========================================
        (function() {
            let courierGpsWatchId = null;
            let courierLastLocation = null;
            const COURIER_ID = {{ auth()->user()->courier->id ?? 0 }};
            const GPS_UPDATE_ROUTE = '{{ route("courier.location") }}';
            const CSRF_TOKEN = '{{ csrf_token() }}';
            
            console.log('🛵 [Courier GPS] Auto-tracking initialized for courier ID:', COURIER_ID);
            
            // Check if Geolocation is supported
            if (!navigator.geolocation) {
                console.warn('🛵 [Courier GPS] Geolocation not supported');
                return;
            }
            
            // Start GPS tracking automatically
            function startCourierGpsTracking() {
                if (courierGpsWatchId !== null) {
                    console.log('🛵 [Courier GPS] Already tracking');
                    return;
                }
                
                const options = {
                    enableHighAccuracy: true,
                    timeout: 30000,
                    maximumAge: 0
                };
                
                courierGpsWatchId = navigator.geolocation.watchPosition(
                    onCourierGpsSuccess,
                    onCourierGpsError,
                    options
                );
                
                console.log('🛵 [Courier GPS] watchPosition started, ID:', courierGpsWatchId);
            }
            
            // GPS success callback
            function onCourierGpsSuccess(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                
                console.log(`🛵 [Courier GPS] Position: ${lat.toFixed(6)}, ${lng.toFixed(6)} (±${accuracy.toFixed(0)}m)`);
                
                // Send to server (throttled - only if moved > 5 meters or first time)
                const shouldSend = !courierLastLocation || 
                    calculateCourierDistance(courierLastLocation.lat, courierLastLocation.lng, lat, lng) > 5;
                
                if (shouldSend) {
                    sendCourierLocation(lat, lng, accuracy);
                    courierLastLocation = { lat, lng, accuracy };
                }
            }
            
            // GPS error callback
            function onCourierGpsError(error) {
                console.warn('🛵 [Courier GPS] Error:', error.code, error.message);
                
                // If permission denied, send inactive status
                if (error.code === error.PERMISSION_DENIED) {
                    console.log('🛵 [Courier GPS] Permission denied - sending inactive status');
                    if (courierLastLocation) {
                        sendCourierLocation(courierLastLocation.lat, courierLastLocation.lng, null, false);
                    }
                }
            }
            
            // Send location to server
            function sendCourierLocation(lat, lng, accuracy, isActive = true) {
                fetch(GPS_UPDATE_ROUTE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                        is_gps_active: isActive,
                        accuracy: accuracy
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        console.log('🛵 [Courier GPS] Location sent successfully');
                    }
                })
                .catch(err => {
                    console.error('🛵 [Courier GPS] Server error:', err);
                });
            }
            
            // Calculate distance between two points (meters)
            function calculateCourierDistance(lat1, lng1, lat2, lng2) {
                const R = 6371000;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLng = (lng2 - lng1) * Math.PI / 180;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLng/2) * Math.sin(dLng/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            }
            
            // Auto-start GPS when page loads
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startCourierGpsTracking);
            } else {
                startCourierGpsTracking();
            }
            
            // Keep GPS active - restart if needed every 30 seconds
            setInterval(() => {
                if (courierGpsWatchId === null) {
                    console.log('🛵 [Courier GPS] Restarting tracking...');
                    startCourierGpsTracking();
                }
            }, 30000);
            
            // Send inactive status when page unloads
            window.addEventListener('beforeunload', function() {
                if (courierGpsWatchId !== null) {
                    navigator.geolocation.clearWatch(courierGpsWatchId);
                }
                // Note: This may not always work due to browser limitations
                if (courierLastLocation) {
                    // Use sendBeacon for reliable unload
                    const data = JSON.stringify({
                        latitude: courierLastLocation.lat,
                        longitude: courierLastLocation.lng,
                        is_gps_active: false
                    });
                    navigator.sendBeacon(GPS_UPDATE_ROUTE + '?_token=' + CSRF_TOKEN, data);
                }
            });
        })();
    </script>
    @endif
    @endauth
    
    @yield('scripts')
</body>
</html>

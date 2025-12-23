@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">👥</div>
        <div>
            <div class="stat-value">{{ $stats['users'] }}</div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon supplier">📦</div>
        <div>
            <div class="stat-value">{{ $stats['suppliers'] }}</div>
            <div class="stat-label">Suppliers</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon factory">🏭</div>
        <div>
            <div class="stat-value">{{ $stats['factories'] }}</div>
            <div class="stat-label">Factories</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon distributor">🏪</div>
        <div>
            <div class="stat-value">{{ $stats['distributors'] }}</div>
            <div class="stat-label">Distributors</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon courier">🚚</div>
        <div>
            <div class="stat-value">{{ $stats['couriers'] }}</div>
            <div class="stat-label">Couriers</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #ec4899, #be185d);">📋</div>
        <div>
            <div class="stat-value">{{ $stats['orders'] }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">⏳</div>
        <div>
            <div class="stat-value">{{ $stats['pendingOrders'] }}</div>
            <div class="stat-label">Pending Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">📦</div>
        <div>
            <div class="stat-value">{{ $stats['products'] }}</div>
            <div class="stat-label">Products</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Users</h2>
            <a href="{{ route('superadmin.users') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">View All</a>
        </div>
        <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="hide-mobile">Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td class="hide-mobile">{{ $user->email }}</td>
                    <td>
                        <span class="badge badge-info">{{ ucfirst($user->role) }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        
        <!-- Pagination Controls -->
        <div class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-glass);">
            <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">
                {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}
            </div>
            <div style="display: flex; gap: 0.5rem;">
                @if($users->onFirstPage())
                    <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">← Prev</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="btn" style="background: rgba(255,255,255,0.1);">← Prev</a>
                @endif
                
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="btn btn-primary">Next →</a>
                @else
                    <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">Next →</span>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Orders</h2>
        </div>
        @if($recentOrders->count() > 0)
        <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Status</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>
                        <span class="badge {{ $order->status === 'delivered' ? 'badge-success' : ($order->status === 'pending' ? 'badge-warning' : 'badge-info') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>{{ formatRupiah($order->total_amount) }}</td>
                </tr>
                @endforeach
        </tbody>
        </table>
        </div>
        
        <!-- Pagination Controls -->
        <div class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-glass);">
            <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">
                {{ $recentOrders->firstItem() ?? 0 }}-{{ $recentOrders->lastItem() ?? 0 }} of {{ $recentOrders->total() }}
            </div>
            <div style="display: flex; gap: 0.5rem;">
                @if($recentOrders->onFirstPage())
                    <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">← Prev</span>
                @else
                    <a href="{{ $recentOrders->previousPageUrl() }}" class="btn" style="background: rgba(255,255,255,0.1);">← Prev</a>
                @endif
                
                @if($recentOrders->hasMorePages())
                    <a href="{{ $recentOrders->nextPageUrl() }}" class="btn btn-primary">Next →</a>
                @else
                    <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">Next →</span>
                @endif
            </div>
        </div>
        @else
        <p style="color: rgba(255,255,255,0.5); padding: 1rem 0;">No orders yet.</p>
        @endif
    </div>
</div>
@endsection

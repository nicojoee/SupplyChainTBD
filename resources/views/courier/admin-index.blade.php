@extends('layouts.app')

@section('title', 'Deliveries Overview')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon courier">🚚</div>
        <div>
            <div class="stat-value">{{ $couriers->count() }}</div>
            <div class="stat-label">Total Couriers</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">✅</div>
        <div>
            <div class="stat-value">{{ $couriers->where('status', 'idle')->count() }}</div>
            <div class="stat-label">Idle (Ready)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">📦</div>
        <div>
            <div class="stat-value">{{ $pendingOrders->count() }}</div>
            <div class="stat-label">Pending Assignment</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">🚚</div>
        <div>
            <div class="stat-value">{{ $inTransitCount }}</div>
            <div class="stat-label">In Transit</div>
        </div>
    </div>
</div>

<!-- Courier Status Overview -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🚚 Courier Status</h2>
        <a href="{{ route('superadmin.couriers') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Manage Couriers</a>
    </div>
    @if($couriers->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
        @foreach($couriers as $courier)
        <div style="background: rgba(255,255,255,0.03); border-radius: 12px; padding: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                <div>
                    <div style="font-weight: 600;">{{ $courier->name }}</div>
                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">{{ $courier->vehicle_type ?? 'N/A' }} • {{ $courier->license_plate ?? 'N/A' }}</div>
                </div>
                <!-- Status Badge: Idle=Success(Green), Busy=Danger(Red) -->
                <span class="badge {{ $courier->status === 'idle' ? 'badge-success' : 'badge-danger' }}">
                    {{ ucfirst($courier->status) }}
                </span>
            </div>
            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5);">
                📞 {{ $courier->phone ?? 'No phone' }} | 
                Active Orders: {{ $allOrders->where('courier_id', $courier->id)->whereIn('status', ['processing', 'shipped'])->count() }}
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p style="color: rgba(255,255,255,0.5);">No couriers registered. <a href="{{ route('superadmin.add.courier') }}" style="color: var(--primary);">Add a courier</a></p>
    @endif
</div>

<!-- Pending Orders (Not Assigned) -->
@if($pendingOrders->count() > 0)
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📦 Pending Orders (Need Courier Assignment)</h2>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Items</th>
                <th>Amount</th>
                <th>Assign Courier</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingOrders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>
                    @foreach($order->items as $item)
                        {{ $item->product->name ?? 'N/A' }} (x{{ $item->quantity }})<br>
                    @endforeach
                </td>
                <td>${{ number_format($order->total_amount, 2) }}</td>
                <td>
                    <form action="{{ route('courier.assign', $order) }}" method="POST" style="display: inline-flex; gap: 0.5rem;">
                        @csrf
                        <select name="courier_id" class="form-control" style="width: auto; padding: 0.4rem; font-size: 0.85rem;">
                            <option value="">Select Courier...</option>
                            @foreach($couriers->where('status', 'idle') as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">Assign</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- All Active Deliveries -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🚚 All Deliveries</h2>
    </div>
    @if($allOrders->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Courier</th>
                <th>Items</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allOrders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->courier->name ?? 'Unassigned' }}</td>
                <td>
                    @foreach($order->items as $item)
                        {{ $item->product->name ?? 'N/A' }} (x{{ $item->quantity }})<br>
                    @endforeach
                </td>
                <td>${{ number_format($order->total_amount, 2) }}</td>
                <td>
                    <span class="badge {{ $order->status === 'delivered' ? 'badge-success' : ($order->status === 'shipped' ? 'badge-info' : 'badge-warning') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>{{ $order->updated_at->format('M d, H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Pagination Controls -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-glass);">
        <div style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">
            Showing {{ $allOrders->firstItem() ?? 0 }} - {{ $allOrders->lastItem() ?? 0 }} of {{ $allOrders->total() }} deliveries
        </div>
        <div style="display: flex; gap: 0.5rem;">
            @if($allOrders->onFirstPage())
                <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">← Previous</span>
            @else
                <a href="{{ $allOrders->previousPageUrl() }}" class="btn" style="background: rgba(255,255,255,0.1);">← Previous</a>
            @endif
            
            @if($allOrders->hasMorePages())
                <a href="{{ $allOrders->nextPageUrl() }}" class="btn btn-primary">Next →</a>
            @else
                <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">Next →</span>
            @endif
        </div>
    </div>
    @else
    <p style="color: rgba(255,255,255,0.5);">No deliveries yet.</p>
    @endif
</div>
@endsection

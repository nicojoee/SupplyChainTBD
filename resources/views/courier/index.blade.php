@extends('layouts.app')

@section('title', 'My Deliveries')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🛵 My Courier Profile</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-info" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">
            🗺️ Open Map
        </a>
    </div>
    <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Name</div>
                <div style="font-weight: 600;">{{ $courier->name }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Vehicle</div>
                <div>{{ $courier->vehicle_type ?? 'N/A' }} • {{ $courier->license_plate ?? 'N/A' }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Phone</div>
                <div>{{ $courier->phone ?? 'N/A' }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Current Status</div>
                <span class="badge {{ $courier->status === 'idle' ? 'badge-success' : 'badge-danger' }}">
                    {{ ucfirst($courier->status) }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Available Deliveries to Accept -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📋 Available Deliveries</h2>
    </div>
    @php
        $availableOrders = \App\Models\Order::whereNull('courier_id')
            ->whereIn('status', ['pickup', 'confirmed'])
            ->with(['items.product', 'sellerSupplier', 'sellerFactory'])
            ->take(10)
            ->get();
    @endphp
    @if($availableOrders->count() > 0)
        <div style="padding: 1rem; display: grid; gap: 0.75rem;">
            @foreach($availableOrders as $order)
            <div style="padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <div style="font-weight: 600;">{{ $order->order_number }}</div>
                        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">
                            @foreach($order->items as $item)
                                {{ $item->product->name ?? 'N/A' }} (x{{ $item->quantity }}){{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </div>
                        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5); margin-top: 0.25rem;">
                            From: {{ $order->sellerSupplier->name ?? $order->sellerFactory->name ?? 'Unknown' }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 600; color: #22c55e;">${{ number_format($order->total_amount, 2) }}</div>
                        <form action="{{ route('courier.accept', $order) }}" method="POST" style="margin-top: 0.5rem;">
                            @csrf
                            <button type="submit" class="btn btn-success" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">
                                ✓ Accept Delivery
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <p style="padding: 1rem; color: rgba(255,255,255,0.5);">No available deliveries at the moment.</p>
    @endif
</div>

<!-- Assigned Deliveries -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📦 Assigned Deliveries</h2>
    </div>
    @if($assignedOrders->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Items</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignedOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>
                        @foreach($order->items as $item)
                            {{ $item->product->name ?? 'N/A' }} (x{{ $item->quantity }})<br>
                        @endforeach
                    </td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                    <td>
                        <span class="badge badge-info">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td>
                        <form action="{{ route('courier.orders.status', $order) }}" method="POST" style="display: inline-flex; gap: 0.5rem;">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-control" style="width: auto; padding: 0.4rem; font-size: 0.85rem;">
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">Update</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="padding: 1rem; color: rgba(255,255,255,0.5);">No assigned deliveries.</p>
    @endif
</div>

<!-- Completed Deliveries -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">✅ Completed Deliveries</h2>
    </div>
    @if($completedOrders->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Items</th>
                    <th>Amount</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($completedOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>
                        @foreach($order->items as $item)
                            {{ $item->product->name ?? 'N/A' }} (x{{ $item->quantity }})<br>
                        @endforeach
                    </td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ $order->updated_at->format('M d, Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="padding: 1rem; color: rgba(255,255,255,0.5);">No completed deliveries yet.</p>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('title', 'Incoming Orders')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📋 Incoming Orders (from Distributors)</h2>
    </div>
    <p style="color: rgba(255,255,255,0.6); margin-bottom: 1rem;">
        Orders from distributors. Confirm and process orders here.
    </p>
</div>

<div class="card">
    @if($incomingOrders->count() > 0)
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>From</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incomingOrders as $order)
                    @php
                        $statusColors = [
                            'pending' => 'background: #f59e0b; color: #000;',
                            'confirmed' => 'background: #22c55e; color: #fff;',
                            'processing' => 'background: #3b82f6; color: #fff;',
                            'pickup' => 'background: #8b5cf6; color: #fff;',
                            'in_delivery' => 'background: #06b6d4; color: #fff;',
                            'delivered' => 'background: #10b981; color: #fff;',
                            'cancelled' => 'background: #ef4444; color: #fff;',
                        ];
                    @endphp
                    <tr>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>{{ $order->buyerDistributor->name ?? 'Unknown Distributor' }}</td>
                        <td>
                            @foreach($order->items as $item)
                                <div style="font-size: 0.85rem;">{{ $item->product->name ?? 'Product' }} × {{ $item->quantity }}</div>
                            @endforeach
                        </td>
                        <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                        <td>
                            <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; {{ $statusColors[$order->status] ?? '' }}">
                                {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                        <td style="font-size: 0.85rem;">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            @if($order->status === 'pending')
                                <form action="{{ route('factory.orders.status', $order) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;">
                                        ✅ Confirm
                                    </button>
                                </form>
                            @elseif($order->status === 'confirmed')
                                <form action="{{ route('factory.orders.status', $order) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="processing">
                                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: #3b82f6;">
                                        🔄 Processing
                                    </button>
                                </form>
                            @elseif($order->status === 'processing')
                                <form action="{{ route('factory.orders.status', $order) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="pickup">
                                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: #8b5cf6;">
                                        📦 Ready for Pickup
                                    </button>
                                </form>
                            @elseif($order->status === 'pickup' && !$order->courier_id)
                                <form action="{{ route('factory.orders.request-courier', $order) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: #f59e0b;" 
                                            onclick="return confirm('Request nearest courier for this order?')">
                                        🚚 Call Courier
                                    </button>
                                </form>
                            @elseif($order->courier_id)
                                <span style="color: #22c55e; font-size: 0.8rem;">✓ Courier assigned</span>
                            @else
                                <span style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">Waiting for courier</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: rgba(255,255,255,0.5); text-align: center; padding: 2rem;">
            No incoming orders from distributors yet.
        </p>
    @endif
</div>
@endsection

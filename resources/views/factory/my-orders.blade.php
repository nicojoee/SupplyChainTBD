@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🛒 My Orders (Purchases from Suppliers)</h2>
    </div>
    <p style="color: rgba(255,255,255,0.6); margin-bottom: 1rem;">
        View your order history. You can edit quantity or cancel orders while they are still pending.
    </p>
</div>

<div class="card">
    @if($orders->count() > 0)
        @foreach($orders as $order)
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
        <div style="border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 1rem; margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <div>
                    <strong style="font-size: 1.1rem;">{{ $order->order_number }}</strong>
                    <span style="margin-left: 1rem; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; {{ $statusColors[$order->status] ?? '' }}">
                        {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5);">{{ $order->created_at->format('M d, Y H:i') }}</div>
                    <div>From: <strong>{{ $order->sellerSupplier->name ?? 'Unknown Supplier' }}</strong></div>
                </div>
            </div>
            
            @if($order->status === 'pending')
            <form action="{{ route('factory.my-orders.edit', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <table class="table" style="margin-bottom: 1rem;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? 'Product' }}</td>
                            <td>${{ number_format($item->unit_price, 2) }}</td>
                            <td>
                                <input type="number" name="quantities[{{ $item->id }}]" value="{{ $item->quantity }}" 
                                       min="1" class="form-control" style="width: 80px; padding: 4px;">
                            </td>
                            <td><strong>${{ number_format($item->subtotal, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: 600;">Total:</td>
                            <td><strong style="color: #22c55e;">${{ number_format($order->total_amount, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">
                        💾 Update Order
                    </button>
                    <button type="button" onclick="confirmCancel({{ $order->id }})" class="btn btn-danger" style="padding: 8px 16px;">
                        ❌ Cancel Order
                    </button>
                </div>
            </form>
            
            <!-- Hidden cancel form -->
            <form id="cancel-form-{{ $order->id }}" action="{{ route('factory.my-orders.cancel', $order) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @else
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Product' }}</td>
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td><strong>${{ number_format($item->subtotal, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: 600;">Total:</td>
                        <td><strong style="color: #22c55e;">${{ number_format($order->total_amount, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
            @endif
        </div>
        @endforeach
    @else
        <p style="color: rgba(255,255,255,0.5); text-align: center; padding: 2rem;">
            No orders yet. Visit the <a href="{{ route('factory.marketplace') }}" style="color: #22c55e;">Marketplace</a> to place orders.
        </p>
    @endif
</div>
@endsection

@section('scripts')
<script>
function confirmCancel(orderId) {
    if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        document.getElementById('cancel-form-' + orderId).submit();
    }
}
</script>
@endsection

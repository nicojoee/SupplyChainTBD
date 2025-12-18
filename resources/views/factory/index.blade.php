@extends('layouts.app')

@section('title', 'Factory Dashboard')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">My Factory Profile</h2>
    </div>
    <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Factory Name</div>
                <div style="font-weight: 600;">{{ $factory->name }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Production Capacity</div>
                <div style="color: var(--warning);">{{ number_format($factory->production_capacity) }} units</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Address</div>
                <div>{{ $factory->address }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Location</div>
                <div>{{ $factory->latitude }}, {{ $factory->longitude }}</div>
            </div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">My Products (For Sale)</h2>
        </div>
        @if($products->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $fp)
                    <tr>
                        <td>{{ $fp->product->name ?? 'Unknown' }}</td>
                        <td>${{ number_format($fp->price, 2) }}</td>
                        <td>{{ number_format($fp->production_quantity) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: rgba(255,255,255,0.5);">No products listed.</p>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Buy From Suppliers</h2>
        </div>
        @if($availableSupplierProducts->count() > 0)
            <form action="{{ route('factory.buy') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Select Product</label>
                    <select name="supplier_product_id" class="form-control" required>
                        <option value="">Choose a product...</option>
                        @foreach($availableSupplierProducts as $sp)
                            <option value="{{ $sp->id }}">
                                {{ $sp->product->name ?? 'Unknown' }} - ${{ number_format($sp->price, 2) }} 
                                (from {{ $sp->supplier->name ?? 'Unknown Supplier' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="1" required placeholder="Enter quantity">
                </div>
                <button type="submit" class="btn btn-success">Place Order</button>
            </form>
        @else
            <p style="color: rgba(255,255,255,0.5);">No products available from suppliers.</p>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">My Orders</h2>
    </div>
    @if($orders->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Products</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>
                        @foreach($order->items as $item)
                            {{ $item->product->name ?? 'N/A' }} (x{{ $item->quantity }})<br>
                        @endforeach
                    </td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                    <td>
                        <span class="badge {{ $order->status === 'delivered' ? 'badge-success' : ($order->status === 'pending' ? 'badge-warning' : 'badge-info') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: rgba(255,255,255,0.5);">No orders yet.</p>
    @endif
</div>
@endsection

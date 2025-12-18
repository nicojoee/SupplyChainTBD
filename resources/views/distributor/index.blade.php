@extends('layouts.app')

@section('title', 'Distributor Dashboard')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">My Distributor Profile</h2>
    </div>
    <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Company Name</div>
                <div style="font-weight: 600;">{{ $distributor->name }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Warehouse Capacity</div>
                <div style="color: var(--primary);">{{ number_format($distributor->warehouse_capacity) }} units</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Address</div>
                <div>{{ $distributor->address }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Location</div>
                <div>{{ $distributor->latitude }}, {{ $distributor->longitude }}</div>
            </div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">My Stock</h2>
        </div>
        @if($stocks->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Min Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stocks as $stock)
                    <tr>
                        <td>{{ $stock->product->name ?? 'Unknown' }}</td>
                        <td>{{ number_format($stock->quantity) }}</td>
                        <td>{{ number_format($stock->min_stock_level) }}</td>
                        <td>
                            @if($stock->quantity <= $stock->min_stock_level)
                                <span class="badge badge-danger">Low Stock</span>
                            @else
                                <span class="badge badge-success">OK</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: rgba(255,255,255,0.5);">No stock yet.</p>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Buy From Factories</h2>
        </div>
        @if($availableFactoryProducts->count() > 0)
            <form action="{{ route('distributor.buy') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Select Product</label>
                    <select name="factory_product_id" class="form-control" required>
                        <option value="">Choose a product...</option>
                        @foreach($availableFactoryProducts as $fp)
                            <option value="{{ $fp->id }}">
                                {{ $fp->product->name ?? 'Unknown' }} - ${{ number_format($fp->price, 2) }} 
                                (from {{ $fp->factory->name ?? 'Unknown Factory' }})
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
            <p style="color: rgba(255,255,255,0.5);">No products available from factories.</p>
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

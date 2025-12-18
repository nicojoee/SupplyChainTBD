@extends('layouts.app')

@section('title', 'Supplier Dashboard')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">My Supplier Profile</h2>
    </div>
    <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px; margin-bottom: 1rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Company Name</div>
                <div style="font-weight: 600;">{{ $supplier->name }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Address</div>
                <div>{{ $supplier->address }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Phone</div>
                <div>{{ $supplier->phone ?? 'N/A' }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Location</div>
                <div>{{ $supplier->latitude }}, {{ $supplier->longitude }}</div>
            </div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">My Products</h2>
        </div>
        @if($products->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $sp)
                    <tr>
                        <td>{{ $sp->product->name ?? 'Unknown' }}</td>
                        <td>${{ number_format($sp->price, 2) }}</td>
                        <td>{{ number_format($sp->stock_quantity) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: rgba(255,255,255,0.5);">No products listed yet.</p>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Add Product</h2>
        </div>
        <form action="{{ route('supplier.products.add') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Select Product</label>
                <select name="product_id" class="form-control" required>
                    <option value="">Choose a product...</option>
                    @foreach(\App\Models\Product::all() as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Price ($)</label>
                <input type="number" name="price" class="form-control" step="0.01" min="0" required placeholder="Enter price">
            </div>
            <div class="form-group">
                <label class="form-label">Stock Quantity</label>
                <input type="number" name="stock_quantity" class="form-control" min="0" required placeholder="Enter quantity">
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>
</div>
@endsection

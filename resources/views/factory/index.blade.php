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
            <h2 class="card-title">My Products</h2>
        </div>
        @if($products->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $fp)
                    <tr id="product-row-{{ $fp->id }}">
                        <td>{{ $fp->product->name ?? 'Unknown' }}</td>
                        <td>
                            <span class="display-value">${{ number_format($fp->price, 2) }}</span>
                            <input type="number" class="edit-input form-control" name="price" value="{{ $fp->price }}" 
                                   step="0.01" min="0" style="display: none; width: 100px; padding: 4px;">
                        </td>
                        <td>
                            <span class="display-value">{{ number_format($fp->production_quantity) }}</span>
                            <input type="number" class="edit-input form-control" name="quantity" value="{{ $fp->production_quantity }}" 
                                   min="0" style="display: none; width: 80px; padding: 4px;">
                        </td>
                        <td>
                            <div class="action-btns" style="display: flex; gap: 6px; align-items: center;">
                                <button type="button" class="btn btn-info edit-btn" onclick="enableEdit({{ $fp->id }})" 
                                        style="padding: 6px 12px; font-size: 0.8rem;">
                                    ✏️ Edit
                                </button>
                                <button type="button" class="btn delete-btn" onclick="deleteProduct({{ $fp->id }})" 
                                        style="padding: 6px 12px; font-size: 0.8rem; background: #ef4444;">
                                    🗑️ Delete
                                </button>
                            </div>
                            <div class="save-cancel-btns" style="display: none; gap: 6px; align-items: center;">
                                <button type="button" class="btn btn-primary" onclick="saveProduct({{ $fp->id }})" 
                                        style="padding: 6px 12px; font-size: 0.8rem;">
                                    💾 Save
                                </button>
                                <button type="button" class="btn" onclick="cancelEdit({{ $fp->id }})" 
                                        style="padding: 6px 12px; font-size: 0.8rem; background: rgba(255,255,255,0.1);">
                                    ❌ Cancel
                                </button>
                            </div>
                        </td>
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
            <h2 class="card-title">Add Product</h2>
        </div>
        <form action="{{ route('factory.products.add') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Select Product</label>
                <select name="product_id" class="form-control" required>
                    <option value="">Choose a product...</option>
                    @foreach(\App\Models\Product::finishedProducts()->get() as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Price ($)</label>
                <input type="number" name="price" class="form-control" step="0.01" min="0" required placeholder="Enter selling price">
            </div>
            <div class="form-group">
                <label class="form-label">Production Quantity</label>
                <input type="number" name="production_quantity" class="form-control" min="0" required placeholder="Enter quantity available">
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>
</div>

<div class="grid-2">
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
                        <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; {{ $statusColors[$order->status] ?? '' }}">
                            {{ strtoupper(str_replace('_', ' ', $order->status)) }}
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

<!-- Incoming Orders from Distributors Section -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">📦 Incoming Orders (from Distributors)</h2>
    </div>
    @php
        $incomingOrders = \App\Models\Order::where('seller_type', 'factory')
            ->where('seller_id', $factory->id)
            ->with(['items.product', 'buyerDistributor'])
            ->latest()
            ->get();
    @endphp
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
                    @foreach($incomingOrders as $incOrder)
                    <tr>
                        <td><strong>{{ $incOrder->order_number }}</strong></td>
                        <td>{{ $incOrder->buyerDistributor->name ?? 'Unknown Distributor' }}</td>
                        <td>
                            @foreach($incOrder->items as $item)
                                <div style="font-size: 0.85rem;">{{ $item->product->name ?? 'Product' }} × {{ $item->quantity }}</div>
                            @endforeach
                        </td>
                        <td><strong>${{ number_format($incOrder->total_amount, 2) }}</strong></td>
                        <td>
                            <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; {{ $statusColors[$incOrder->status] ?? '' }}">
                                {{ strtoupper(str_replace('_', ' ', $incOrder->status)) }}
                            </span>
                        </td>
                        <td style="font-size: 0.85rem;">{{ $incOrder->created_at->format('M d, H:i') }}</td>
                        <td>
                            @if($incOrder->status === 'pending')
                                <form action="{{ route('factory.orders.status', $incOrder) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;">
                                        ✅ Confirm
                                    </button>
                                </form>
                            @elseif($incOrder->status === 'confirmed')
                                <form action="{{ route('factory.orders.status', $incOrder) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="processing">
                                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: #3b82f6;">
                                        🔄 Processing
                                    </button>
                                </form>
                            @elseif($incOrder->status === 'processing')
                                <form action="{{ route('factory.orders.status', $incOrder) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="pickup">
                                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: #8b5cf6;">
                                        📦 Ready for Pickup
                                    </button>
                                </form>
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
        <p style="color: rgba(255,255,255,0.5); padding: 1rem;">No incoming orders from distributors yet.</p>
    @endif
</div>
@endsection

@section('scripts')
<script>
function enableEdit(productId) {
    const row = document.getElementById('product-row-' + productId);
    row.querySelectorAll('.display-value').forEach(el => el.style.display = 'none');
    row.querySelectorAll('.edit-input').forEach(el => el.style.display = 'block');
    row.querySelector('.action-btns').style.display = 'none';
    row.querySelector('.save-cancel-btns').style.display = 'flex';
}

function cancelEdit(productId) {
    const row = document.getElementById('product-row-' + productId);
    row.querySelectorAll('.display-value').forEach(el => el.style.display = 'inline');
    row.querySelectorAll('.edit-input').forEach(el => el.style.display = 'none');
    row.querySelector('.action-btns').style.display = 'flex';
    row.querySelector('.save-cancel-btns').style.display = 'none';
}

function saveProduct(productId) {
    const row = document.getElementById('product-row-' + productId);
    const price = row.querySelector('input[name="price"]').value;
    const quantity = row.querySelector('input[name="quantity"]').value;
    
    fetch('{{ route("factory.products.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            product_id: productId,
            price: price,
            production_quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const priceDisplay = row.querySelectorAll('.display-value')[0];
            const qtyDisplay = row.querySelectorAll('.display-value')[1];
            priceDisplay.textContent = '$' + parseFloat(price).toFixed(2);
            qtyDisplay.textContent = parseInt(quantity).toLocaleString();
            cancelEdit(productId);
            alert('Product updated successfully!');
        } else {
            alert('Error: ' + (data.message || 'Failed to update product'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating product');
    });
}

function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        return;
    }
    
    fetch('{{ route("factory.products.delete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('product-row-' + productId).remove();
            alert('Product deleted successfully!');
        } else {
            alert('Error: ' + (data.message || 'Failed to delete product'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting product');
    });
}
</script>
@endsection

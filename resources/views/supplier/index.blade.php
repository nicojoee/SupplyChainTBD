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
                        <th>Price/Ton</th>
                        <th>Stock (Ton)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $sp)
                    <tr id="product-row-{{ $sp->id }}">
                        <td>{{ $sp->product->name ?? 'Unknown' }}</td>
                        <td>
                            <span class="display-value">{{ formatRupiah($sp->price) }}</span>
                            <input type="number" class="edit-input form-control" name="price" value="{{ $sp->price }}" 
                                   step="0.01" min="0" style="display: none; width: 100px; padding: 4px;">
                        </td>
                        <td>
                            <span class="display-value">{{ number_format($sp->stock_quantity) }}</span>
                            <input type="number" class="edit-input form-control" name="stock" value="{{ $sp->stock_quantity }}" 
                                   min="0" style="display: none; width: 80px; padding: 4px;">
                        </td>
                        <td>
                            <div class="action-btns" style="display: flex; gap: 6px; align-items: center;">
                                <button type="button" class="btn btn-info edit-btn" onclick="enableEdit({{ $sp->id }})" 
                                        style="padding: 6px 12px; font-size: 0.8rem;">
                                    ✏️ Edit
                                </button>
                                <button type="button" class="btn delete-btn" onclick="deleteProduct({{ $sp->id }})" 
                                        style="padding: 6px 12px; font-size: 0.8rem; background: #ef4444;">
                                    🗑️ Delete
                                </button>
                            </div>
                            <div class="save-cancel-btns" style="display: none; gap: 6px; align-items: center;">
                                <button type="button" class="btn btn-primary" onclick="saveProduct({{ $sp->id }})" 
                                        style="padding: 6px 12px; font-size: 0.8rem;">
                                    💾 Save
                                </button>
                                <button type="button" class="btn" onclick="cancelEdit({{ $sp->id }})" 
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
                    @foreach(\App\Models\Product::rawMaterials()->get() as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Price/Ton (Rp)</label>
                <input type="number" name="price" class="form-control" step="0.01" min="0" required placeholder="Enter price">
            </div>
            <div class="form-group">
                <label class="form-label">Stock Quantity (Ton)</label>
                <input type="number" name="stock_quantity" class="form-control" min="0" step="0.01" required placeholder="Enter quantity in tons">
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>
</div>

<!-- Incoming Orders Section -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">📦 Incoming Orders</h2>
    </div>
    @if(isset($incomingOrders) && $incomingOrders->count() > 0)
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
                    <tr>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>{{ $order->buyerFactory->name ?? 'Unknown Factory' }}</td>
                        <td>
                            @foreach($order->items as $item)
                                <div style="font-size: 0.85rem;">{{ $item->product->name ?? 'Product' }} × {{ $item->quantity }}</div>
                            @endforeach
                        </td>
                        <td><strong>{{ formatRupiah($order->total_amount) }}</strong></td>
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
                        <td style="font-size: 0.85rem;">{{ $order->created_at->format('M d, H:i') }}</td>
                        <td>
                            @if($order->status === 'pending')
                                <form action="{{ route('supplier.orders.status', $order) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;">
                                        ✅ Confirm
                                    </button>
                                </form>
                            @elseif($order->status === 'confirmed')
                                <form action="{{ route('supplier.orders.status', $order) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="processing">
                                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: #3b82f6;">
                                        🔄 Processing
                                    </button>
                                </form>
                            @elseif($order->status === 'processing')
                                <form action="{{ route('supplier.orders.status', $order) }}" method="POST" style="display: inline;">
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
        <p style="color: rgba(255,255,255,0.5); padding: 1rem;">No incoming orders yet.</p>
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
    const stock = row.querySelector('input[name="stock"]').value;
    
    fetch('{{ route("supplier.products.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            product_id: productId,
            price: price,
            stock_quantity: stock
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update display values
            const priceDisplay = row.querySelectorAll('.display-value')[0];
            const stockDisplay = row.querySelectorAll('.display-value')[1];
            priceDisplay.textContent = '$' + parseFloat(price).toFixed(2);
            stockDisplay.textContent = parseInt(stock).toLocaleString();
            
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
    
    fetch('{{ route("supplier.products.delete") }}', {
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

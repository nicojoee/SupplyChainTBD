@extends('layouts.app')

@section('title', 'Marketplace - Buy From Factories')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🏪 Marketplace - Available Products</h2>
    </div>
    <p style="color: rgba(255,255,255,0.6); margin-bottom: 1rem;">
        Browse products from factories. Products are sorted by distance from your warehouse.
    </p>
</div>

<div class="card">
    @if(count($marketplace) > 0)
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Factory</th>
                        <th>Distance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marketplace as $item)
                    <tr>
                        <td><strong>{{ $item['product_name'] }}</strong></td>
                        <td style="color: #22c55e;">${{ number_format($item['price'], 2) }}</td>
                        <td>{{ number_format($item['stock']) }}</td>
                        <td>
                            <div>{{ $item['seller_name'] }}</div>
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">Factory</div>
                        </td>
                        <td>
                            <span style="color: #f59e0b;">📏 {{ $item['distance'] }} km</span>
                        </td>
                        <td>
                            <button onclick="openBuyModal('{{ $item['product_name'] }}', {{ $item['price'] }}, {{ $item['stock'] }}, {{ $item['id'] }}, '{{ $item['seller_name'] }}')" 
                                    class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;">
                                🛒 Buy
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: rgba(255,255,255,0.5); text-align: center; padding: 2rem;">
            No products available from factories yet.
        </p>
    @endif
</div>

<!-- Purchase Modal -->
<div id="purchase-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: linear-gradient(135deg, #1e1b4b, #0f0a3c); border-radius: 16px; padding: 2rem; max-width: 450px; width: 90%; border: 1px solid rgba(255,255,255,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0;">🛒 Confirm Purchase</h3>
            <button onclick="closeModal()" style="background: transparent; border: none; color: #fff; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <form action="{{ route('distributor.buy') }}" method="POST">
            @csrf
            <input type="hidden" name="factory_product_id" id="modal-product-id">
            
            <div style="margin-bottom: 1rem; padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="color: rgba(255,255,255,0.6);">Factory:</span>
                    <strong id="modal-seller-name"></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="color: rgba(255,255,255,0.6);">Product:</span>
                    <strong id="modal-product-name"></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="color: rgba(255,255,255,0.6);">Unit Price:</span>
                    <strong style="color: #22c55e;" id="modal-unit-price"></strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: rgba(255,255,255,0.6);">Available Stock:</span>
                    <span id="modal-stock"></span>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" id="modal-quantity" min="1" value="1" 
                       class="form-control" onchange="updateTotal()" oninput="updateTotal()">
            </div>
            
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(34, 197, 94, 0.1); border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.3);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 1.1rem;">Total Amount:</span>
                    <strong style="font-size: 1.5rem; color: #22c55e;" id="modal-total">$0.00</strong>
                </div>
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" onclick="closeModal()" class="btn" style="flex: 1; background: rgba(255,255,255,0.1);">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    ✅ Confirm Purchase
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentPrice = 0;
let maxStock = 0;

function openBuyModal(productName, price, stock, productId, sellerName) {
    currentPrice = price;
    maxStock = stock;
    
    document.getElementById('modal-product-id').value = productId;
    document.getElementById('modal-seller-name').textContent = sellerName;
    document.getElementById('modal-product-name').textContent = productName;
    document.getElementById('modal-unit-price').textContent = '$' + price.toFixed(2);
    document.getElementById('modal-stock').textContent = stock.toLocaleString();
    document.getElementById('modal-quantity').max = stock;
    document.getElementById('modal-quantity').value = 1;
    
    updateTotal();
    
    document.getElementById('purchase-modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('purchase-modal').style.display = 'none';
}

function updateTotal() {
    const qty = parseInt(document.getElementById('modal-quantity').value) || 1;
    const total = qty * currentPrice;
    document.getElementById('modal-total').textContent = '$' + total.toFixed(2);
}

// Close modal on outside click
document.getElementById('purchase-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection

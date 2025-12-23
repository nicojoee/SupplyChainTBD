@extends('layouts.app')

@section('title', 'My Deliveries')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🚚 My Courier Profile</h2>
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
                        <div style="font-weight: 600; color: #22c55e;">{{ formatRupiah($order->total_amount) }}</div>
                        <button type="button" class="btn btn-success" style="padding: 0.4rem 0.75rem; font-size: 0.85rem; margin-top: 0.5rem;"
                                onclick="showAcceptConfirmation('{{ $order->order_number }}', '{{ $order->sellerSupplier->name ?? $order->sellerFactory->name ?? 'Unknown' }}', '{{ $order->sellerSupplier->address ?? $order->sellerFactory->address ?? '' }}', '{{ number_format($order->total_amount, 2) }}', {{ $order->id }})">
                            ✓ Accept Delivery
                        </button>
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
                    <td>{{ formatRupiah($order->total_amount) }}</td>
                    <td>
                        <span class="badge badge-info">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
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
                            
                            @php
                                $canCancel = false;
                                $remainingTime = 0;
                                if ($order->courier_accepted_at) {
                                    $minutesSinceAcceptance = now()->diffInMinutes($order->courier_accepted_at);
                                    $canCancel = $minutesSinceAcceptance <= 5;
                                    $remainingTime = max(0, 300 - now()->diffInSeconds($order->courier_accepted_at)); // 300 seconds = 5 minutes
                                }
                            @endphp
                            
                            @if($canCancel)
                                <button type="button" class="btn btn-danger" style="padding: 0.4rem 0.75rem; font-size: 0.8rem;"
                                        onclick="showCancelConfirmation('{{ $order->order_number }}', {{ $order->id }})"
                                        data-order-id="{{ $order->id }}"
                                        data-remaining-seconds="{{ $remainingTime }}">
                                    ❌ Cancel (<span class="cancel-countdown" data-order-id="{{ $order->id }}">{{ floor($remainingTime / 60) }}:{{ str_pad($remainingTime % 60, 2, '0', STR_PAD_LEFT) }}</span>)
                                </button>
                            @endif
                        </div>
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
                    <td>{{ formatRupiah($order->total_amount) }}</td>
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

@section('scripts')
<!-- Accept Delivery Confirmation Modal -->
<div id="acceptModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: linear-gradient(135deg, #1e1b4b, #1e3a5f); border-radius: 16px; padding: 2rem; max-width: 450px; width: 90%; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.25rem; color: #fff;">🚚 Confirm Accept Delivery</h3>
        <div style="background: rgba(255,255,255,0.05); border-radius: 10px; padding: 1rem; margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0.75rem;">
                <span style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">Order Number:</span>
                <div id="acceptOrderNumber" style="font-weight: 600; color: #22c55e;"></div>
            </div>
            <div style="margin-bottom: 0.75rem;">
                <span style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">Pickup From:</span>
                <div id="acceptSellerName" style="font-weight: 500;"></div>
            </div>
            <div style="margin-bottom: 0.75rem;">
                <span style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">Address:</span>
                <div id="acceptSellerAddress" style="font-size: 0.9rem;"></div>
            </div>
            <div>
                <span style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">Order Value:</span>
                <div id="acceptAmount" style="font-weight: 600; color: #22c55e; font-size: 1.1rem;"></div>
            </div>
        </div>
        <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; padding: 0.75rem; margin-bottom: 1.5rem;">
            <p style="font-size: 0.85rem; color: #fcd34d; margin: 0;">
                ⚠️ <strong>Important:</strong> After accepting, you have <strong>5 minutes</strong> to cancel if needed. After that, the delivery must be completed.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
            <button type="button" onclick="closeAcceptModal()" class="btn" style="background: rgba(255,255,255,0.1); padding: 0.6rem 1.25rem;">
                Cancel
            </button>
            <form id="acceptForm" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-success" style="padding: 0.6rem 1.25rem;">
                    ✓ Yes, Accept Delivery
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Delivery Confirmation Modal -->
<div id="cancelModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: linear-gradient(135deg, #1e1b4b, #1e3a5f); border-radius: 16px; padding: 2rem; max-width: 400px; width: 90%; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.25rem; color: #fff;">❌ Cancel Delivery</h3>
        <p style="margin-bottom: 1rem; color: rgba(255,255,255,0.8);">
            Are you sure you want to cancel order <strong id="cancelOrderNumber" style="color: #fca5a5;"></strong>?
        </p>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 0.75rem; margin-bottom: 1.5rem;">
            <p style="font-size: 0.85rem; color: #fca5a5; margin: 0;">
                ⚠️ This order will be returned to the available deliveries pool for other couriers.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
            <button type="button" onclick="closeCancelModal()" class="btn" style="background: rgba(255,255,255,0.1); padding: 0.6rem 1.25rem;">
                Keep Order
            </button>
            <form id="cancelForm" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-danger" style="padding: 0.6rem 1.25rem;">
                    ❌ Yes, Cancel Delivery
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Accept Delivery Modal Functions
function showAcceptConfirmation(orderNumber, sellerName, sellerAddress, amount, orderId) {
    document.getElementById('acceptOrderNumber').textContent = orderNumber;
    document.getElementById('acceptSellerName').textContent = sellerName;
    document.getElementById('acceptSellerAddress').textContent = sellerAddress || 'Address not available';
    document.getElementById('acceptAmount').textContent = '$' + amount;
    document.getElementById('acceptForm').action = '/courier/accept/' + orderId;
    document.getElementById('acceptModal').style.display = 'flex';
}

function closeAcceptModal() {
    document.getElementById('acceptModal').style.display = 'none';
}

// Cancel Delivery Modal Functions
function showCancelConfirmation(orderNumber, orderId) {
    document.getElementById('cancelOrderNumber').textContent = orderNumber;
    document.getElementById('cancelForm').action = '/courier/cancel/' + orderId;
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

// Close modals when clicking outside
document.getElementById('acceptModal').addEventListener('click', function(e) {
    if (e.target === this) closeAcceptModal();
});
document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) closeCancelModal();
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAcceptModal();
        closeCancelModal();
    }
});

// Countdown Timer for Cancel Buttons
function initCountdowns() {
    const cancelButtons = document.querySelectorAll('[data-remaining-seconds]');
    
    cancelButtons.forEach(button => {
        let remaining = parseInt(button.getAttribute('data-remaining-seconds'));
        const orderId = button.getAttribute('data-order-id');
        const countdownSpan = button.querySelector('.cancel-countdown');
        
        if (!countdownSpan || remaining <= 0) return;
        
        const updateCountdown = () => {
            if (remaining <= 0) {
                // Remove the cancel button when time expires
                button.remove();
                return;
            }
            
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            countdownSpan.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            remaining--;
        };
        
        // Update every second
        setInterval(updateCountdown, 1000);
    });
}

// Initialize countdowns when page loads
document.addEventListener('DOMContentLoaded', initCountdowns);
</script>
@endsection

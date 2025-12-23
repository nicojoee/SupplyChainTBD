@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<style>
    #map-card:fullscreen {
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        padding: 0 !important;
        margin: 0 !important;
        border-radius: 0 !important;
        display: flex;
        flex-direction: column;
    }
    #map-card:fullscreen .card-header {
        flex-shrink: 0;
        border-radius: 0;
    }
    #map-card:fullscreen .map-container {
        flex: 1;
        height: calc(100vh - 60px) !important;
    }
    #map-card:fullscreen #map {
        height: 100% !important;
        width: 100% !important;
    }
    
    /* Self-marker pulsing animation */
    @keyframes selfPulse {
        0% {
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.6), 0 4px 15px rgba(0,0,0,0.4);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 35px rgba(255, 255, 255, 0.9), 0 4px 20px rgba(0,0,0,0.5);
            transform: scale(1.08);
        }
        100% {
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.6), 0 4px 15px rgba(0,0,0,0.4);
            transform: scale(1);
        }
    }
    
    .self-marker {
        z-index: 1000 !important;
    }
    
    .self-marker-inner {
        animation: selfPulse 2s infinite ease-in-out;
    }
    
    /* Self-marker legend indicator */
    .self-legend {
        position: absolute;
        bottom: 20px;
        left: 20px;
        background: rgba(30, 27, 75, 0.95);
        backdrop-filter: blur(10px);
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
        color: #fff;
    }
    
    .self-legend-dot {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ec4899, #8b5cf6);
        animation: selfPulse 2s infinite ease-in-out;
    }

    /* Map Controls Container - Bottom Right */
    .map-controls-right {
        position: absolute;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .map-control-btn {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: none;
        background: rgba(30, 27, 75, 0.95);
        backdrop-filter: blur(10px);
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        transition: all 0.2s ease;
    }

    .map-control-btn:hover {
        transform: scale(1.05);
        background: rgba(50, 47, 95, 0.95);
    }

    /* Category Filter - Bottom Left */
    .map-category-filter {
        position: absolute;
        bottom: 20px;
        left: 20px;
        z-index: 1000;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        max-width: 280px;
        background: rgba(30, 27, 75, 0.95);
        backdrop-filter: blur(10px);
        padding: 10px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }

    .category-btn {
        padding: 6px 12px;
        border-radius: 20px;
        border: none;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .category-btn.active {
        box-shadow: 0 0 10px rgba(255,255,255,0.3);
    }

    .category-btn.all { background: #6b7280; color: white; }
    .category-btn.all.active { background: #374151; }
    .category-btn.supplier { background: rgba(34, 197, 94, 0.3); color: #22c55e; }
    .category-btn.supplier.active { background: #22c55e; color: white; }
    .category-btn.factory { background: rgba(245, 158, 11, 0.3); color: #f59e0b; }
    .category-btn.factory.active { background: #f59e0b; color: white; }
    .category-btn.distributor { background: rgba(99, 102, 241, 0.3); color: #6366f1; }
    .category-btn.distributor.active { background: #6366f1; color: white; }
    .category-btn.courier { background: rgba(14, 165, 233, 0.3); color: #0ea5e9; }
    .category-btn.courier.active { background: #0ea5e9; color: white; }

    /* Location pulse animation for auto-locate marker */
    @keyframes locationPulse {
        0% {
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5), 0 2px 8px rgba(0,0,0,0.3);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 25px rgba(59, 130, 246, 0.8), 0 2px 12px rgba(0,0,0,0.4);
            transform: scale(1.1);
        }
        100% {
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5), 0 2px 8px rgba(0,0,0,0.3);
            transform: scale(1);
        }
    }

    /* Button pulse animation for loading state */
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* Auto-locate button responsive */
    @media (max-width: 480px) {
        #auto-locate-btn {
            bottom: 15px !important;
            right: 15px !important;
            width: 44px !important;
            height: 44px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon supplier">📦</div>
        <div>
            <div class="stat-value">{{ $suppliers->count() }}</div>
            <div class="stat-label">Suppliers</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon factory">🏭</div>
        <div>
            <div class="stat-value">{{ $factories->count() }}</div>
            <div class="stat-label">Factories</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon distributor">🏪</div>
        <div>
            <div class="stat-value">{{ $distributors->count() }}</div>
            <div class="stat-label">Distributors</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon courier">🚚</div>
        <div>
            <div class="stat-value">{{ $couriers->count() }}</div>
            <div class="stat-label">Couriers</div>
        </div>
    </div>
</div>

<div class="card" id="map-card">
    <div class="card-header" style="flex-wrap: wrap; gap: 0.75rem;">
        <h2 class="card-title">Supply Chain Map</h2>
        <div class="flex-wrap-mobile" style="display: flex; gap: 0.5rem; align-items: center; flex: 1; min-width: 200px;">
            <!-- Search Box -->
            <div style="position: relative; flex: 1; min-width: 180px;">
                <input type="text" 
                       id="map-search" 
                       class="mobile-full-width"
                       placeholder="🔍 Search..." 
                       style="width: 100%; max-width: 320px; padding: 0.5rem 1rem; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: #fff; font-size: 0.85rem;"
                       autocomplete="off">
                <div id="search-results" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #1e1b4b; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; margin-top: 4px; max-height: 300px; overflow-y: auto; z-index: 1000;"></div>
            </div>
            <button onclick="toggleMapFullscreen()" class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.8rem; white-space: nowrap;" id="fullscreen-btn">
                ⛶ Full
            </button>
        </div>
    </div>
    <div class="map-container" style="position: relative;">
        <div id="map"></div>
        <!-- Map Controls -->
        
        <!-- Category Filter (Bottom Left) -->
        <div class="map-category-filter">
            <button onclick="filterMap('all')" class="category-btn all active" id="filter-all">
                All
            </button>
            <button onclick="filterMap('supplier')" class="category-btn supplier" id="filter-supplier">
                📦 Suppliers
            </button>
            <button onclick="filterMap('factory')" class="category-btn factory" id="filter-factory">
                🏭 Factories
            </button>
            <button onclick="filterMap('distributor')" class="category-btn distributor" id="filter-distributor">
                🏪 Distributors
            </button>
            <button onclick="filterMap('courier')" class="category-btn courier" id="filter-courier">
                🚚 Couriers
            </button>
        </div>

        <!-- Right Side Controls (Bottom Right) -->
        <div class="map-controls-right">
            <!-- Auto Locate Button (For everyone) -->
            <button id="auto-locate-btn" onclick="locateMyAccount()" class="map-control-btn" title="Locate My Account">
                📍
            </button>
        </div>
    </div>
</div>

<!-- My Deliveries Section - Courier Only -->
@if(auth()->user()->role === 'courier')
<div class="card" style="margin-top: 1rem;">
    <div class="card-header" style="flex-wrap: wrap; gap: 0.5rem;">
        <h2 class="card-title">🚚 My Deliveries</h2>
        <div class="flex-wrap-mobile" style="display: flex; gap: 0.5rem; align-items: center;">
            <span id="courier-gps-status" class="badge badge-warning">⏳ Detecting GPS...</span>
            <button id="toggle-gps-btn" onclick="toggleCourierGPS()" class="btn btn-success" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">
                ▶️ Start GPS
            </button>
        </div>
    </div>
    
    <!-- GPS Info with Accuracy -->
    <div style="padding: 0.75rem; background: rgba(255,255,255,0.03); border-bottom: 1px solid var(--border-glass);">
        <div class="grid-info-mobile" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 0.5rem; text-align: center;">
            <div style="background: rgba(255,255,255,0.02); padding: 0.5rem; border-radius: 8px;">
                <div style="color: rgba(255,255,255,0.5); font-size: 0.65rem;">LAT</div>
                <div id="courier-lat" style="font-family: monospace; font-weight: 600; font-size: 0.8rem;">
                    {{ auth()->user()->courier && auth()->user()->courier->current_latitude ? number_format(auth()->user()->courier->current_latitude, 6) : '-' }}
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); padding: 0.5rem; border-radius: 8px;">
                <div style="color: rgba(255,255,255,0.5); font-size: 0.65rem;">LNG</div>
                <div id="courier-lng" style="font-family: monospace; font-weight: 600; font-size: 0.8rem;">
                    {{ auth()->user()->courier && auth()->user()->courier->current_longitude ? number_format(auth()->user()->courier->current_longitude, 6) : '-' }}
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); padding: 0.5rem; border-radius: 8px;">
                <div style="color: rgba(255,255,255,0.5); font-size: 0.65rem;">AKURASI</div>
                <div id="courier-accuracy" style="font-weight: 600; font-size: 0.8rem;">-</div>
                <div id="courier-accuracy-label" style="font-size: 0.6rem;"></div>
            </div>
            <div style="background: rgba(255,255,255,0.02); padding: 0.5rem; border-radius: 8px;">
                <div style="color: rgba(255,255,255,0.5); font-size: 0.65rem;">UPDATE</div>
                <div id="courier-last-update" style="font-size: 0.8rem;">-</div>
            </div>
        </div>
    </div>

    <!-- Assigned Deliveries -->
    <div style="padding: 1rem;">
        <h4 style="margin: 0 0 0.75rem 0; font-size: 1rem;">📦 Assigned Deliveries</h4>
        @if(isset($assignedOrders) && $assignedOrders->count() > 0)
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
            <p style="color: rgba(255,255,255,0.5);">No assigned deliveries.</p>
        @endif
    </div>

    <!-- Available Deliveries -->
    <div style="padding: 1rem; border-top: 1px solid var(--border-glass);">
        <h4 style="margin: 0 0 0.75rem 0; font-size: 1rem;">📋 Available Deliveries</h4>
        @if(isset($availableOrders) && $availableOrders->count() > 0)
            <div style="display: grid; gap: 0.75rem;">
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
                            <form action="{{ route('courier.accept', $order) }}" method="POST" style="margin-top: 0.5rem;">
                                @csrf
                                <button type="submit" class="btn btn-success" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">
                                    ✓ Accept
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p style="color: rgba(255,255,255,0.5);">No available deliveries at the moment.</p>
        @endif
    </div>
</div>
@endif

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📦 Suppliers & Products</h2>
        </div>
        <div id="suppliers-list">
            @include('partials.suppliers-list', ['suppliers' => $suppliers])
        </div>
        @if($suppliers->total() > 3)
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--border-glass);">
            <span id="suppliers-info" style="color: rgba(255,255,255,0.4); font-size: 0.8rem;">{{ $suppliers->firstItem() }}-{{ $suppliers->lastItem() }} of {{ $suppliers->total() }}</span>
            <div style="display: flex; gap: 0.25rem;">
                <button onclick="loadPage('suppliers', 'prev')" id="suppliers-prev" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; background: rgba(255,255,255,0.1);" {{ $suppliers->onFirstPage() ? 'disabled' : '' }}>←</button>
                <button onclick="loadPage('suppliers', 'next')" id="suppliers-next" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" {{ !$suppliers->hasMorePages() ? 'disabled' : '' }}>→</button>
            </div>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🏭 Factories & Products</h2>
        </div>
        <div id="factories-list">
            @include('partials.factories-list', ['factories' => $factories])
        </div>
        @if($factories->total() > 3)
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--border-glass);">
            <span id="factories-info" style="color: rgba(255,255,255,0.4); font-size: 0.8rem;">{{ $factories->firstItem() }}-{{ $factories->lastItem() }} of {{ $factories->total() }}</span>
            <div style="display: flex; gap: 0.25rem;">
                <button onclick="loadPage('factories', 'prev')" id="factories-prev" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; background: rgba(255,255,255,0.1);" {{ $factories->onFirstPage() ? 'disabled' : '' }}>←</button>
                <button onclick="loadPage('factories', 'next')" id="factories-next" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" {{ !$factories->hasMorePages() ? 'disabled' : '' }}>→</button>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🏪 Distributor Stock</h2>
        </div>
        <div id="distributors-list">
            @include('partials.distributors-list', ['distributors' => $distributors])
        </div>
        @if($distributors->total() > 3)
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--border-glass);">
            <span id="distributors-info" style="color: rgba(255,255,255,0.4); font-size: 0.8rem;">{{ $distributors->firstItem() }}-{{ $distributors->lastItem() }} of {{ $distributors->total() }}</span>
            <div style="display: flex; gap: 0.25rem;">
                <button onclick="loadPage('distributors', 'prev')" id="distributors-prev" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; background: rgba(255,255,255,0.1);" {{ $distributors->onFirstPage() ? 'disabled' : '' }}>←</button>
                <button onclick="loadPage('distributors', 'next')" id="distributors-next" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" {{ !$distributors->hasMorePages() ? 'disabled' : '' }}>→</button>
            </div>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🚚 Courier Status</h2>
        </div>
        <div id="couriers-list">
            @include('partials.couriers-list', ['couriers' => $couriers])
        </div>
        @if($couriers->total() > 3)
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--border-glass);">
            <span id="couriers-info" style="color: rgba(255,255,255,0.4); font-size: 0.8rem;">{{ $couriers->firstItem() }}-{{ $couriers->lastItem() }} of {{ $couriers->total() }}</span>
            <div style="display: flex; gap: 0.25rem;">
                <button onclick="loadPage('couriers', 'prev')" id="couriers-prev" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; background: rgba(255,255,255,0.1);" {{ $couriers->onFirstPage() ? 'disabled' : '' }}>←</button>
        @endif
    </div>

</div>



<!-- Purchase Modal -->
@if(auth()->user()->role === 'factory' || auth()->user()->role === 'distributor')
<div id="purchase-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: linear-gradient(145deg, #1e1b4b, #312e81); border-radius: 16px; padding: 24px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; border: 1px solid rgba(255,255,255,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="modal-title" style="margin: 0; color: #fff; font-size: 1.25rem;">🛒 Purchase Order</h3>
            <button onclick="closePurchaseModal()" style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.2rem;">×</button>
        </div>
        
        <div id="modal-seller-info" style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 8px; margin-bottom: 16px;">
            <div id="modal-seller-name" style="font-weight: 600; color: #fff;"></div>
            <div id="modal-seller-type" style="font-size: 0.8rem; color: rgba(255,255,255,0.5);"></div>
        </div>
        
        <form id="purchase-form">
            <input type="hidden" id="modal-seller-id" name="seller_id">
            <input type="hidden" id="modal-seller-entity" name="seller_type">
            
            <div id="modal-products" style="margin-bottom: 20px;">
                <!-- Products will be inserted here -->
            </div>
            
            <div style="background: rgba(255,255,255,0.05); padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: rgba(255,255,255,0.7);">Total Amount:</span>
                    <span id="modal-total" style="font-size: 1.5rem; font-weight: 700; color: #22c55e;">$0.00</span>
                </div>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <button type="button" onclick="closePurchaseModal()" style="flex: 1; padding: 12px; background: rgba(255,255,255,0.1); color: #fff; border: none; border-radius: 8px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" id="modal-submit-btn" style="flex: 2; padding: 12px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    📦 Place Order
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    // Fullscreen toggle for map
    function toggleMapFullscreen() {
        const mapCard = document.getElementById('map-card');
        const btn = document.getElementById('fullscreen-btn');
        
        if (!document.fullscreenElement) {
            mapCard.requestFullscreen().then(() => {
                btn.innerHTML = '✕ Exit';
                mapCard.style.borderRadius = '0';
                setTimeout(() => map.invalidateSize(), 100);
            }).catch(err => {
                console.error('Fullscreen error:', err);
            });
        } else {
            document.exitFullscreen().then(() => {
                btn.innerHTML = '⛶ Full';
                mapCard.style.borderRadius = '';
                setTimeout(() => map.invalidateSize(), 100);
            });
        }
    }

    // Auto-Locate Me function - GPS based location
    let myLocationMarker = null;
    let isLocating = false;
    
    // Define My Location Icon
    const myLocationIcon = L.divIcon({
        className: 'my-location-marker',
        html: `<div style="
            width: 24px; height: 24px; 
            background: linear-gradient(135deg, #3b82f6, #2563eb); 
            border: 3px solid white; 
            border-radius: 50%; 
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.6), 0 2px 8px rgba(0,0,0,0.3);
            animation: locationPulse 2s infinite;
        "></div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    // New Locate function for all users
    function locateMyAccount() {
        // Checking button state to prevent double clicks
        const btn = document.getElementById('auto-locate-btn');
        if (isLocating) return;
        
        isLocating = true;
        btn.innerHTML = '⌛'; // Loading spinner placeholder
        btn.style.animation = 'pulse 1s infinite';

        // 1. Try to use entity location first (for non-couriers)
        @if(auth()->user()->role !== 'courier')
            if (selfEntity && selfEntity.latitude && selfEntity.longitude && selfEntity.latitude != 0) {
                map.setView([selfEntity.latitude, selfEntity.longitude], 16);
                if (selfMarker) selfMarker.openPopup();
                
                // Reset button
                setTimeout(() => {
                    btn.innerHTML = '📍';
                    btn.style.animation = '';
                    isLocating = false;
                }, 500);
                return;
            }
        @endif

        // 2. Use GPS (for Courier or fallback)
        if (!navigator.geolocation) {
            alert('Browser anda tidak mendukung Geolocation.');
            btn.innerHTML = '📍';
            btn.style.animation = '';
            isLocating = false;
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                if (myLocationMarker) {
                    myLocationMarker.setLatLng([lat, lng]);
                    const popupContent = myLocationMarker.getPopup().getContent();
                    // Update content if needed
                    myLocationMarker.setPopupContent(popupContent).openPopup();
                } else {
                    myLocationMarker = L.marker([lat, lng], { icon: myLocationIcon, zIndexOffset: 1000 })
                        .addTo(map)
                        .bindPopup(`
                            <div style="text-align: center;">
                                <strong>📍 Lokasi Anda</strong><br>
                                <span style="font-size: 0.8rem; color: rgba(255,255,255,0.7);">
                                    ${lat.toFixed(6)}, ${lng.toFixed(6)}<br>
                                    Akurasi: ±${Math.round(accuracy)}m
                                </span>
                            </div>
                        `);
                }

                // Zoom to location
                const zoomLevel = accuracy < 50 ? 17 : accuracy < 200 ? 15 : 13;
                map.setView([lat, lng], zoomLevel);
                myLocationMarker.openPopup();

                // Reset button
                btn.innerHTML = '📍';
                btn.style.animation = '';
                isLocating = false;

                // For courier: also update server location
                @if(auth()->user()->role === 'courier')
                sendCourierLocationToServer(lat, lng, true, accuracy);
                @endif
            },
            function(error) {
                console.error('GPS Error:', error);
                let errorMsg = 'Gagal mendapatkan lokasi GPS.';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = 'Izin GPS ditolak. Pastikan izin lokasi aktif.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = 'Signal GPS lemah atau tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        errorMsg = 'Waktu permintaan habis.';
                        break;
                }
                
                alert(errorMsg);
                btn.innerHTML = '📍';
                btn.style.animation = '';
                isLocating = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    }

    // Map Filtering Function
    let activeFilter = 'all';

    function filterMap(category) {
        activeFilter = category;
        
        // Update button states
        document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('filter-' + category).classList.add('active');
        
        // Loop through all markers and show/hide based on category
        // Suppliers
        Object.values(allMarkers.suppliers).forEach(marker => {
            if (category === 'all' || category === 'supplier') {
                if (!map.hasLayer(marker)) marker.addTo(map);
            } else {
                if (map.hasLayer(marker)) marker.removeFrom(map);
            }
        });

        // Factories
        Object.values(allMarkers.factories).forEach(marker => {
            if (category === 'all' || category === 'factory') {
                if (!map.hasLayer(marker)) marker.addTo(map);
            } else {
                if (map.hasLayer(marker)) marker.removeFrom(map);
            }
        });

        // Distributors
        Object.values(allMarkers.distributors).forEach(marker => {
            if (category === 'all' || category === 'distributor') {
                if (!map.hasLayer(marker)) marker.addTo(map);
            } else {
                if (map.hasLayer(marker)) marker.removeFrom(map);
            }
        });

        // Couriers
        Object.values(courierMarkers).forEach(marker => {
            if (category === 'all' || category === 'courier') {
                if (!map.hasLayer(marker)) marker.addTo(map);
            } else {
                if (map.hasLayer(marker)) marker.removeFrom(map);
            }
        });

        // Always keep Self Marker visible if it exists
        if (selfMarker) {
             if (!map.hasLayer(selfMarker)) selfMarker.addTo(map);
        }
    }

    // Listen for fullscreen change (ESC key exit)
    document.addEventListener('fullscreenchange', () => {
        const btn = document.getElementById('fullscreen-btn');
        const mapCard = document.getElementById('map-card');
        if (!document.fullscreenElement && btn) {
            btn.innerHTML = '⛶ Fullscreen';
            mapCard.style.borderRadius = '';
            setTimeout(() => map.invalidateSize(), 100);
        }
    });

    // Initialize map centered on Indonesia with aggressive performance optimizations
    const map = L.map('map', {
        center: [-2.5, 118],
        zoom: 5,
        preferCanvas: true,         // Use canvas for markers (faster than SVG)
        zoomControl: true,
        scrollWheelZoom: true,
        fadeAnimation: false,       // Disable fade animation (reduces CPU)
        zoomAnimation: true,        // Keep zoom animation for UX
        markerZoomAnimation: false, // Disable marker zoom animation
        inertia: true,              // Keep inertia for smooth panning
        inertiaDeceleration: 2000,  // Faster deceleration
        worldCopyJump: false,       // Disable world copy
        maxBoundsViscosity: 0       // No bounds viscosity
    });

    // Street - OpenStreetMap Standard (clearer fonts and road lines)
    // Using tile.openstreetmap.org CDN for better performance
    const streetLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
        updateWhenIdle: true,
        updateWhenZooming: false,
        keepBuffer: 4
    });

    // Satellite - ESRI World Imagery + Roads with Labels
    const satelliteImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Imagery &copy; Esri',
        maxZoom: 19,
        updateWhenIdle: true,
        updateWhenZooming: false,
        keepBuffer: 4
    });

    // Roads + Labels overlay (ESRI Transportation - includes road names)
    const roadsWithLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Roads &copy; Esri',
        maxZoom: 19,
        updateWhenIdle: true,
        updateWhenZooming: false,
        keepBuffer: 2,
        pane: 'overlayPane'
    });

    const satelliteLayer = L.layerGroup([satelliteImagery, roadsWithLabels]);

    // Add default layer
    streetLayer.addTo(map);

    // Overlay - Administrative boundaries (sharp labels)
    const adminBoundaries = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://carto.com/">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20,
        detectRetina: true
    });

    // Layer control - Simple and clean
    const baseLayers = {
        '🗺️ Street': streetLayer,
        '🛰️ Satellite': satelliteLayer
    };

    const overlayLayers = {
        '📍 Batas Wilayah': adminBoundaries
    };

    L.control.layers(baseLayers, overlayLayers, { position: 'topright' }).addTo(map);

    // Search functionality with autocomplete
    const searchInput = document.getElementById('map-search');
    const searchResults = document.getElementById('search-results');
    let searchTimeout = null;
    let allMarkers = { suppliers: {}, factories: {}, distributors: {} }; // Store markers by type and id

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Clear previous timeout
        if (searchTimeout) clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        // Debounce search
        searchTimeout = setTimeout(() => {
            fetch(`{{ route('api.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(results => {
                    // Filter results based on active category filter
                    let filteredResults = results;
                    if (activeFilter !== 'all') {
                        // Map filter name to entity type (handle singular/plural differences)
                        const filterTypeMap = {
                            'supplier': 'supplier',
                            'factory': 'factory',
                            'distributor': 'distributor',
                            'courier': 'courier'
                        };
                        const allowedType = filterTypeMap[activeFilter];
                        filteredResults = results.filter(item => item.type === allowedType);
                    }
                    
                    if (filteredResults.length === 0) {
                        // Show different message if filtered vs no results at all
                        const filterMessage = activeFilter !== 'all' 
                            ? `No ${activeFilter} results found. <a href="#" onclick="filterMap('all'); document.getElementById('map-search').dispatchEvent(new Event('input')); return false;" style="color: #3b82f6; text-decoration: underline;">Show all categories</a>` 
                            : 'No results found';
                        searchResults.innerHTML = `<div style="padding: 12px; color: rgba(255,255,255,0.5);">${filterMessage}</div>`;
                    } else {
                        searchResults.innerHTML = filteredResults.map(item => `
                            <div class="search-result-item" 
                                 onclick="locateEntity(${item.latitude}, ${item.longitude}, '${item.type}', ${item.id}, '${item.name.replace(/'/g, "\\'")}')"
                                 style="padding: 10px 12px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 10px; transition: background 0.2s;"
                                 onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                                 onmouseout="this.style.background='transparent'">
                                <span style="font-size: 1.2rem;">${item.icon}</span>
                                <div>
                                    <div style="font-weight: 500; color: #fff;">${item.name}</div>
                                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">${item.type.charAt(0).toUpperCase() + item.type.slice(1)} • ${item.address || 'No address'}</div>
                                </div>
                            </div>
                        `).join('');
                    }
                    searchResults.style.display = 'block';
                })
                .catch(err => {
                    console.error('Search error:', err);
                    searchResults.style.display = 'none';
                });
        }, 300);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Locate entity on map
    window.locateEntity = function(lat, lng, type, id, name) {
        searchResults.style.display = 'none';
        searchInput.value = name;
        
        // Zoom to location
        map.setView([lat, lng], 16);
        
        // Find and open the marker popup
        const markerKey = `${type}-${id}`;
        const marker = allMarkers[markerKey] || allMarkers.suppliers?.[id] || allMarkers.factories?.[id] || allMarkers.distributors?.[id] || courierMarkers?.[id];
        
        if (marker) {
            // Ensure marker is visible on map (in case it was hidden by filter)
            if (!map.hasLayer(marker)) {
                marker.addTo(map);
            }
            setTimeout(() => marker.openPopup(), 300);
        }
    };

    // Haversine distance calculation (returns km)
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return (R * c).toFixed(1);
    }

    // ==========================================
    // COURIER ROUTING FUNCTIONS
    // ==========================================
    let currentRouteLayer = null;
    let routeMarkers = [];

    // Generate Google Maps navigation URL
    function getGoogleMapsUrl(destLat, destLng, originLat = null, originLng = null) {
        if (originLat && originLng) {
            return `https://www.google.com/maps/dir/${originLat},${originLng}/${destLat},${destLng}`;
        }
        return `https://www.google.com/maps/dir/?api=1&destination=${destLat},${destLng}`;
    }

    // Clear existing route from map
    function clearRoute() {
        if (currentRouteLayer) {
            map.removeLayer(currentRouteLayer);
            currentRouteLayer = null;
        }
        routeMarkers.forEach(m => map.removeLayer(m));
        routeMarkers = [];
    }

    // Draw route using OSRM (Open Source Routing Machine)
    async function drawRoute(originLat, originLng, destLat, destLng, destName = 'Destination') {
        clearRoute();
        
        // Show loading indicator
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'route-loading';
        loadingDiv.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(30, 27, 75, 0.95); padding: 20px 30px; border-radius: 12px; z-index: 10000; color: white;';
        loadingDiv.innerHTML = '⏳ Calculating route...';
        document.body.appendChild(loadingDiv);
        
        try {
            // Call OSRM public API
            const url = `https://router.project-osrm.org/route/v1/driving/${originLng},${originLat};${destLng},${destLat}?overview=full&geometries=geojson`;
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                throw new Error('No route found');
            }
            
            const route = data.routes[0];
            const coordinates = route.geometry.coordinates.map(c => [c[1], c[0]]); // Convert [lng,lat] to [lat,lng]
            const durationMinutes = Math.round(route.duration / 60);
            const distanceKm = (route.distance / 1000).toFixed(1);
            
            // Draw the route polyline
            currentRouteLayer = L.polyline(coordinates, {
                color: '#3b82f6',
                weight: 5,
                opacity: 0.8,
                dashArray: null
            }).addTo(map);
            
            // Add destination marker with route info
            const destMarker = L.marker([destLat, destLng], {
                icon: L.divIcon({
                    className: 'route-dest-marker',
                    html: '<div style="background: #ef4444; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.4);">📍</div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                })
            }).addTo(map);
            
            destMarker.bindPopup(`
                <div style="text-align: center; min-width: 200px;">
                    <div style="font-weight: 600; margin-bottom: 8px;">📍 ${destName}</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                        <div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 8px;">
                            <div style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">Distance</div>
                            <div style="font-weight: 600; color: #3b82f6;">${distanceKm} km</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 8px;">
                            <div style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">Est. Time</div>
                            <div style="font-weight: 600; color: #22c55e;">${durationMinutes} min</div>
                        </div>
                    </div>
                    <a href="${getGoogleMapsUrl(destLat, destLng, originLat, originLng)}" target="_blank" 
                       style="display: block; background: linear-gradient(135deg, #4285F4, #34A853); color: white; padding: 10px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                        🗺️ Buka di Google Maps
                    </a>
                    <button onclick="clearRoute()" 
                            style="margin-top: 8px; width: 100%; padding: 8px; background: rgba(255,255,255,0.1); color: white; border: none; border-radius: 8px; cursor: pointer;">
                        ✕ Tutup Rute
                    </button>
                </div>
            `).openPopup();
            
            routeMarkers.push(destMarker);
            
            // Fit map to show entire route
            map.fitBounds(currentRouteLayer.getBounds().pad(0.1));
            
            console.log(`📍 Route drawn: ${distanceKm}km, ${durationMinutes}min`);
            
        } catch (error) {
            console.error('Routing error:', error);
            alert('Gagal menghitung rute. Silakan coba Google Maps:\n' + getGoogleMapsUrl(destLat, destLng));
        } finally {
            const loader = document.getElementById('route-loading');
            if (loader) loader.remove();
        }
    }

    // Navigate to a location (called from popup buttons)
    window.navigateTo = function(destLat, destLng, destName) {
        // Get courier's current position from GPS or selfEntity
        let originLat = null, originLng = null;
        
        @if(auth()->user()->role === 'courier')
        // Try to get from GPS first
        if (myLocationMarker) {
            const pos = myLocationMarker.getLatLng();
            originLat = pos.lat;
            originLng = pos.lng;
        } else if (selfEntity && selfEntity.latitude && selfEntity.longitude) {
            originLat = selfEntity.latitude;
            originLng = selfEntity.longitude;
        }
        @endif
        
        if (originLat && originLng) {
            drawRoute(originLat, originLng, destLat, destLng, destName);
        } else {
            // No GPS, open Google Maps directly
            window.open(getGoogleMapsUrl(destLat, destLng), '_blank');
        }
    };

    // Open Google Maps directly
    window.openGoogleMaps = function(destLat, destLng) {
        window.open(getGoogleMapsUrl(destLat, destLng), '_blank');
    };

    // Self entity data from server
    const selfEntity = @json($selfEntity);

    // Custom icons
    const icons = {
        supplier: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #22c55e; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">📦</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        factory: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #f59e0b; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">🏭</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        distributor: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #6366f1; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">🏪</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        courierIdle: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #22c55e; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">🚚</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        courierBusy: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #ef4444; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">🚚</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        courierNoGps: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #6b7280; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px dashed #9ca3af; box-shadow: 0 2px 10px rgba(0,0,0,0.3); opacity: 0.7;">🚚</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        // Special SELF icons - larger with pulsing glow effect
        selfSupplier: L.divIcon({
            className: 'custom-marker self-marker',
            html: '<div class="self-marker-inner" style="background: linear-gradient(135deg, #22c55e, #16a34a); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 4px solid #ffffff; box-shadow: 0 0 20px rgba(34, 197, 94, 0.8), 0 4px 15px rgba(0,0,0,0.4); animation: selfPulse 2s infinite;">📦</div>',
            iconSize: [45, 45],
            iconAnchor: [22, 22]
        }),
        selfFactory: L.divIcon({
            className: 'custom-marker self-marker',
            html: '<div class="self-marker-inner" style="background: linear-gradient(135deg, #f59e0b, #d97706); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 4px solid #ffffff; box-shadow: 0 0 20px rgba(245, 158, 11, 0.8), 0 4px 15px rgba(0,0,0,0.4); animation: selfPulse 2s infinite;">🏭</div>',
            iconSize: [45, 45],
            iconAnchor: [22, 22]
        }),
        selfDistributor: L.divIcon({
            className: 'custom-marker self-marker',
            html: '<div class="self-marker-inner" style="background: linear-gradient(135deg, #6366f1, #4f46e5); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 4px solid #ffffff; box-shadow: 0 0 20px rgba(99, 102, 241, 0.8), 0 4px 15px rgba(0,0,0,0.4); animation: selfPulse 2s infinite;">🏪</div>',
            iconSize: [45, 45],
            iconAnchor: [22, 22]
        }),
        selfCourier: L.divIcon({
            className: 'custom-marker self-marker',
            html: '<div class="self-marker-inner" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 4px solid #ffffff; box-shadow: 0 0 20px rgba(14, 165, 233, 0.8), 0 4px 15px rgba(0,0,0,0.4); animation: selfPulse 2s infinite;">🚚</div>',
            iconSize: [45, 45],
            iconAnchor: [22, 22]
        })
    };

    // Fetch and display markers
    let selfMarker = null;
    
    fetch('{{ route("api.map-data") }}')
        .then(response => response.json())
        .then(data => {
            data.features.forEach(feature => {
                const coords = feature.geometry.coordinates;
                const props = feature.properties;
                
                // Check if this entity is the self (logged-in user's) entity
                // Using == for ID comparison to handle type coercion (string vs number)
                const isSelf = selfEntity && 
                               selfEntity.type === props.type && 
                               selfEntity.id == props.id;
                
                // Debug logging for self detection
                if (selfEntity && selfEntity.type === props.type) {
                    console.log('Checking entity:', props.type, 'props.id:', props.id, '(type:', typeof props.id, ') selfEntity.id:', selfEntity.id, '(type:', typeof selfEntity.id, ') isSelf:', isSelf);
                }
                
                let icon = icons[props.type] || icons.supplier;

                // Use special self icon if this is the logged-in user's entity
                if (isSelf) {
                    const selfIconKey = 'self' + props.type.charAt(0).toUpperCase() + props.type.slice(1);
                    icon = icons[selfIconKey] || icon;
                } else if (props.type === 'courier') {
                    // Check GPS status - use semi-transparent icon if no GPS
                    if (!props.is_gps_active) {
                        icon = icons.courierNoGps;
                    } else {
                        icon = (props.status === 'idle') ? icons.courierIdle : icons.courierBusy;
                    }
                }

                let popupContent = `
                    <div class="popup-title">${props.name}${isSelf ? ' <span style="background: linear-gradient(135deg, #ec4899, #8b5cf6); padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; margin-left: 6px;">YOU</span>' : ''}</div>
                    <span class="popup-type ${props.type}">${props.type}</span>
                `;

                if (props.address) {
                    popupContent += `<div class="popup-info">📍 ${props.address}</div>`;
                }
                if (props.phone) {
                    popupContent += `<div class="popup-info">📞 ${props.phone}</div>`;
                }
                // Show email only for self marker (from user account)
                if (isSelf && selfEntity && selfEntity.email) {
                    console.log('Adding email to popup:', selfEntity.email);
                    popupContent += `<div class="popup-info">✉️ ${selfEntity.email}</div>`;
                }
                if (props.capacity) {
                    popupContent += `<div class="popup-info">📊 Capacity: ${props.capacity}</div>`;
                }
                if (props.status) {
                    popupContent += `<div class="popup-info">Status: ${props.status}</div>`;
                }
                // Show GPS status for couriers
                if (props.type === 'courier') {
                    const gpsStatus = props.is_gps_active 
                        ? '<span style="background: #22c55e; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem;">📍 GPS Aktif</span>'
                        : '<span style="background: #6b7280; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem;">⏸️ GPS Tidak Aktif</span>';
                    popupContent += `<div style="margin: 8px 0;">${gpsStatus}</div>`;
                    if (props.vehicle) {
                        popupContent += `<div class="popup-info">🚚 ${props.vehicle}</div>`;
                    }
                    if (props.license_plate) {
                        popupContent += `<div class="popup-info">🔢 ${props.license_plate}</div>`;
                    }
                    if (props.last_seen) {
                        popupContent += `<div class="popup-info" style="color: #888;">🕐 ${props.last_seen}</div>`;
                    }
                }


                // Products/Stocks - Role-based display
                @php
                    $userRole = auth()->user()->role;
                @endphp

                // Supplier products - show badge if has stock
                if (props.type === 'supplier' && props.products && props.products.length > 0) {
                    const hasStock = props.products.some(p => p.stock > 0);
                    if (hasStock) {
                        popupContent += `<div style="margin: 8px 0;"><span style="background: linear-gradient(135deg, #22c55e, #16a34a); padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">🏷️ SELLING</span></div>`;
                    }
                    
                    // isSelf = user viewing their own marker, OR Factory can see full details
                    @if($userRole === 'factory' || $userRole === 'supplier')
                    if (isSelf || '{{ $userRole }}' === 'factory') {
                        // Show full product details with price and stock
                        popupContent += '<div class="popup-products"><strong>📦 ' + (isSelf ? 'My Products:' : 'Raw Materials Available:') + '</strong>';
                        props.products.forEach(p => {
                            const stockBadge = p.stock > 0 ? `<span style="color: #22c55e;">(${p.stock} in stock)</span>` : `<span style="color: #ef4444;">(Out of stock)</span>`;
                            popupContent += `<div class="popup-product">• ${p.name} - <strong>$${p.price}</strong> ${stockBadge}</div>`;
                        });
                        popupContent += '</div>';
                    } else {
                        // Other suppliers viewing different supplier - limited info
                        popupContent += `<div class="popup-info" style="margin-top: 8px; color: rgba(255,255,255,0.5); font-size: 0.8rem;"><em>📦 ${props.products.length} products available</em></div>`;
                    }
                    @else
                    // Superadmin, Distributor & Courier can only see company info, no product details
                    popupContent += `<div class="popup-info" style="margin-top: 8px; color: rgba(255,255,255,0.5); font-size: 0.8rem;"><em>📦 ${props.products.length} products available</em></div>`;
                    @endif
                }

                // Factory products - show to distributors with price info
                if (props.type === 'factory' && props.products && props.products.length > 0) {
                    const hasStock = props.products.some(p => p.quantity > 0);
                    if (hasStock) {
                        popupContent += `<div style="margin: 8px 0;"><span style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">🏭 PRODUCING</span></div>`;
                    }
                    
                    // isSelf = user viewing their own marker, OR Distributor can see full details
                    @if($userRole === 'distributor' || $userRole === 'factory')
                    if (isSelf || '{{ $userRole }}' === 'distributor') {
                        // Show full product details with price and quantity
                        popupContent += '<div class="popup-products"><strong>🏭 ' + (isSelf ? 'My Products:' : 'Products Available:') + '</strong>';
                        props.products.forEach(p => {
                            const qtyBadge = p.quantity > 0 ? `<span style="color: #22c55e;">(${p.quantity} available)</span>` : `<span style="color: #ef4444;">(None available)</span>`;
                            popupContent += `<div class="popup-product">• ${p.name} - <strong>$${p.price}</strong> ${qtyBadge}</div>`;
                        });
                        popupContent += '</div>';
                    } else {
                        // Other factories viewing different factory - limited info
                        popupContent += `<div class="popup-info" style="margin-top: 8px; color: rgba(255,255,255,0.5); font-size: 0.8rem;"><em>🏭 ${props.products.length} products manufactured</em></div>`;
                    }
                    @else
                    // Superadmin, Supplier & Courier can only see company info
                    popupContent += `<div class="popup-info" style="margin-top: 8px; color: rgba(255,255,255,0.5); font-size: 0.8rem;"><em>🏭 ${props.products.length} products manufactured</em></div>`;
                    @endif
                }

                // Distributor stocks - show full info only if isSelf
                if (props.stocks && props.stocks.length > 0) {
                    @if($userRole === 'distributor')
                    if (isSelf) {
                        popupContent += '<div class="popup-products"><strong>📊 ' + (isSelf ? 'My Stock:' : 'Stock:') + '</strong>';
                        props.stocks.forEach(s => {
                            popupContent += `<div class="popup-product">• ${s.name}: ${s.quantity} pcs</div>`;
                        });
                        popupContent += '</div>';
                    } else {
                        popupContent += `<div class="popup-info" style="margin-top: 8px; color: rgba(255,255,255,0.5); font-size: 0.8rem;"><em>📊 ${props.stocks.length} products in stock</em></div>`;
                    }
                    @else
                    // Other roles see limited info
                    popupContent += `<div class="popup-info" style="margin-top: 8px; color: rgba(255,255,255,0.5); font-size: 0.8rem;"><em>📊 ${props.stocks.length} products in stock (contact for details)</em></div>`;
                    @endif
                }

                // Superadmin controls - Edit Position & Delete
                @if(auth()->user()->role === 'superadmin')
                if (props.type !== 'courier') {
                    popupContent += `
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-bottom: 8px;">📍 Lat: ${coords[1].toFixed(6)}, Lng: ${coords[0].toFixed(6)}</div>
                            <div style="display: flex; gap: 8px;">
                                <button onclick="editPosition('${props.type}', ${props.id}, ${coords[1]}, ${coords[0]}, '${props.name.replace(/'/g, "\\'")}')" 
                                    style="flex: 1; padding: 8px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">
                                    ✏️ Edit Position
                                </button>
                                <button onclick="deleteEntity('${props.type}', ${props.id}, '${props.name}')" 
                                    style="flex: 1; padding: 8px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">
                                    🗑️ Delete
                                </button>
                            </div>
                        </div>
                    `;
                }
                @endif

                // Role-based action buttons
                @if($userRole === 'supplier')
                // Supplier viewing own marker - Manage Products
                if (isSelf && props.type === 'supplier') {
                    popupContent += `
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <a href="{{ route('supplier.index') }}" 
                               style="display: block; text-align: center; padding: 10px; background: linear-gradient(135deg, #22c55e, #16a34a); color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                🏷️ Manage My Products
                            </a>
                        </div>
                    `;
                }
                @endif

                @if($userRole === 'factory')
                // Factory viewing own marker - Manage Products
                if (isSelf && props.type === 'factory') {
                    popupContent += `
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <a href="{{ route('factory.index') }}" 
                               style="display: block; text-align: center; padding: 10px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                🏭 Manage My Products
                            </a>
                        </div>
                    `;
                }
                // Factory viewing Supplier marker - Buy Materials
                if (!isSelf && props.type === 'supplier' && props.products && props.products.length > 0) {
                    const distance = selfEntity ? calculateDistance(selfEntity.latitude, selfEntity.longitude, coords[1], coords[0]) : '?';
                    popupContent += `
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-bottom: 8px;">📏 Distance: ${distance} km from your factory</div>
                            <button onclick="openPurchaseModal('supplier', ${props.id}, '${props.name.replace(/'/g, "\\'")}', ${JSON.stringify(props.products).replace(/"/g, '&quot;')})" 
                                style="width: 100%; padding: 10px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                🛒 Buy Raw Materials
                            </button>
                        </div>
                    `;
                }
                @endif

                @if($userRole === 'distributor')
                // Distributor viewing own marker
                if (isSelf && props.type === 'distributor') {
                    popupContent += `
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <a href="{{ route('distributor.index') }}" 
                               style="display: block; text-align: center; padding: 10px; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                📊 Manage My Stock
                            </a>
                        </div>
                    `;
                }
                // Distributor viewing Factory marker - Buy Products
                if (!isSelf && props.type === 'factory' && props.products && props.products.length > 0) {
                    const distance = selfEntity ? calculateDistance(selfEntity.latitude, selfEntity.longitude, coords[1], coords[0]) : '?';
                    popupContent += `
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-bottom: 8px;">📏 Distance: ${distance} km from your warehouse</div>
                            <button onclick="openPurchaseModal('factory', ${props.id}, '${props.name.replace(/'/g, "\\'")}', ${JSON.stringify(props.products).replace(/"/g, '&quot;')})" 
                                style="width: 100%; padding: 10px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                🛒 Buy Products
                            </button>
                        </div>
                    `;
                }
                @endif

                // Courier viewing Supplier/Factory markers - Show Navigation Buttons
                @if($userRole === 'courier')
                if (!isSelf && (props.type === 'supplier' || props.type === 'factory')) {
                    const distance = selfEntity ? calculateDistance(selfEntity.latitude, selfEntity.longitude, coords[1], coords[0]) : '?';
                    const entityIcon = props.type === 'supplier' ? '📦' : '🏭';
                    const entityLabel = props.type === 'supplier' ? 'Supplier' : 'Factory';
                    
                    popupContent += `
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6); margin-bottom: 10px; text-align: center;">
                                📏 <strong>${distance} km</strong> dari lokasi Anda
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <button onclick="navigateTo(${coords[1]}, ${coords[0]}, '${props.name.replace(/'/g, "\\'")}')" 
                                    style="width: 100%; padding: 10px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                    🗺️ Lihat Rute
                                </button>
                                <a href="https://www.google.com/maps/dir/?api=1&destination=${coords[1]},${coords[0]}" target="_blank" 
                                   style="display: block; text-align: center; padding: 10px; background: linear-gradient(135deg, #4285F4, #34A853); color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                    🚗 Buka Google Maps
                                </a>
                            </div>
                        </div>
                    `;
                }
                @endif

                const marker = L.marker([coords[1], coords[0]], { icon })
                    .addTo(map)
                    .bindPopup(popupContent, { maxWidth: 300 });
                
                // Store marker for search auto-locate and category filtering
                // Store by type and id for filtering, and also legacy format for search
                if (props.type === 'supplier') {
                    allMarkers.suppliers[props.id] = marker;
                } else if (props.type === 'factory') {
                    allMarkers.factories[props.id] = marker;
                } else if (props.type === 'distributor') {
                    allMarkers.distributors[props.id] = marker;
                }
                // Also keep legacy format for search locateEntity
                allMarkers[`${props.type}-${props.id}`] = marker;
                
                // Store self marker for auto-zoom
                if (isSelf) {
                    selfMarker = marker;
                }
            });

            // Debug: Log selfEntity data
            console.log('Self Entity Data:', selfEntity);

            // Auto-zoom to self entity if exists with valid coordinates, otherwise fit all bounds
            @if(auth()->user()->role === 'courier')
            // For courier: use GPS auto-locate for accurate real-time position
            console.log('Courier detected - triggering GPS auto-locate...');
            setTimeout(() => {
                autoLocateMe();
            }, 1000);
            @else
            if (selfEntity && selfEntity.latitude && selfEntity.longitude && 
                selfEntity.latitude !== 0 && selfEntity.longitude !== 0) {
                console.log('Auto-zooming to self location:', selfEntity.latitude, selfEntity.longitude);
                map.setView([selfEntity.latitude, selfEntity.longitude], 14);
                // Open popup for self marker after a short delay
                if (selfMarker) {
                    setTimeout(() => selfMarker.openPopup(), 500);
                }
            } else if (data.features.length > 0) {
                const group = L.featureGroup(
                    data.features.map(f => L.marker([f.geometry.coordinates[1], f.geometry.coordinates[0]]))
                );
                map.fitBounds(group.getBounds().pad(0.1));
            }
            @endif
        })
        .catch(err => console.error('Error loading map data:', err));


    // AJAX Pagination for dashboard panels
    const pageState = {
        suppliers: {{ $suppliers->currentPage() }},
        factories: {{ $factories->currentPage() }},
        distributors: {{ $distributors->currentPage() }},
        couriers: {{ $couriers->currentPage() }}
    };

    const totalPages = {
        suppliers: {{ $suppliers->lastPage() }},
        factories: {{ $factories->lastPage() }},
        distributors: {{ $distributors->lastPage() }},
        couriers: {{ $couriers->lastPage() }}
    };

    function loadPage(type, direction) {
        const currentPage = pageState[type];
        let newPage = direction === 'next' ? currentPage + 1 : currentPage - 1;
        
        if (newPage < 1 || newPage > totalPages[type]) return;

        const urls = {
            suppliers: '{{ route("api.suppliers") }}',
            factories: '{{ route("api.factories") }}',
            distributors: '{{ route("api.distributors") }}',
            couriers: '{{ route("api.couriers") }}'
        };

        fetch(`${urls[type]}?page=${newPage}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById(`${type}-list`).innerHTML = data.html;
                pageState[type] = data.current_page;
                
                // Update info text
                const firstItem = (data.current_page - 1) * 3 + 1;
                const lastItem = Math.min(data.current_page * 3, data.total);
                document.getElementById(`${type}-info`).textContent = `${firstItem}-${lastItem} of ${data.total}`;
                
                // Update button states
                document.getElementById(`${type}-prev`).disabled = data.current_page <= 1;
                document.getElementById(`${type}-next`).disabled = data.current_page >= data.last_page;
            })
            .catch(error => console.error('Error loading page:', error));
    }

    // Store courier markers for updating
    let courierMarkers = {};

    // Function to refresh courier positions every 5 seconds
    function refreshCourierPositions() {
        fetch('{{ route("api.map-data") }}')
            .then(response => response.json())
            .then(data => {
                data.features.forEach(feature => {
                    if (feature.properties.type === 'courier') {
                        const coords = feature.geometry.coordinates;
                        const props = feature.properties;
                        const courierId = props.id;
                        // Check GPS status
                        const hasGps = props.is_gps_active;

                        // Create icon based on status and GPS
                        let icon;
                        if (!hasGps) {
                            icon = icons.courierNoGps;
                        } else {
                            icon = (props.status === 'idle') ? icons.courierIdle : icons.courierBusy;
                        }

                        // GPS status badge
                        const gpsStatus = hasGps 
                            ? '<span style="background: #22c55e; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem;">📍 GPS Aktif</span>'
                            : '<span style="background: #6b7280; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem;">⏸️ GPS Tidak Aktif</span>';

                        // Last seen info
                        const lastSeenInfo = props.last_seen 
                            ? `<div class="popup-info" style="color: #888;">🕐 ${props.last_seen}</div>` 
                            : '';

                        // Build popup
                        let popupContent = `
                            <div class="popup-title">${props.name}</div>
                            <span class="popup-type courier">courier</span>
                            <div style="margin: 8px 0;">${gpsStatus}</div>
                            <div class="popup-info">🚚 ${props.vehicle || 'Vehicle N/A'}</div>
                            <div class="popup-info">📞 ${props.phone || 'N/A'}</div>
                            <div class="popup-info">Status: <strong>${props.status}</strong></div>
                            ${lastSeenInfo}
                        `;

                        if (courierMarkers[courierId]) {
                            // Update existing marker position, icon, and popup
                            courierMarkers[courierId].setLatLng([coords[1], coords[0]]);
                            courierMarkers[courierId].setIcon(icon);
                            courierMarkers[courierId].setPopupContent(popupContent);
                        } else {
                            // Create new marker
                            courierMarkers[courierId] = L.marker([coords[1], coords[0]], { icon })
                                .addTo(map)
                                .bindPopup(popupContent);
                        }
                    }
                });
            })
            .catch(err => console.error('Error refreshing couriers:', err));
    }

    // Refresh courier positions every 3 seconds (optimized - only update markers, not reload entire map)
    setInterval(refreshCourierPositions, 3000);

    // Purchase Modal Functions
    @if(auth()->user()->role === 'factory' || auth()->user()->role === 'distributor')
    let currentProducts = [];
    
    window.openPurchaseModal = function(sellerType, sellerId, sellerName, products) {
        // Decode products if it's a string
        if (typeof products === 'string') {
            products = JSON.parse(products.replace(/&quot;/g, '"'));
        }
        currentProducts = products;
        
        document.getElementById('modal-seller-id').value = sellerId;
        document.getElementById('modal-seller-entity').value = sellerType;
        document.getElementById('modal-seller-name').textContent = sellerName;
        document.getElementById('modal-seller-type').textContent = sellerType === 'supplier' ? '📦 Supplier' : '🏭 Factory';
        document.getElementById('modal-title').textContent = sellerType === 'supplier' ? '🛒 Buy Raw Materials' : '🛒 Buy Products';
        
        // Build products list
        let productsHtml = '';
        products.forEach((p, index) => {
            const available = p.stock || p.quantity || 0;
            const price = parseFloat(p.price) || 0;
            productsHtml += `
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px; margin-bottom: 8px;">
                    <div style="flex: 1;">
                        <div style="font-weight: 500; color: #fff;">${p.name}</div>
                        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5);">$${price.toFixed(2)} per unit • ${available} available</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="number" 
                               name="qty_${index}" 
                               data-price="${price}" 
                               data-product-id="${p.id || index}"
                               data-product-name="${p.name}"
                               value="0" 
                               min="0" 
                               max="${available}"
                               onchange="updateModalTotal()"
                               oninput="updateModalTotal()"
                               style="width: 70px; padding: 8px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; color: #fff; text-align: center;">
                    </div>
                </div>
            `;
        });
        document.getElementById('modal-products').innerHTML = productsHtml;
        document.getElementById('modal-total').textContent = '$0.00';
        
        // Show modal
        document.getElementById('purchase-modal').style.display = 'flex';
    };
    
    window.closePurchaseModal = function() {
        document.getElementById('purchase-modal').style.display = 'none';
    };
    
    window.updateModalTotal = function() {
        const inputs = document.querySelectorAll('#modal-products input[type="number"]');
        let total = 0;
        inputs.forEach(input => {
            const qty = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            total += qty * price;
        });
        document.getElementById('modal-total').textContent = '$' + total.toFixed(2);
    };
    
    // Form submit handler
    document.getElementById('purchase-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const sellerId = document.getElementById('modal-seller-id').value;
        const sellerType = document.getElementById('modal-seller-entity').value;
        const inputs = document.querySelectorAll('#modal-products input[type="number"]');
        
        // Collect items with qty > 0
        const items = [];
        inputs.forEach((input, index) => {
            const qty = parseInt(input.value) || 0;
            if (qty > 0) {
                items.push({
                    product_name: input.dataset.productName,
                    product_id: input.dataset.productId,
                    quantity: qty,
                    price: parseFloat(input.dataset.price)
                });
            }
        });
        
        if (items.length === 0) {
            alert('Please select at least one product');
            return;
        }
        
        // Determine endpoint based on user role
        const endpoint = sellerType === 'supplier' 
            ? '{{ route("factory.buy-from-supplier") }}'
            : '{{ route("distributor.buy-from-factory") }}';
        
        // Submit order via AJAX
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                seller_id: sellerId,
                items: items
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Order placed successfully! Order #' + data.order_id + '\n\nWaiting for seller confirmation.');
                closePurchaseModal();
                // Reload page to refresh data
                window.location.reload();
            } else {
                alert('❌ Error: ' + (data.message || 'Failed to place order'));
            }
        })
        .catch(err => {
            console.error('Order error:', err);
            alert('❌ Failed to place order. Please try again.');
        });
    });
    @endif

    // Superadmin functions for Edit Position and Delete
    @if(auth()->user()->role === 'superadmin')
    // Helper to get correct plural form for routes
    function getEntityPlural(type) {
        const plurals = {
            'supplier': 'suppliers',
            'factory': 'factories',
            'distributor': 'distributors'
        };
        return plurals[type] || type + 's';
    }

    // Edit Mode State
    let editModeActive = false;
    let editingEntity = null;
    let editPreviewMarker = null;
    let editBannerElement = null;

    // Setup Location Mode State (for new role assignment)
    let setupLocationMode = false;
    let setupLocationEntity = null;
    @if(session('setup_location'))
    setupLocationMode = true;
    setupLocationEntity = @json(session('setup_location'));
    @endif

    // Create edit mode banner
    function createEditBanner() {
        if (editBannerElement) return;
        
        // Get map container
        const mapContainer = document.getElementById('map');
        
        editBannerElement = document.createElement('div');
        editBannerElement.id = 'edit-mode-banner';
        editBannerElement.style.cssText = `
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.95), rgba(217, 119, 6, 0.95));
            color: white;
            padding: 10px 14px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: system-ui, sans-serif;
            font-size: 0.85rem;
            backdrop-filter: blur(8px);
            max-width: 320px;
        `;
        mapContainer.style.position = 'relative';
        mapContainer.appendChild(editBannerElement);
    }

    function showEditBanner(entityName) {
        createEditBanner();
        // Truncate long entity names
        const displayName = entityName.length > 20 ? entityName.substring(0, 20) + '...' : entityName;
        editBannerElement.innerHTML = `
            <div style="display: flex; align-items: center; gap: 6px; flex: 1; min-width: 0;">
                <span style="font-size: 1rem;">📍</span>
                <div style="min-width: 0;">
                    <div style="font-weight: 600; font-size: 0.8rem;">Edit Mode</div>
                    <div style="font-size: 0.75rem; opacity: 0.9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Click map for "${displayName}"</div>
                </div>
            </div>
            <button onclick="cancelEditMode()" style="
                background: rgba(255,255,255,0.25);
                border: none;
                color: white;
                padding: 6px 10px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 500;
                font-size: 0.75rem;
                white-space: nowrap;
                transition: background 0.2s;
            " onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.25)'">
                ✕ Cancel
            </button>
        `;
        editBannerElement.style.display = 'flex';
    }

    function hideEditBanner() {
        if (editBannerElement) {
            editBannerElement.style.display = 'none';
        }
    }

    function editPosition(type, id, currentLat, currentLng, name) {
        // Close any open popups
        map.closePopup();
        
        // Enter edit mode
        editModeActive = true;
        editingEntity = { type, id, currentLat, currentLng, name };
        
        // Show edit banner
        showEditBanner(name);
        
        // Change cursor style
        document.getElementById('map').style.cursor = 'crosshair';
        
        // Add preview marker at current position
        if (editPreviewMarker) {
            map.removeLayer(editPreviewMarker);
        }
        
        editPreviewMarker = L.marker([currentLat, currentLng], {
            icon: L.divIcon({
                className: 'edit-preview-marker',
                html: `<div style="
                    background: #f59e0b;
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 16px;
                    border: 3px solid white;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.4);
                    animation: pulse 1.5s ease-in-out infinite;
                ">📍</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            }),
            zIndexOffset: 1000
        }).addTo(map);
        
        // Add pulse animation CSS if not exists
        if (!document.getElementById('edit-marker-styles')) {
            const style = document.createElement('style');
            style.id = 'edit-marker-styles';
            style.textContent = `
                @keyframes pulse {
                    0%, 100% { transform: scale(1); opacity: 1; }
                    50% { transform: scale(1.1); opacity: 0.8; }
                }
            `;
            document.head.appendChild(style);
        }
        
        console.log('📍 Edit mode activated for:', name);
    }

    function cancelEditMode() {
        editModeActive = false;
        editingEntity = null;
        
        // Remove preview marker
        if (editPreviewMarker) {
            map.removeLayer(editPreviewMarker);
            editPreviewMarker = null;
        }
        
        // Hide banner
        hideEditBanner();
        
        // Reset cursor
        document.getElementById('map').style.cursor = '';
        
        console.log('📍 Edit mode cancelled');
    }

    function confirmNewPosition(lat, lng) {
        if (!editingEntity) return;
        
        const { type, id, name, currentLat, currentLng } = editingEntity;
        const plural = getEntityPlural(type);
        
        // Show confirmation
        if (!confirm(`Set new location for "${name}"?\n\nNew coordinates:\nLatitude: ${lat.toFixed(6)}\nLongitude: ${lng.toFixed(6)}`)) {
            return;
        }

        // Send update request
        fetch(`/superadmin/${plural}/${id}/position`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: lat,
                longitude: lng
            })
        })
        .then(response => {
            if (response.ok) {
                return response.json().catch(() => ({ success: true }));
            }
            return response.json().then(data => {
                throw new Error(data.message || 'Failed to update position');
            });
        })
        .then(data => {
            if (data.success) {
                // Update marker position without reload
                const markerKey = `${type}-${id}`;
                const existingMarker = allMarkers[markerKey];
                
                if (existingMarker) {
                    // Update marker position
                    existingMarker.setLatLng([lat, lng]);
                    
                    // Get current popup content and update coordinates
                    const popup = existingMarker.getPopup();
                    if (popup) {
                        let content = popup.getContent();
                        // Update coordinates in popup using regex
                        content = content.replace(
                            /📍 Lat: [\d.-]+, Lng: [\d.-]+/,
                            `📍 Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`
                        );
                        popup.setContent(content);
                    }
                    
                    // Pan to new location
                    map.panTo([lat, lng]);
                }
                
                // Show success toast notification
                showSuccessToast(`✅ Position updated for "${name}"`);
            } else {
                alert('Error: ' + (data.message || 'Failed to update position'));
            }
        })
        .catch(err => {
            console.error('Error updating position:', err);
            alert('Error: ' + err.message);
        })
        .finally(() => {
            cancelEditMode();
        });
    }

    // Toast notification for success messages
    function showSuccessToast(message) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 10000;
            font-family: system-ui, sans-serif;
            font-weight: 500;
            animation: slideUp 0.3s ease-out;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Add animation CSS
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                @keyframes slideUp {
                    from { opacity: 0; transform: translateX(-50%) translateY(20px); }
                    to { opacity: 1; transform: translateX(-50%) translateY(0); }
                }
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Click marker for adding new entities
    let clickMarker = null;

    // Setup location banner element
    let setupBannerElement = null;

    // Show setup location banner
    function showSetupLocationBanner(entityName, entityType) {
        const mapContainer = document.getElementById('map');
        
        setupBannerElement = document.createElement('div');
        setupBannerElement.id = 'setup-location-banner';
        setupBannerElement.style.cssText = `
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.95), rgba(22, 163, 74, 0.95));
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: system-ui, sans-serif;
            backdrop-filter: blur(8px);
        `;
        
        const icon = entityType === 'supplier' ? '📦' : entityType === 'factory' ? '🏭' : '🏪';
        setupBannerElement.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5rem;">${icon}</span>
                <div>
                    <div style="font-weight: 600;">Set Location for ${entityName}</div>
                    <div style="font-size: 0.8rem; opacity: 0.9;">Click anywhere on the map to set their ${entityType} location</div>
                </div>
            </div>
            <button onclick="cancelSetupLocation()" style="
                background: rgba(255,255,255,0.25);
                border: none;
                color: white;
                padding: 8px 14px;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 500;
                font-size: 0.85rem;
            ">
                ✕ Skip
            </button>
        `;
        mapContainer.style.position = 'relative';
        mapContainer.appendChild(setupBannerElement);
        
        // Change cursor
        mapContainer.style.cursor = 'crosshair';
    }

    // Hide setup location banner
    function hideSetupLocationBanner() {
        if (setupBannerElement) {
            setupBannerElement.remove();
            setupBannerElement = null;
        }
        document.getElementById('map').style.cursor = '';
    }

    // Cancel setup location mode
    window.cancelSetupLocation = function() {
        setupLocationMode = false;
        setupLocationEntity = null;
        hideSetupLocationBanner();
        showSuccessToast('⚠️ Location setup skipped. You can set it later from the map.');
    };

    // Save setup location
    function saveSetupLocation(lat, lng) {
        if (!setupLocationEntity) return;
        
        const { type, id, name } = setupLocationEntity;
        const plural = getEntityPlural(type);
        
        // Send update request
        fetch(`/superadmin/${plural}/${id}/position`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: lat,
                longitude: lng
            })
        })
        .then(response => {
            if (response.ok) {
                return response.json().catch(() => ({ success: true }));
            }
            throw new Error('Failed to save location');
        })
        .then(data => {
            if (data.success) {
                showSuccessToast(`✅ Location set for "${name}"!`);
                // Reload to show marker at new position
                setTimeout(() => location.reload(), 1500);
            }
        })
        .catch(err => {
            console.error('Error saving location:', err);
            alert('Failed to save location. Please try again.');
        })
        .finally(() => {
            setupLocationMode = false;
            setupLocationEntity = null;
            hideSetupLocationBanner();
        });
    }

    // Auto-start setup location mode if redirected from role update
    if (setupLocationMode && setupLocationEntity) {
        setTimeout(() => {
            showSetupLocationBanner(setupLocationEntity.name, setupLocationEntity.type);
        }, 500);
    }

    // Handle map click for edit mode and setup location mode
    map.on('click', function(e) {
        // Priority 1: Setup location mode (new role assignment)
        if (setupLocationMode && setupLocationEntity) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            if (confirm(`Set location for "${setupLocationEntity.name}"?\n\nCoordinates:\nLat: ${lat.toFixed(6)}\nLng: ${lng.toFixed(6)}`)) {
                saveSetupLocation(lat, lng);
            }
            return;
        }

        // Priority 2: Edit position mode
        if (editModeActive && editingEntity) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            // Update preview marker position
            if (editPreviewMarker) {
                editPreviewMarker.setLatLng([lat, lng]);
            }
            
            // Confirm and save new position
            confirmNewPosition(lat, lng);
            return; // Don't process normal click
        }
        
        // Normal superadmin click behavior (add new entity) - only when not in edit mode
        const lat = e.latlng.lat.toFixed(8);
        const lng = e.latlng.lng.toFixed(8);

        // Remove previous click marker
        if (clickMarker) {
            map.removeLayer(clickMarker);
        }

        // Create popup content with entity options (only fixed location entities)
        const popupContent = `
            <div style="text-align: center; min-width: 200px; background: rgba(30, 27, 75, 0.95); padding: 15px; border-radius: 12px; margin: -13px -20px;">
                <div style="font-weight: 600; margin-bottom: 10px; color: #fff;">📍 Add New Entity</div>
                <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7); margin-bottom: 15px;">
                    Lat: ${lat}<br>Lng: ${lng}
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('superadmin.add.supplier') }}?lat=${lat}&lng=${lng}" 
                       style="background: #22c55e; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        📦 Add Supplier
                    </a>
                    <a href="{{ route('superadmin.add.factory') }}?lat=${lat}&lng=${lng}" 
                       style="background: #f59e0b; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        🏭 Add Factory
                    </a>
                    <a href="{{ route('superadmin.add.distributor') }}?lat=${lat}&lng=${lng}" 
                       style="background: #6366f1; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        🏪 Add Distributor
                    </a>
                </div>
            </div>
        `;

        // Add a temporary marker with popup
        clickMarker = L.marker([e.latlng.lat, e.latlng.lng], {
            icon: L.divIcon({
                className: 'click-marker',
                html: '<div style="background: #ec4899; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">➕</div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            })
        }).addTo(map);

        clickMarker.bindPopup(popupContent, { maxWidth: 300 }).openPopup();
    });

    function deleteEntity(type, id, name) {
        if (!confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone.`)) {
            return;
        }

        const plural = getEntityPlural(type);

        // Send delete request
        fetch(`/superadmin/${plural}/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            // Check if response is ok (status 200-299)
            if (response.ok) {
                return response.json().catch(() => ({ success: true }));
            }
            // Try to parse error response
            return response.json().then(data => {
                throw new Error(data.message || 'Failed to delete');
            }).catch(() => {
                throw new Error('Failed to delete');
            });
        })
        .then(data => {
            if (data.success) {
                alert('Deleted successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete'));
            }
        })
        .catch(err => {
            console.error('Error deleting:', err);
            alert('Error: ' + err.message);
        });
    }
    @endif

    // ========================================
    // IMPROVED COURIER GPS TRACKING (Dashboard)
    // Using watchPosition for real-time accuracy
    // ========================================
    @if(auth()->user()->role === 'courier')
    let courierWatchId = null;
    let isCourierGpsTracking = false;
    let courierSelfMarker = null;
    let courierLastSentLocation = null;
    let courierSendThrottle = null;
    
    // Find the courier's own marker on the map
    function findCourierSelfMarker() {
        if (selfEntity && selfEntity.type === 'courier') {
            const markerKey = `courier-${selfEntity.id}`;
            courierSelfMarker = allMarkers[markerKey] || null;
        }
    }
    
    function toggleCourierGPS() {
        if (isCourierGpsTracking) {
            stopCourierGPS();
        } else {
            startCourierGPS();
        }
    }

    function startCourierGPS() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by this browser.');
            updateCourierGpsStatus('❌ Not Supported', 'badge-danger');
            return;
        }
        
        if (courierWatchId !== null) return; // Already tracking

        updateCourierGpsStatus('🔄 Detecting...', 'badge-warning');
        document.getElementById('toggle-gps-btn').innerHTML = '⏳ Starting...';
        document.getElementById('toggle-gps-btn').disabled = true;

        // Use watchPosition for real-time continuous updates
        courierWatchId = navigator.geolocation.watchPosition(
            onCourierGpsSuccess,
            onCourierGpsError,
            {
                enableHighAccuracy: true,  // Use GPS hardware
                timeout: 15000,            // 15 second timeout
                maximumAge: 0              // Don't use cached position
            }
        );
        
        isCourierGpsTracking = true;
        document.getElementById('toggle-gps-btn').innerHTML = '⏹️ Stop GPS';
        document.getElementById('toggle-gps-btn').classList.remove('btn-success');
        document.getElementById('toggle-gps-btn').classList.add('btn-danger');
        document.getElementById('toggle-gps-btn').disabled = false;
        
        console.log('📍 Dashboard GPS watchPosition started, ID:', courierWatchId);
    }

    function stopCourierGPS() {
        if (courierWatchId !== null) {
            navigator.geolocation.clearWatch(courierWatchId);
            courierWatchId = null;
            console.log('📍 Dashboard GPS watchPosition stopped');
        }
        
        isCourierGpsTracking = false;
        document.getElementById('toggle-gps-btn').innerHTML = '▶️ Start GPS';
        document.getElementById('toggle-gps-btn').classList.remove('btn-danger');
        document.getElementById('toggle-gps-btn').classList.add('btn-success');
        updateCourierGpsStatus('⏸️ Paused', 'badge-warning');
        
        // Send inactive status
        if (courierLastSentLocation) {
            sendCourierLocationToServer(courierLastSentLocation.lat, courierLastSentLocation.lng, false);
        }
    }
    
    function onCourierGpsSuccess(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy;
        const timestamp = new Date(position.timestamp);

        console.log(`📍 Dashboard GPS: ${lat.toFixed(6)}, ${lng.toFixed(6)} (±${accuracy.toFixed(0)}m)`);

        // Update status
        updateCourierGpsStatus('🟢 GPS Active', 'badge-success');

        // Update coordinates display
        document.getElementById('courier-lat').textContent = lat.toFixed(6);
        document.getElementById('courier-lng').textContent = lng.toFixed(6);
        document.getElementById('courier-accuracy').textContent = `±${Math.round(accuracy)}m`;
        document.getElementById('courier-last-update').textContent = timestamp.toLocaleTimeString('id-ID');
        
        // Update accuracy indicator color
        updateCourierAccuracyColor(accuracy);

        // Update marker on map with GPS status
        findCourierSelfMarker();
        const selfPopupContent = `
            <strong>${selfEntity ? selfEntity.name : 'Courier'}</strong>
            <span style="background: linear-gradient(135deg, #ec4899, #8b5cf6); padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; margin-left: 6px;">YOU</span>
            <div style="margin: 8px 0;"><span style="background: #22c55e; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem;">📍 GPS Aktif</span></div>
            <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7);">📍 ${lat.toFixed(6)}, ${lng.toFixed(6)}</div>
            <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7);">🎯 Akurasi: ±${Math.round(accuracy)}m</div>
        `;
        
        if (courierSelfMarker) {
            courierSelfMarker.setLatLng([lat, lng]);
            courierSelfMarker.setPopupContent(selfPopupContent);
        } else if (selfEntity && selfEntity.type === 'courier') {
            courierSelfMarker = L.marker([lat, lng], { icon: icons.selfCourier })
                .addTo(map)
                .bindPopup(selfPopupContent);
            allMarkers[`courier-${selfEntity.id}`] = courierSelfMarker;
        }

        // Throttled send to server
        throttledCourierSend(lat, lng, accuracy);
    }
    
    function onCourierGpsError(error) {
        console.error('GPS Error:', error.code, error.message);
        
        let errorMsg = '';
        let statusClass = 'badge-danger';
        
        switch (error.code) {
            case error.PERMISSION_DENIED:
                errorMsg = '🚫 Permission Denied';
                showCourierPermissionHelp();
                break;
            case error.POSITION_UNAVAILABLE:
                errorMsg = '📡 Signal Unavailable';
                statusClass = 'badge-warning';
                break;
            case error.TIMEOUT:
                errorMsg = '⏱️ Timeout...';
                statusClass = 'badge-warning';
                break;
            default:
                errorMsg = '⚠️ GPS Error';
                break;
        }
        
        updateCourierGpsStatus(errorMsg, statusClass);
    }
    
    function updateCourierAccuracyColor(accuracy) {
        const el = document.getElementById('courier-accuracy');
        const label = document.getElementById('courier-accuracy-label');
        let color, text;
        
        if (accuracy < 10) {
            color = '#22c55e'; text = '🎯 Excellent';
        } else if (accuracy < 30) {
            color = '#84cc16'; text = '✅ Good';
        } else if (accuracy < 100) {
            color = '#f59e0b'; text = '⚠️ Fair';
        } else {
            color = '#ef4444'; text = '❌ Poor';
        }
        
        el.style.color = color;
        label.innerHTML = `<span style="color: ${color}">${text}</span>`;
    }
    
    function updateCourierGpsStatus(text, badgeClass) {
        const el = document.getElementById('courier-gps-status');
        el.innerHTML = text;
        el.className = 'badge ' + badgeClass;
    }
    
    function throttledCourierSend(lat, lng, accuracy) {
        if (courierSendThrottle) clearTimeout(courierSendThrottle);
        
        const locationData = { lat, lng, accuracy };
        const shouldSendNow = !courierLastSentLocation || 
            calculateCourierDistance(courierLastSentLocation.lat, courierLastSentLocation.lng, lat, lng) > 10;
        
        if (shouldSendNow) {
            sendCourierLocationToServer(lat, lng, true, accuracy);
            courierLastSentLocation = locationData;
        } else {
            courierSendThrottle = setTimeout(() => {
                sendCourierLocationToServer(lat, lng, true, accuracy);
                courierLastSentLocation = locationData;
            }, 3000);
        }
    }
    
    function calculateCourierDistance(lat1, lng1, lat2, lng2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        return 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    function sendCourierLocationToServer(lat, lng, isGpsActive, accuracy = null) {
        if (!lat || !lng) return;
        
        fetch('{{ route("courier.location") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                latitude: lat, 
                longitude: lng,
                is_gps_active: isGpsActive,
                accuracy: accuracy
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                console.log('📤 Location sent to server');
            }
        })
        .catch(err => {
            console.error('Server Error:', err);
        });
    }
    
    function showCourierPermissionHelp() {
        const btn = document.getElementById('toggle-gps-btn');
        if (btn) {
            btn.innerHTML = '🔓 Enable GPS';
            btn.classList.remove('btn-success', 'btn-danger');
            btn.classList.add('btn-warning');
            btn.onclick = function() {
                alert('GPS Permission Denied!\n\nTo enable GPS:\n\n1. Click the lock/info icon in browser address bar\n2. Select "Site settings"\n3. Change "Location" to "Allow"\n4. Refresh this page');
            };
        }
    }

    // Auto-start GPS on page load for courier
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🛵 Dashboard GPS - Initializing with watchPosition...');
        
        if (!navigator.geolocation) {
            updateCourierGpsStatus('❌ GPS Not Supported', 'badge-danger');
            return;
        }
        
        // Check permission state if available
        if (navigator.permissions) {
            navigator.permissions.query({ name: 'geolocation' }).then(function(result) {
                console.log('GPS Permission status:', result.state);
                
                if (result.state === 'granted') {
                    updateCourierGpsStatus('✅ GPS Ready', 'badge-success');
                    startCourierGPS();
                } else if (result.state === 'denied') {
                    updateCourierGpsStatus('🚫 GPS Denied', 'badge-danger');
                    showCourierPermissionHelp();
                } else {
                    updateCourierGpsStatus('📍 Click Start GPS', 'badge-info');
                }
                
                result.onchange = function() {
                    if (this.state === 'granted' && !isCourierGpsTracking) {
                        startCourierGPS();
                    }
                };
            });
        } else {
            // Fallback: try to start and see what happens
            startCourierGPS();
        }
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (courierWatchId !== null) {
            navigator.geolocation.clearWatch(courierWatchId);
            if (courierLastSentLocation) {
                sendCourierLocationToServer(courierLastSentLocation.lat, courierLastSentLocation.lng, false);
            }
        }
    });
    @endif
</script>
@endsection

